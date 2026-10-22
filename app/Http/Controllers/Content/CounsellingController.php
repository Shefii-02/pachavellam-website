<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\CounsellingRequest;
use App\Support\StaffAccess;
use Illuminate\Http\Request;

/** "Talk to a counsellor": students send the home page form, staff follow up under Website content > Counselling requests. */
class CounsellingController extends Controller
{
    /** Public: the home page form. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:/^[0-9+\-\s]{10,20}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'exam_id' => ['nullable', 'integer', 'exists:competitive_exams,id'],
            'preferred_time' => ['nullable', 'in:' . implode(',', CounsellingRequest::TIMES)],
            'message' => ['nullable', 'string', 'max:1000'],
        ], ['phone.regex' => 'Enter a valid mobile number.']);

        CounsellingRequest::create($data + ['user_id' => $request->user()?->id, 'status' => 'new']);

        $msg = 'Thank you! Our counsellor will call you soon.';

        return $request->expectsJson() ? response()->json(['ok' => true, 'message' => $msg]) : back()->with('counselling', $msg);
    }

    public function index(Request $request)
    {
        StaffAccess::authorize($request, 'Content manage');

        $list = CounsellingRequest::with('exam:id,short_name,name,color', 'handler:id,name')
            ->when(isset(CounsellingRequest::STATUSES[$request->status]), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('exam'), fn ($q) => $q->where('exam_id', $request->exam))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%' . $request->q . '%')->orWhere('phone', 'like', '%' . $request->q . '%')))
            ->orderByRaw("status = 'new' desc")->orderByDesc('id')->paginate(25)->withQueryString();

        return view('admin.content.counselling.index', [
            'list' => $list, 'exams' => CompetitiveExam::orderBy('position')->get(),
            'counts' => CounsellingRequest::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
        ]);
    }

    public function update(Request $request, $id)
    {
        StaffAccess::authorize($request, 'Content manage');
        $data = $request->validate(['status' => ['required', 'in:' . implode(',', array_keys(CounsellingRequest::STATUSES))], 'staff_note' => ['nullable', 'string', 'max:2000']]);

        CounsellingRequest::findOrFail($id)->update($data + ['handled_by' => $request->user()->id]);

        return back()->with('message', 'Counselling request updated');
    }
}
