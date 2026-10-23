<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\DeviceToken;
use App\Models\PushCampaign;
use App\Services\PushNotifier;
use App\Support\Landing;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Push notifications sent from the panel (admin/push): to every device of everyone in the audience. */
class PushController extends Controller
{
    public function index(Request $request)
    {
        StaffAccess::authorize($request, 'Content manage');

        return view('admin.content.push', [
            'history' => PushCampaign::orderByDesc('id')->paginate(per_page(20))->withQueryString(),
            'exams' => CompetitiveExam::orderBy('position')->get(),
            'devices' => DeviceToken::select('platform', DB::raw('count(*) as n'), DB::raw('count(distinct user_id) as people'))->groupBy('platform')->get()->keyBy('platform'),
            'people' => DeviceToken::distinct('user_id')->count('user_id'),
        ]);
    }

    public function send(Request $request)
    {
        StaffAccess::authorize($request, 'Content manage');
        $data = $request->validate([
            'title' => 'required|string|max:120', 'body' => 'required|string|max:300',
            'audience' => 'required|in:all,interested,exams', 'exam_ids' => 'required_if:audience,exams|array', 'exam_ids.*' => 'integer|exists:competitive_exams,id',
            'platforms' => 'nullable|array', 'platforms.*' => 'in:android,ios,web',
        ], ['exam_ids.required_if' => 'Tick at least one exam type.']);
        [$tt, $tv] = Landing::fromRequest($request);
        if ($request->input('target_type') === 'link') {
            $tt = $request->filled('link_url') ? 'link' : 'none';
            $tv = null;
        }

        $c = PushNotifier::create([
            'title' => $data['title'], 'body' => $data['body'], 'target_type' => $tt === 'link' ? 'link' : $tt, 'target_value' => $tt === 'link' ? $request->input('link_url') : $tv,
            'audience' => $data['audience'], 'audience_exam_ids' => $data['audience'] === 'exams' ? array_map('intval', $data['exam_ids']) : null,
            'platforms' => $data['platforms'] ?? null, 'created_by' => $request->user()->id,
        ]);
        // a custom link is carried as the landing value
        dispatch(fn () => app(PushNotifier::class)->send($c))->afterResponse();

        return redirect(url('admin/push'))->with('message', 'Push notification is being sent');
    }

    /** Preview how many devices a send would reach. */
    public function preview(Request $request)
    {
        StaffAccess::authorize($request, 'Content manage');
        $c = new PushCampaign(['audience' => $request->input('audience', 'all'), 'audience_exam_ids' => array_map('intval', (array) $request->input('exam_ids')), 'platforms' => $request->input('platforms') ?: null]);
        $d = app(PushNotifier::class)->devices($c);

        return response()->json(['devices' => $d->count(), 'people' => $d->pluck('user_id')->unique()->count()]);
    }
}
