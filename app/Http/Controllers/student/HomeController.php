<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\StudentFilter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Auth;
use App\Helper;
use Storage;

class HomeController extends Controller
{
    //
    public function dashboard(){

        return view('admin.student.dashboard');
    }


    public function students_list(Request $request){
        $filters = StudentFilter::filtersFrom($request);

        $list = StudentFilter::query($filters)
            ->orderByDesc('users.id')
            ->paginate(50)
            ->withQueryString();

        // newest activity is only needed for the rows on screen
        if (!isset($list->first()->last_activity)) {
            StudentFilter::attachLastActivity($list->items());
        }

        // headline numbers, cached for a few minutes (they scan the activity tables)
        $stats = Cache::remember('student-list-stats', 300, function () {
            $base = ['type' => 'Student'] + StudentFilter::filtersFrom(new Request());
            $count = fn (array $over) => StudentFilter::query(array_merge($base, $over))->count('users.id');

            return [
                'total' => $count([]),
                'with_mobile' => $count(['mobile' => 'with']),
                'without_mobile' => $count(['mobile' => 'without']),
                'active_30' => $count(['activity' => 'active_30']),
                'inactive_90' => $count(['activity' => 'inactive_90']),
            ];
        });

        return view('admin.student.student-list', [
            'list' => $list,
            'filters' => $filters,
            'stats' => $stats,
            'types' => User::select('type')->distinct()->pluck('type'),
        ]);
    }

    /** CSV of everything the current filters match (not just the visible page). */
    public function students_export(Request $request){
        $filters = StudentFilter::filtersFrom($request);
        $query = StudentFilter::query($filters, true);
        $filename = 'students-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Malayalam names correctly
            fputcsv($out, ['ID', 'Name', 'Email', 'Mobile', 'Mobile verified', 'Type', 'Signup method',
                'Gender', 'City', 'State', 'Joined', 'Last activity', 'Status']);

            // guard against spreadsheet formula injection (=, +, -, @ at the start of a cell)
            $safe = fn ($v) => is_string($v) && $v !== '' && strpbrk($v[0], '=+-@') ? "'" . $v : $v;

            $query->lazyById(1000, 'users.id', 'id')->each(function ($u) use ($out, $safe) {
                fputcsv($out, [
                    $u->id, $safe($u->name), $safe($u->email), $safe($u->mobile),
                    $u->mobile_verified_at ? 'Yes' : 'No', $u->type, StudentFilter::signupMethod($u),
                    $u->gender, $safe($u->city), $safe($u->state),
                    optional($u->created_at)->format('Y-m-d H:i'),
                    $u->last_activity ?: '',
                    StudentFilter::activityStatus($u->last_activity)[0],
                ]);
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
    
    public function student_delete($slug){
        
        if(auth()->user()->type == 'Admin'){
            User::where('id',$slug)->delete();
            
            return redirect()->back()->with('message','Deleted Successfully');
        }
        else{
            abort(404);
        }
    }
}
