<?php

namespace App\Http\Controllers\cms;

use App\Http\Controllers\Controller;
use App\Models\CaSeason;
use App\Models\DailyExam;
use App\Models\DailyExamdetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Current Affairs exams in seasons:  admin/kpsc/ca-daily-exam
 *
 *   season  ->  daily exams (one per day)           exam_kind = daily
 *           ->  weekly / weekend / other exams      built by hand or pulled from the daily exams
 */
class CaSeasonController extends Controller
{
    private const SECTION = 'CA-Daily-Exam';

    // ---------------------------------------------------------------- seasons

    public function index(Request $request)
    {
        $q = CaSeason::query()
            ->when($request->filled('q'), fn ($w) => $w->where('name', 'like', '%' . $request->q . '%'))
            ->when($request->filled('status'), fn ($w) => $w->where('status', $request->status))
            ->when($request->filled('tag'), fn ($w) => $w->whereJsonContains('tags', $request->tag));

        $seasons = $q->orderByDesc('is_featured')->orderBy('position')->orderByDesc('id')->get();

        // exams per season and kind, questions and attempts - one query each, not per season
        $examCounts = DailyExam::where('section', self::SECTION)->whereNotNull('season_id')
            ->select('season_id', 'exam_kind', DB::raw('COUNT(*) as c'))->groupBy('season_id', 'exam_kind')->get()
            ->groupBy('season_id')->map(fn ($g) => $g->pluck('c', 'exam_kind'));

        $questionCounts = DB::table('kpsc_daily_exams_details as d')->join('kpsc_daily_exams as e', 'e.id', '=', 'd.exam_id')
            ->where('e.section', self::SECTION)->whereNotNull('e.season_id')
            ->select('e.season_id', DB::raw('COUNT(*) as c'))->groupBy('e.season_id')->pluck('c', 'season_id');

        $attemptCounts = DB::table('kpsc_ca_daily_exam_attempts as a')->join('kpsc_daily_exams as e', 'e.id', '=', 'a.exam_id')
            ->where('e.section', self::SECTION)->whereNotNull('e.season_id')
            ->select('e.season_id', DB::raw('COUNT(*) as c'))->groupBy('e.season_id')->pluck('c', 'season_id');

        $allTags = CaSeason::pluck('tags')->flatten()->filter()->unique()->sort()->values();

        return view('cms.ca-season.index', compact('seasons', 'examCounts', 'questionCounts', 'attemptCounts', 'allTags'));
    }

    public function create()
    {
        return view('cms.ca-season.form', ['season' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedSeason($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['created_by'] = auth()->id();

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('ca-seasons', 'public');
        }

        $season = CaSeason::create($data);
        $this->keepSingleFeatured($season);

        return redirect(kpsc_cms('ca-daily-exam/seasons/' . $season->id))->with('message', 'Season created. Add its first exam below.');
    }

    public function edit($id)
    {
        return view('cms.ca-season.form', ['season' => CaSeason::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $season = CaSeason::findOrFail($id);
        $data = $this->validatedSeason($request, $season);

        if ($request->hasFile('cover_image')) {
            $this->deleteFile($season->cover_image);
            $data['cover_image'] = $request->file('cover_image')->store('ca-seasons', 'public');
        } elseif ($request->boolean('remove_cover')) {
            $this->deleteFile($season->cover_image);
            $data['cover_image'] = null;
        }

        $season->update($data);
        $this->keepSingleFeatured($season);

        return redirect(kpsc_cms('ca-daily-exam/seasons/' . $season->id))->with('message', 'Season updated');
    }

    public function destroy($id)
    {
        $season = CaSeason::findOrFail($id);

        if ($season->exams()->exists()) {
            return back()->with('error', 'This season still has exams. Set it to Archived instead, or delete its exams first.');
        }

        $this->deleteFile($season->cover_image);
        $season->delete();

        return redirect(kpsc_cms('ca-daily-exam'))->with('message', 'Season deleted');
    }

    public function show($id)
    {
        $season = CaSeason::findOrFail($id);

        $exams = $season->exams()->withCount(['exam_details', 'ca_exam_attened'])
            ->orderBy('slot_no')->orderBy('id')->get();

        $daily = $exams->where('exam_kind', 'daily')->values();
        $others = $exams->where('exam_kind', '!=', 'daily')->sortBy([['exam_kind', 'asc'], ['slot_no', 'asc']])->values();

        // which daily exams each weekly / weekend exam was built from
        $sources = DB::table('kpsc_ca_exam_sources as s')->join('kpsc_daily_exams as e', 'e.id', '=', 's.source_exam_id')
            ->whereIn('s.exam_id', $others->pluck('id'))
            ->select('s.exam_id', 'e.slot_no', 's.question_count')->orderBy('e.slot_no')->get()->groupBy('exam_id');

        // heads-up for a running daily season: is tomorrow's exam ready?
        $notice = null;
        if ($season->isOngoing() && in_array($season->schedule_type, ['daily', 'mixed'])) {
            $tomorrow = Carbon::tomorrow()->toDateString();
            if (!$daily->contains(fn ($e) => $e->exam_date == $tomorrow)) {
                $notice = 'No daily exam is scheduled for tomorrow (' . Carbon::tomorrow()->format('D, d M') . ').';
            } elseif ($daily->contains(fn ($e) => $e->exam_date == $tomorrow && !$e->exam_details_count)) {
                $notice = "Tomorrow's daily exam has no questions yet.";
            }
        }

        return view('cms.ca-season.show', compact('season', 'daily', 'others', 'sources', 'notice'));
    }

    // ------------------------------------------------------------------ exams

    /** Form for one exam of a season (daily / weekly / weekend / monthly / other). */
    public function examCreate(Request $request, $id)
    {
        $season = CaSeason::findOrFail($id);
        $kind = array_key_exists($request->kind, CaSeason::KINDS) ? $request->kind : 'daily';

        return view('cms.ca-season.exam-form', [
            'season' => $season,
            'kind' => $kind,
            'nextSlot' => $this->nextSlot($season->id, $kind),
            'suggestedStart' => $this->suggestedStart($season, $kind),
        ]);
    }

    public function examStore(Request $request, $id)
    {
        $season = CaSeason::findOrFail($id);

        $data = $request->validate([
            'exam_kind' => ['required', Rule::in(array_keys(CaSeason::KINDS))],
            'slot_no' => 'required|integer|min:1|max:9999',
            'exam_title' => 'nullable|string|max:255',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after:started_at',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
        ]);

        $this->assertSlotFree($season->id, $data['exam_kind'], $data['slot_no']);

        $exam = $this->makeExam($season, $data, $request->boolean('status'));

        return redirect(kpsc_cms('ca-daily-exam/edit/' . $exam->id))
            ->with('message', 'Exam created. Add its questions below' . ($data['exam_kind'] === 'daily' ? '.' : ', or pull them from the daily exams.'));
    }

    public function togglePublish($examId)
    {
        $exam = DailyExam::where('section', self::SECTION)->findOrFail($examId);
        $exam->status = $exam->status ? 0 : 1;
        $exam->save();

        return back()->with('message', $exam->status ? 'Published' : 'Unpublished (hidden from the app)');
    }

    // ---------------------------------------------- weekly / weekend builder

    /** Builder: pick a period, tick the daily exams, choose how many questions to take. */
    public function build(Request $request, $id)
    {
        $season = CaSeason::findOrFail($id);
        $kind = in_array($request->kind, ['weekly', 'weekend', 'monthly', 'other']) ? $request->kind : 'weekend';

        $existing = $season->exams()->where('exam_kind', '!=', 'daily')->orderByDesc('id')->get(['id', 'exam_kind', 'slot_no', 'exam_title']);
        $target = $request->filled('exam_id') ? $existing->firstWhere('id', (int) $request->exam_id) : null;

        // default period: the 7 days before today (weekend exams are usually taken on Sat/Sun for that week)
        $to = $target?->period_end ?: Carbon::today();
        $from = $target?->period_start ?: Carbon::parse($to)->subDays(6);

        return view('cms.ca-season.build', [
            'season' => $season,
            'kind' => $target?->exam_kind ?: $kind,
            'existing' => $existing,
            'target' => $target,
            'nextSlot' => $this->nextSlot($season->id, $target?->exam_kind ?: $kind),
            'suggestedStart' => $this->suggestedStart($season, $kind),
            'from' => Carbon::parse($from)->toDateString(),
            'to' => Carbon::parse($to)->toDateString(),
        ]);
    }

    /** JSON: the season's daily exams inside a date range (refreshes the checklist). */
    public function buildPreview(Request $request, $id)
    {
        $season = CaSeason::findOrFail($id);
        $request->validate(['from' => 'required|date', 'to' => 'required|date|after_or_equal:from']);

        $rows = $season->exams()->where('exam_kind', 'daily')
            ->whereBetween('exam_date', [$request->from, $request->to])
            ->withCount('exam_details')->orderBy('exam_date')->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'slot' => $e->slot_no,
                'date' => Carbon::parse($e->exam_date)->format('D, d M'),
                'title' => $e->exam_title ?: 'Day ' . $e->slot_no,
                'questions' => $e->exam_details_count,
                'published' => (bool) $e->status,
            ]);

        return response()->json(['data' => $rows]);
    }

    public function buildStore(Request $request, $id)
    {
        $season = CaSeason::findOrFail($id);

        $data = $request->validate([
            'exam_id' => 'nullable|integer',
            'exam_kind' => ['required_without:exam_id', 'nullable', Rule::in(['weekly', 'weekend', 'monthly', 'other'])],
            'slot_no' => 'required_without:exam_id|nullable|integer|min:1|max:9999',
            'exam_title' => 'nullable|string|max:255',
            'started_at' => 'required_without:exam_id|nullable|date',
            'ended_at' => 'nullable|date',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'source_ids' => 'required|array|min:1',
            'source_ids.*' => 'integer',
            'mode' => ['required', Rule::in(['all', 'per_exam', 'total'])],
            'n' => 'nullable|integer|min:1|max:1000',
        ], ['source_ids.required' => 'Tick at least one daily exam to take questions from.']);

        if ($data['mode'] !== 'all' && empty($data['n'])) {
            return back()->withInput()->withErrors(['n' => 'Enter how many questions to take.']);
        }

        // sources must be daily exams of THIS season
        $sources = $season->exams()->where('exam_kind', 'daily')->whereIn('id', $data['source_ids'])->get();
        if ($sources->isEmpty()) {
            return back()->withInput()->withErrors(['source_ids' => 'Those daily exams do not belong to this season.']);
        }

        return DB::transaction(function () use ($request, $season, $data, $sources) {
            if (!empty($data['exam_id'])) {
                $exam = $season->exams()->where('exam_kind', '!=', 'daily')->findOrFail($data['exam_id']);
                $exam->period_start = $data['period_start'];
                $exam->period_end = $data['period_end'];
                $exam->save();
            } else {
                $this->assertSlotFree($season->id, $data['exam_kind'], $data['slot_no']);
                $exam = $this->makeExam($season, $data, $request->boolean('status'));
            }

            // questions already in the exam (from an earlier pull) are never copied twice
            $have = DailyExamdetails::where('exam_id', $exam->id)->whereNotNull('source_detail_id')->pluck('source_detail_id')->all();

            $pool = DailyExamdetails::whereIn('exam_id', $sources->pluck('id'))->whereNotIn('id', $have)->get()->groupBy('exam_id');

            $picked = collect();
            if ($data['mode'] === 'all') {
                $picked = $pool->flatten(1);
            } elseif ($data['mode'] === 'per_exam') {
                $picked = $pool->flatMap(fn ($qs) => $qs->shuffle()->take($data['n']));
            } else {
                $picked = $pool->flatten(1)->shuffle()->take($data['n']);
            }

            $position = (int) DailyExamdetails::where('exam_id', $exam->id)->max('position');
            $now = now();
            $rows = $picked->values()->map(function ($q, $i) use ($exam, $position, $now) {
                return [
                    'exam_id' => $exam->id,
                    'source_detail_id' => $q->id,
                    'question' => $q->question,
                    'option_1' => $q->option_1,
                    'option_2' => $q->option_2,
                    'option_3' => $q->option_3,
                    'option_4' => $q->option_4,
                    'currect_ans' => $q->currect_ans,
                    'notes' => $q->notes,
                    'position' => $position + $i + 1,
                    'status' => $q->status,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->all();

            foreach (array_chunk($rows, 200) as $chunk) {
                DailyExamdetails::insert($chunk);
            }

            // remember where the questions came from
            foreach ($picked->groupBy('exam_id') as $sourceId => $qs) {
                $row = DB::table('kpsc_ca_exam_sources')->where('exam_id', $exam->id)->where('source_exam_id', $sourceId)->first();
                if ($row) {
                    DB::table('kpsc_ca_exam_sources')->where('id', $row->id)->update(['question_count' => $row->question_count + $qs->count(), 'mode' => $data['mode'] === 'all' ? 'all' : 'random', 'updated_at' => $now]);
                } else {
                    DB::table('kpsc_ca_exam_sources')->insert(['exam_id' => $exam->id, 'source_exam_id' => $sourceId, 'mode' => $data['mode'] === 'all' ? 'all' : 'random', 'question_count' => $qs->count(), 'created_at' => $now, 'updated_at' => $now]);
                }
            }

            return redirect(kpsc_cms('ca-daily-exam/edit/' . $exam->id))
                ->with('message', $picked->count() . ' question(s) added from ' . $picked->pluck('exam_id')->unique()->count() . ' daily exam(s). Review them below, edit or remove any, then publish.');
        });
    }

    // --------------------------------------------------------------- helpers

    private function makeExam(CaSeason $season, array $data, bool $publish): DailyExam
    {
        $kind = $data['exam_kind'];
        $slot = (int) $data['slot_no'];
        $started = Carbon::parse($data['started_at']);

        $exam = new DailyExam();
        $exam->section = self::SECTION;
        $exam->season_id = $season->id;
        $exam->exam_kind = $kind;
        $exam->slot_no = $slot;
        $exam->level = $slot;                       // the app orders by level and shows "day" from examtitle
        $exam->examtitle = (string) $slot;
        $exam->exam_title = ($data['exam_title'] ?? null) ?: (CaSeason::KINDS[$kind] === 'Daily' ? 'Day ' . $slot : CaSeason::KINDS[$kind] . ' ' . $slot);
        $exam->exam_date = $started->toDateString();
        $exam->started_at = $started->toDateTimeString();
        $exam->ended_at = !empty($data['ended_at']) ? Carbon::parse($data['ended_at'])->toDateTimeString() : null;
        $exam->period_start = $data['period_start'] ?? null;
        $exam->period_end = $data['period_end'] ?? null;
        $exam->status = $publish ? 1 : 0;
        $exam->save();

        return $exam;
    }

    private function nextSlot(int $seasonId, string $kind): int
    {
        return (int) DailyExam::where('section', self::SECTION)->where('season_id', $seasonId)->where('exam_kind', $kind)->max('slot_no') + 1;
    }

    private function assertSlotFree(int $seasonId, string $kind, int $slot): void
    {
        $taken = DailyExam::where('section', self::SECTION)->where('season_id', $seasonId)->where('exam_kind', $kind)->where('slot_no', $slot)->exists();
        if ($taken) {
            throw \Illuminate\Validation\ValidationException::withMessages(['slot_no' => CaSeason::KINDS[$kind] . " number {$slot} already exists in this season."]);
        }
    }

    /** Suggested start: tomorrow 6 AM for daily, the coming Saturday for weekend, next Monday for weekly. */
    private function suggestedStart(CaSeason $season, string $kind): string
    {
        $d = match ($kind) {
            'weekend' => Carbon::now()->next(Carbon::SATURDAY),
            'weekly' => Carbon::now()->next(Carbon::MONDAY),
            default => Carbon::tomorrow(),
        };
        $latest = $season->exams()->where('exam_kind', $kind)->max('started_at');
        if ($kind === 'daily' && $latest && Carbon::parse($latest)->gte(Carbon::today())) {
            $d = Carbon::parse($latest)->addDay();   // continue the running series
        }

        return $d->setTime(6, 0)->format('Y-m-d\TH:i');
    }

    private function validatedSeason(Request $request, ?CaSeason $season = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(array_keys(CaSeason::STATUSES))],
            'schedule_type' => ['required', Rule::in(array_keys(CaSeason::SCHEDULES))],
            'starts_on' => 'nullable|date',
            'ends_on' => 'nullable|date|after_or_equal:starts_on',
            'tags' => 'nullable|string|max:500',
            'position' => 'nullable|integer|min:0',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $data['tags'] = collect(explode(',', (string) ($data['tags'] ?? '')))
            ->map(fn ($t) => Str::limit(trim($t), 30, ''))->filter()->unique()->take(10)->values()->all();
        $data['position'] = $data['position'] ?? 0;
        $data['is_featured'] = $request->boolean('is_featured');
        unset($data['cover_image']);

        return $data;
    }

    private function keepSingleFeatured(CaSeason $season): void
    {
        if ($season->is_featured) {
            CaSeason::where('id', '!=', $season->id)->update(['is_featured' => false]);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'season';
        $slug = $base;
        for ($i = 2; CaSeason::where('slug', $slug)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }

        return $slug;
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
