<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** admin/home-exams: tick which exam series (Daily / OMR / GK / CA) of which exam type appear on the home page, and in what order. */
class HomeExamsController extends Controller
{
    public const SERIES = ['daily-exams' => 'Daily exams', 'omr-exams' => 'OMR exams', 'gk-exams' => 'GK exam series', 'ca-exams' => 'CA exam series', 'special-exams' => 'Special exams'];

    private function allow(Request $request): void
    {
        StaffAccess::authorize($request, 'Content manage');
    }

    public function index(Request $request)
    {
        $this->allow($request);
        $rows = DB::table('home_exam_series')->get()->keyBy(fn ($r) => $r->exam_id . ':' . $r->series);

        return view('admin.home-exams.index', ['exams' => CompetitiveExam::where('status', 1)->orderBy('position')->get(), 'rows' => $rows, 'series' => self::SERIES]);
    }

    public function save(Request $request)
    {
        $this->allow($request);
        $on = (array) $request->input('show', []);          // show[examId][series] = 1
        $pos = (array) $request->input('pos', []);          // pos[examId][series] = number
        $exams = CompetitiveExam::pluck('id')->all();
        DB::transaction(function () use ($on, $pos, $exams, $request) {
            foreach ($exams as $examId) {
                foreach (array_keys(self::SERIES) as $s) {
                    DB::table('home_exam_series')->updateOrInsert(['exam_id' => $examId, 'series' => $s], [
                        'show_home' => !empty($on[$examId][$s]), 'position' => max(0, min(9999, (int) ($pos[$examId][$s] ?? 0))),
                        'updated_by' => $request->user()->id, 'updated_at' => now(),
                    ]);
                }
            }
        });
        Cache::forget('home_exam_series');

        return redirect(url('admin/home-exams'))->with('message', 'Saved - the home page shows the ticked exam series.');
    }
}
