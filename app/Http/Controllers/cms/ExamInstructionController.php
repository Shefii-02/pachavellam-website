<?php

namespace App\Http\Controllers\cms;

use App\Http\Controllers\Controller;
use App\Models\DailyExam;
use App\Models\ExamInstruction;
use Illuminate\Http\Request;

/** Admin: instruction templates that OMR and MCQ exams pick from (write once, reuse). */
class ExamInstructionController extends Controller
{
    public function index(Request $request)
    {
        $list = ExamInstruction::orderBy('kind')->orderBy('name')->get();
        $edit = $request->filled('edit') ? $list->firstWhere('id', (int) $request->edit) : null;
        $used = DailyExam::whereNotNull('instruction_id')->selectRaw('instruction_id, count(*) c')->groupBy('instruction_id')->pluck('c', 'instruction_id');

        return view('cms.exam-instructions', compact('list', 'edit', 'used'));
    }

    public function save(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:120', 'kind' => 'required|in:omr,mcq', 'body' => 'required|string']);
        $data['body'] = ExamInstruction::clean($data['body']);
        $row = $request->filled('id') ? ExamInstruction::findOrFail($request->id) : new ExamInstruction();
        $row->fill($data)->save();
        if ($request->boolean('is_default')) {
            ExamInstruction::where('kind', $row->kind)->where('id', '<>', $row->id)->update(['is_default' => false]);
            $row->update(['is_default' => true]);
        } elseif ($row->is_default) {
            $row->update(['is_default' => false]);
        }

        return redirect(url('admin/kpsc/exam-instructions'))->with('ok', 'Saved.');
    }

    public function delete($id)
    {
        DailyExam::where('instruction_id', $id)->update(['instruction_id' => null]);
        ExamInstruction::whereKey($id)->delete();

        return redirect(url('admin/kpsc/exam-instructions'))->with('ok', 'Deleted.');
    }
}
