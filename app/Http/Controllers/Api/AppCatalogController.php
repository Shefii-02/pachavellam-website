<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\cms\HomeController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MyCourseController;
use App\Http\Controllers\PublicCatalogController;
use App\Http\Controllers\SeasonController;
use App\Models\CompetitiveExam;
use App\Models\ContentItem;
use App\Models\CourseAdmission;
use App\Models\ExamCourse;
use App\Models\ExamSeason;
use App\Models\PaymentLink;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentLinkService;
use App\Services\SeasonAccess;
use App\Services\StudentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * The website's pages as JSON for the apps (Flutter): home, exam type page, exam series with seasons, a season, bought courses, ranking.
 * Same data and same rules as the web pages - they share the controllers' data methods.
 *   GET  api/app/home
 *   GET  api/app/exams/{slug}
 *   GET  api/app/exams/{slug}/series/{series}     series = daily-exams | omr-exams | gk-exams | ca-exams | special-exams
 *   GET  api/app/seasons/{id}
 *   POST api/auth/seasons/{id}/buy                -> { url } payment page to open
 *   GET  api/app/ranking?exam=kpsc
 *   GET  api/auth/my-courses , api/auth/my-courses/{id}
 * A signed in student's token is optional on the public ones: with it the answer knows what is locked / bought.
 */
class AppCatalogController extends Controller
{
    private function img(?string $path): ?string
    {
        return $path ? url(Storage::url('files/' . $path)) : null;
    }

    private function abs(?string $u): ?string
    {
        if (!$u) {
            return null;
        }

        return preg_match('#^https?://#', $u) ? $u : url($u);
    }

    private function examType(CompetitiveExam $e): array
    {
        return ['id' => $e->id, 'slug' => $e->slug, 'name' => $e->name, 'short' => $e->short_name ?: $e->name, 'color' => $e->color ?: '#0a8f5b',
            'image' => $e->image ? url(Storage::url($e->image)) : null];
    }

    /** Where an exam link leads in the app: the native exam screens of the old KPSC exams, or the web page. */
    private function ref(?string $url): array
    {
        $url = (string) $url;
        if (preg_match('#kpsc/daily-exam/(\d{4}-\d{2}-\d{2})/(\d+)#', $url, $m)) {
            return ['type' => 'daily', 'date' => $m[1], 'id' => (int) $m[2]];
        }
        if (preg_match('#kpsc/model-exam/(\d{4}-\d{2}-\d{2})/(\d+)#', $url, $m)) {
            return ['type' => 'model', 'date' => $m[1], 'id' => (int) $m[2]];
        }
        if (preg_match('#/seasons/(\d+)#', $url, $m)) {
            return ['type' => 'season', 'id' => (int) $m[1]];
        }
        if (preg_match('#/courses/([^/?]+)#', $url, $m)) {
            return ['type' => 'course', 'slug' => $m[1]];
        }

        return ['type' => 'web'];
    }

    private function item($it): array
    {
        $it = (object) $it;

        return ['title' => $it->title ?? '', 'sub' => $it->sub ?? ($it->date ?? ''), 'tag' => $it->tag ?? '', 'tagc' => $it->tagc ?? null, 'state' => $it->state ?? null, 'lock' => (bool) ($it->lock ?? (($it->state ?? '') === 'locked')),
            'done' => (bool) ($it->done ?? false), 'inPaid' => (bool) ($it->inPaid ?? false), 'url' => $it->url ?? null, 'ref' => $this->ref($it->url ?? null)];
    }

    private function card($f): array
    {
        $f = (object) $f;
        $series = ['daily-exams' => 'daily-exams', 'omr-exams' => 'omr-exams', 'gk-exams' => 'gk-exams', 'premium-daily-ca' => 'ca-exams', 'special-exams' => 'special-exams'][$f->slug] ?? null;

        return [
            'key' => $f->slug, 'name' => $f->name, 'icon' => $f->icon ?? 'bi-check2-circle', 'description' => $f->description ?? null, 'total' => (int) ($f->total ?? 0), 'live' => (bool) ($f->live ?? false),
            'badge' => $f->badge ?? null, 'exam' => $f->examSlug ?? null, 'series' => $series, 'all' => $f->all ?? null, 'accent' => $f->accent ?? null,
            'items' => array_map(fn ($i) => $this->item($i), $f->items ?? []),
            'seasons' => array_map(fn ($s) => ['id' => $s->id ?? null, 'title' => $s->title, 'sub' => $s->sub ?? '', 'paid' => (bool) ($s->paid ?? false), 'price' => $s->price ?? null, 'total' => (int) ($s->total ?? 0),
                'live' => (bool) ($s->live ?? false), 'items' => array_map(fn ($i) => $this->item($i), $s->items ?? [])], $f->seasons ?? []),
        ];
    }

    private function content($c, string $kind = 'announcement'): array
    {
        return ['kind' => $kind, 'id' => $c->id, 'title' => $c->title, 'image' => $this->img($c->image), 'date' => optional($c->published_at ?? $c->created_at)->toDateString(),
            'exams' => $c->relationLoaded('exams') ? $c->exams->map(fn ($e) => $e->short_name ?: $e->name)->values() : [], 'pinned' => (bool) $c->is_pinned, 'url' => $this->abs($c->landing_url)];
    }

    private function promo($c): array
    {
        $vid = \App\Services\ClassSource::youtubeId((string) $c->link_url);

        return ['uid' => $c->uid, 'title' => $c->title, 'mode' => $c->mode, 'at' => optional($c->scheduled_at)->toIso8601String(), 'chat' => $c->chat_mode !== 'off',
            'image' => $c->image ? $this->img($c->image) : ($vid ? 'https://img.youtube.com/vi/' . $vid . '/hqdefault.jpg' : null), 'player' => $c->playerUrl(), 'url' => $c->publicUrl()];
    }

    private function course($c): array
    {
        return ['id' => $c->id, 'slug' => $c->slug, 'title' => $c->title, 'image' => $c->cover_image ? url(Storage::url($c->cover_image)) : null, 'free' => $c->isFree(), 'price' => $c->effectivePrice(), 'mrp' => $c->price,
            'validity' => $c->validityLabel(), 'exam' => $c->relationLoaded('exam') && $c->exam ? $this->examType($c->exam) : null, 'url' => url('courses/' . ($c->slug ?: $c->id))];
    }

    // ------------------------------------------------------------------ home

    public function home(Request $request)
    {
        auth()->shouldUse('api');
        $today = today();
        $live = fn ($kind) => ContentItem::ofKind($kind)->live()->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $today))->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $today));
        $examTypes = CompetitiveExam::where('status', 1)->orderBy('position')->with(['categories' => fn ($q) => $q->whereNull('parent_id')->where('status', 1)->orderBy('position')->orderBy('id')->with(['children' => fn ($c) => $c->where('status', 1)])])->get();
        $courses = ExamCourse::with('exam:id,short_name,name,color,slug')->where('status', 'published')->where('slug', 'not like', 'series-%')->orderByDesc('id')->limit(8)->get();

        return response()->json(['status' => 200, 'data' => [
            'examTypes' => $examTypes->map(fn ($e) => $this->examType($e) + ['categories' => $e->categories->map(fn ($c) => $this->categoryCard($c))->values()])->values(),
            'banners' => $live('banner')->orderBy('position')->get()->map(fn ($b) => ['image' => $this->img($b->image), 'title' => $b->title, 'url' => $this->abs($b->landing_url), 'ref' => $this->ref($b->landing_url)])->values(),
            'announcements' => $live('announcement')->with('exams:id,short_name,name,color')->orderByDesc('is_pinned')->orderByDesc('published_at')->orderByDesc('id')->limit(8)->get()->map(fn ($c) => $this->content($c))->values(),
            'articles' => ContentItem::ofKind('article')->live()->with('exams:id,short_name,name,color')->orderByDesc('published_at')->orderByDesc('id')->limit(8)->get()->map(fn ($c) => $this->content($c, 'article') + ['url' => url('articles/' . $c->id)])->values(),
            // the latest classes live on each exam type's own page, not on the home
            'freeTests' => $this->freeTests(),
            'courses' => $courses->map(fn ($c) => $this->course($c))->values(),
            'examSeries' => array_map(fn ($c) => $this->card($c), app(HomeController::class)->homeSeriesCards()),
            'ranking' => app(StudentProgress::class)->publicBoard(null, 20)->map(fn ($b) => ['pos' => $b->pos, 'name' => $b->name, 'coins' => $b->coins, 'tests' => $b->attempts, 'me' => auth()->check() && $b->uid === auth()->id()])->values(),
            'links' => ['android' => config('app_links.android'), 'ios' => config('app_links.ios'), 'site' => url('/'), 'logoLight' => \App\Models\SiteSetting::logo('light'), 'logoDark' => \App\Models\SiteSetting::logo('dark')],
        ]]);
    }

    // ------------------------------------------------------------------ one exam type

    public function exam(Request $request, $slug)
    {
        auth()->shouldUse('api');
        $d = app(PublicCatalogController::class)->examData($slug);
        $exam = $d['exam'];

        return response()->json(['status' => 200, 'data' => [
            'exam' => $this->examType($exam) + ['description' => $exam->description],
            'banners' => $d['banners']->map(fn ($b) => ['image' => $this->img($b->image), 'title' => $b->title, 'url' => $this->abs($b->landing_url), 'ref' => $this->ref($b->landing_url)])->values(),
            'tabs' => $d['tabs']->map(fn ($t) => ['title' => $t->title, 'image' => $this->img($t->image), 'url' => $this->abs($t->landing_url), 'bg' => $t->bg_color, 'color' => $t->text_color])->values(),
            'subjects' => collect($d['subjects'])->map(fn ($s) => ['title' => $s->title, 'image' => $s->image ? $this->abs($s->image) : null, 'color' => $s->color, 'url' => $this->abs($s->url)])->values(),
            'liveClasses' => $d['liveClasses']->map(fn ($c) => $this->promo($c))->values(),
            'recorded' => $d['recorded']->map(fn ($c) => $this->promo($c))->values(),
            'tests' => $d['tests']->map(fn ($t) => ['id' => $t->id, 'title' => $t->title, 'start' => optional($t->start_at)->toIso8601String(), 'end' => optional($t->end_at)->toIso8601String(), 'minutes' => $t->duration_minutes,
                'paid' => (bool) $t->is_paid, 'course' => $t->course ? ['id' => $t->course_id, 'slug' => $t->course->slug, 'title' => $t->course->title] : null])->values(),
            'courses' => $d['courses']->map(fn ($c) => $this->course($c))->values(),
            'mine' => $d['mine'],
            'announcements' => $d['announcements']->map(fn ($c) => $this->content($c))->values(),
            'articles' => $d['articles']->map(fn ($c) => $this->content($c, 'article') + ['url' => url('articles/' . $c->id)])->values(),
            'provide' => array_map(fn ($f) => $this->card($f), $d['provide']),
            'ranking' => $d['rankRows']->map(fn ($b) => ['pos' => $b->pos, 'name' => $b->name, 'coins' => $b->coins, 'tests' => $b->attempts, 'me' => auth()->check() && $b->uid === auth()->id()])->values(),
        ]]);
    }

    // ------------------------------------------------------------------ an exam series with its seasons

    public function series(Request $request, $slug, $series)
    {
        auth()->shouldUse('api');
        $d = app(PublicCatalogController::class)->seriesData($request, $slug, $series);

        return response()->json(['status' => 200, 'data' => [
            'exam' => $this->examType($d['exam']), 'series' => $d['series'], 'label' => $d['label'],
            'sessions' => array_map(fn ($s) => [
                'id' => $s->id, 'title' => $s->title, 'sub' => $s->sub, 'basis' => $s->basis, 'paid' => (bool) $s->paid, 'price' => $s->price, 'oldPrice' => $s->oldPrice, 'validity' => $s->validity,
                'freeCount' => (int) $s->freeN, 'owns' => (bool) $s->owns, 'total' => (int) $s->total, 'items' => array_map(fn ($i) => $this->item($i), $s->items),
            ], $d['active']),
            'others' => array_map(fn ($i) => $this->item($i), $d['rows']),
            'page' => $d['others']->currentPage(), 'lastPage' => $d['others']->lastPage(), 'total' => $d['others']->total(),
        ]]);
    }

    // ------------------------------------------------------------------ a season

    public function season(Request $request, $id)
    {
        auth()->shouldUse('api');
        $d = app(SeasonController::class)->data($request, $id, app(SeasonAccess::class));
        $s = $d['season'];
        $acc = $d['access'];

        return response()->json(['status' => 200, 'data' => [
            'id' => $s->id, 'title' => $s->title, 'tagline' => $s->tagline, 'description' => strip_tags((string) $s->description), 'kind' => $s->kind, 'exam' => $this->examType($d['exam']),
            'cover' => $s->cover_image ? url(Storage::url($s->cover_image)) : null, 'basis' => $s->basis, 'basisName' => $s->basis_name,
            'from' => optional($s->starts_on)->toDateString(), 'to' => optional($s->ends_on)->toDateString(),
            'paid' => $d['paid'], 'price' => $d['paid'] ? $acc->effectivePrice($s) : 0, 'mrp' => $d['paid'] ? (float) $s->price : 0, 'validity' => $d['paid'] ? $acc->validityLabel($s) : null,
            'freeCount' => $d['freeCount'], 'owns' => $d['owns'],
            'exams' => array_map(fn ($r) => $this->item($r), $d['rows']),
        ]]);
    }

    /** Buy a paid season: answers with the payment page to open. */
    public function seasonBuy(Request $request, $id, SeasonAccess $access, PaymentLinkService $links)
    {
        $season = ExamSeason::where('status', 'published')->findOrFail($id);
        if (!$access->isPaid($season)) {
            return response()->json(['status' => 422, 'message' => 'This season is free.'], 422);
        }
        $access->syncCourse($season);
        $course = ExamCourse::find($season->fresh()->course_id);
        if (!$course || $course->status !== 'published') {
            return response()->json(['status' => 422, 'message' => 'This season cannot be bought right now.'], 422);
        }
        if ($access->owns($request->user(), $season)) {
            return response()->json(['status' => 200, 'owned' => true, 'message' => 'You already have this season.']);
        }
        try {
            $link = PaymentLink::where('user_id', $request->user()->id)->where('course_id', $course->id)->where('channel', 'web')->where('status', 'active')->latest('id')->first();
            if (!$link || !$link->isUsable()) {
                $link = $links->create($request->user(), $course, 'web', null, 48, 'Bought from the app (exam season)', null);
            }

            return response()->json(['status' => 200, 'owned' => false, 'url' => $link->url()]);
        } catch (PaymentException $e) {
            return response()->json(['status' => 422, 'message' => $e->getMessage()], 422);
        }
    }

    // ------------------------------------------------------------------ ranking

    public function ranking(Request $request)
    {
        auth()->shouldUse('api');
        $slug = $request->filled('exam') ? (string) $request->exam : null;
        $rows = app(StudentProgress::class)->publicBoard($slug, min(50, max(5, (int) $request->query('limit', 20))));

        return response()->json(['status' => 200, 'data' => $rows->map(fn ($b) => ['pos' => $b->pos, 'name' => $b->name, 'coins' => $b->coins, 'tests' => $b->attempts, 'me' => auth()->check() && $b->uid === auth()->id()])->values()]);
    }

    // ------------------------------------------------------------------ bought courses

    public function myCourses(Request $request)
    {
        $rows = $request->user()->activeAdmissions()->filter(fn ($a) => $a->course && !str_starts_with((string) $a->course->slug, 'series-'));

        return response()->json(['status' => 200, 'data' => $rows->map(fn ($a) => $this->course($a->course) + [
            'validUntil' => optional($a->valid_until)->toDateString(), 'daysLeft' => $a->valid_until ? max(0, (int) ceil(now()->floatDiffInDays($a->valid_until, false))) : null,
        ])->values()]);
    }

    public function myCourse(Request $request, $id)
    {
        $d = app(MyCourseController::class)->data($request->user(), $id);
        if ($d instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json(['status' => 403, 'message' => 'Buy this course to open it.'], 403);
        }
        $row = fn ($r) => [
            'title' => $r->title, 'when' => $r->when ?? '', 'sub' => $r->sub ?? '', 'bucket' => $r->bucket ?? null, 'thumb' => $r->thumb ?? null, 'embed' => $r->embed ?? null, 'url' => $r->url ?? null,
            'locked' => (bool) ($r->locked ?? false), 'opens' => $r->opens ?? null, 'ref' => $this->ref($r->url ?? null),
        ];
        $node = function ($n) use (&$node) {
            return ['type' => $n->type, 'title' => $n->title, 'kind' => $n->kind, 'url' => $n->url, 'size' => $n->size, 'audio' => $n->audio, 'body' => $n->body, 'free' => $n->free, 'children' => array_map($node, $n->children)];
        };

        return response()->json(['status' => 200, 'data' => [
            'course' => $this->course($d['course']), 'preview' => $d['preview'], 'validUntil' => optional(optional($d['admission'])->valid_until)->toDateString(), 'daysLeft' => $d['daysLeft'], 'summary' => $d['summary'],
            'tabs' => array_map(fn ($t) => [
                'key' => $t['key'], 'name' => $t['name'], 'icon' => $t['icon'], 'kind' => $t['kind'], 'description' => $t['description'], 'count' => $t['count'] ?? 0,
                'live' => array_map($row, $t['live'] ?? []), 'upcoming' => array_map($row, $t['upcoming'] ?? []), 'done' => array_map($row, $t['done'] ?? []),
                'nodes' => array_map($node, $t['nodes'] ?? []), 'items' => array_map(fn ($i) => ['title' => $i->title, 'sub' => $i->sub], $t['items'] ?? []), 'link' => $t['link'] ?? null,
            ], $d['tabs']),
        ]]);
    }

    // ------------------------------------------------------------------ my account: coins, rank, progress, activity

    /** GET api/auth/account-summary?period=day|month|year - everything the My account dashboard of the website shows. */
    public function account(Request $request)
    {
        $user = $request->user();
        $p = app(StudentProgress::class);
        $period = in_array($request->query('period'), ['day', 'month', 'year'], true) ? $request->query('period') : 'day';
        $sum = $p->summary($user->id);
        $act = $p->activity($user->id);
        $board = $p->leaderboard($user->id, 10);

        return response()->json(['status' => 200, 'data' => [
            'coins' => $sum['coins'], 'testCoins' => $sum['testCoins'], 'bonusCoins' => $sum['bonusCoins'], 'rank' => $sum['rank'], 'ranked' => $sum['ranked'], 'topPercent' => $sum['topPercent'],
            'attempts' => $sum['attempts'], 'rights' => $sum['rights'], 'wrong' => $sum['wrong'], 'skipped' => $sum['skipped'], 'accuracy' => $sum['accuracy'], 'seconds' => $sum['secs'],
            'streak' => $sum['streak'], 'bestStreak' => $sum['bestStreak'], 'activeDays' => $sum['activeDays'], 'level' => $sum['level'],
            'byKind' => $sum['byKind']->map(fn ($k, $name) => ['kind' => $name, 'attempts' => $k->attempts, 'rights' => $k->rights, 'wrong' => $k->wrong, 'seconds' => $k->secs])->values(),
            'recent' => $sum['recent']->map(fn ($r) => ['title' => $r->title, 'kind' => $r->kind, 'rights' => (int) $r->rights, 'wrong' => (int) $r->wrong, 'skipped' => (int) $r->skipped, 'score' => $r->score, 'seconds' => (int) $r->secs, 'at' => $r->at])->values(),
            'activity' => ['visits' => $act['visits'], 'lastSeen' => $act['lastSeen'], 'classMinutes' => $act['classMinutes'], 'classesWatched' => $act['classesWatched'], 'comments' => $act['comments'], 'likes' => $act['likes'], 'discussions' => $act['discussions'],
                'devices' => collect($act['platforms'])->map(fn ($d) => ['platform' => $d->platform, 'version' => $d->app_version, 'device' => $d->device_model, 'last' => $d->last_active_at])->values()],
            'leaderboard' => ['rows' => collect($board['rows'])->map(fn ($b) => ['pos' => $b->pos, 'name' => $b->name, 'coins' => $b->coins, 'tests' => $b->attempts, 'me' => $b->me])->values()],
            'trend' => $p->trend($user->id, $period),
        ]]);
    }

    private function categoryCard($c): array
    {
        return ['id' => $c->id, 'slug' => $c->slug, 'name' => $c->name, 'image' => $c->image ? url(Storage::url($c->image)) : null, 'icon' => $c->icon,
            'subs' => $c->relationLoaded('children') ? $c->children->pluck('name')->take(8)->values() : [], 'url' => $c->publicUrl()];
    }

    /** Open free tests: free tests of an exam type and of free seasons, soonest first (each knows its exam type, so the app can filter). */
    private function freeTests(): array
    {
        $now = now();
        $exams = CompetitiveExam::pluck('slug', 'id');
        $rows = \App\Models\CourseExam::with('course:id,slug,title')->where('status', 'published')->where('is_paid', false)
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', $now))->whereNotNull('start_at')->where('start_at', '<=', $now->copy()->addDays(15))->orderBy('start_at')->limit(12)->get()
            ->map(fn ($t) => ['id' => $t->id, 'title' => $t->title, 'by' => $t->course->title ?? '', 'exam' => $exams[$t->exam_id] ?? null, 'start' => optional($t->start_at)->toIso8601String(), 'end' => optional($t->end_at)->toIso8601String(),
                'minutes' => $t->duration_minutes, 'tag' => 'Free test', 'url' => url('tests/' . $t->id)]);
        if (\Illuminate\Support\Facades\Schema::hasTable('exam_seasons')) {
            $access = app(SeasonAccess::class);
            $free = ExamSeason::where('status', 'published')->get()->reject(fn ($se) => $access->isPaid($se))->keyBy('id');
            if ($free->count()) {
                $rows = $rows->concat(\App\Models\CourseExam::where('status', 'published')->whereIn('season_id', $free->keys())->whereNotIn('id', $rows->pluck('id'))
                    ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', $now))->whereNotNull('start_at')->where('start_at', '<=', $now->copy()->addDays(15))->orderBy('start_at')->limit(12)->get()
                    ->map(fn ($t) => ['id' => $t->id, 'title' => $t->title, 'by' => $free[$t->season_id]->title, 'exam' => $exams[$t->exam_id] ?? null, 'start' => optional($t->start_at)->toIso8601String(), 'end' => optional($t->end_at)->toIso8601String(),
                        'minutes' => $t->duration_minutes, 'tag' => 'Season · Free test', 'url' => url('tests/' . $t->id)]));
            }
        }

        return $rows->sortBy('start')->values()->all();
    }

    // ------------------------------------------------------------------ categories (slug based)

    /** GET api/app/categories/{slug} - a category / sub category: its sub categories, articles, free videos, courses. */
    public function category(Request $request, $slug)
    {
        auth()->shouldUse('api');
        $cat = \App\Models\ExamCategory::with('exam')->where('status', 1)->where('slug', $slug)->first();
        if (!$cat || !$cat->exam || !$cat->exam->status) {
            return response()->json(['status' => 404, 'message' => 'Category not found.'], 404);
        }
        $ids = array_merge([$cat->id], $cat->descendantIds());
        $items = fn ($kind) => ContentItem::ofKind($kind)->live()->whereIn('category_id', $ids)->with('exams:id,short_name,name,color');

        return response()->json(['status' => 200, 'data' => [
            'id' => $cat->id, 'slug' => $cat->slug, 'name' => $cat->name, 'description' => strip_tags((string) $cat->description), 'exam' => $this->examType($cat->exam),
            'trail' => $cat->ancestors()->push($cat)->map(fn ($c) => ['slug' => $c->slug, 'name' => $c->name])->values(),
            'children' => $cat->children()->where('status', 1)->get()->map(fn ($c) => $this->categoryCard($c))->values(),
            'articles' => $items('article')->orderByDesc('published_at')->orderByDesc('id')->limit(12)->get()->map(fn ($c) => $this->content($c, 'article') + ['url' => url('articles/' . $c->id)])->values(),
            'videos' => $items('promo_class')->orderByRaw('COALESCE(scheduled_at, created_at) desc')->limit(12)->get()->map(fn ($c) => $this->promo($c))->values(),
            'courses' => ExamCourse::with('exam:id,short_name,name,color,slug')->where('status', 'published')->where('slug', 'not like', 'series-%')->inCategory($cat->id)->orderByDesc('is_featured')->orderByDesc('id')->limit(12)->get()->map(fn ($c) => $this->course($c))->values(),
        ]]);
    }

    // ------------------------------------------------------------------ free articles with playlists

    /** GET api/app/articles?exam=&folder=&page= - like api/app/classes: playlists (folders) at this level + the articles in it. */
    public function articles(Request $request)
    {
        $exam = $request->filled('exam') ? CompetitiveExam::where(fn ($q) => ctype_digit((string) $request->exam) ? $q->where('id', $request->exam) : $q->where('slug', $request->exam))->first() : null;
        $folder = $request->filled('folder') ? \App\Models\ContentFolder::where('kind', 'article')->find($request->folder) : null;
        $all = \App\Models\ContentFolder::where('kind', 'article')->get(['id', 'parent_id']);
        $own = ContentItem::ofKind('article')->live()->whereNotNull('folder_id')->selectRaw('folder_id, count(*) c')->groupBy('folder_id')->pluck('c', 'folder_id');
        $count = function ($id) use (&$count, $own, $all) {
            return (int) ($own[$id] ?? 0) + $all->where('parent_id', $id)->sum(fn ($f) => $count($f->id));
        };
        $lists = \App\Models\ContentFolder::where('kind', 'article')->where('parent_id', $folder?->id)->when($exam, fn ($q) => $q->where('exam_id', $exam->id))->orderBy('position')->orderBy('title')->get()->filter(fn ($f) => $count($f->id) > 0)->values();
        $page = ContentItem::ofKind('article')->live()->with('exams:id,short_name,name,color')->when($exam, fn ($w) => $w->forExam($exam->slug))->where('folder_id', $folder?->id)
            ->orderByDesc('published_at')->orderByDesc('id')->paginate(min(30, max(4, (int) $request->query('per', 12))));

        return response()->json(['status' => 200, 'data' => [
            'playlist' => $folder ? ['id' => $folder->id, 'title' => $folder->title, 'parent' => $folder->parent_id] : null,
            'playlists' => $request->integer('page', 1) > 1 ? [] : $lists->map(fn ($f) => ['id' => $f->id, 'title' => $f->title, 'count' => $count($f->id), 'image' => null])->values(),
            'items' => $page->getCollection()->map(fn ($c) => $this->content($c, 'article') + ['url' => url('articles/' . $c->id), 'audio' => (bool) $c->audioSrc()])->values(),
            'page' => $page->currentPage(), 'lastPage' => $page->lastPage(), 'total' => $page->total(),
        ]]);
    }

    // ------------------------------------------------------------------ courses (list + Udemy style detail)

    private function courseQuery(Request $request)
    {
        $exam = $request->filled('exam') ? CompetitiveExam::where('status', 1)->where(fn ($q) => ctype_digit((string) $request->exam) ? $q->where('id', $request->exam) : $q->where('slug', $request->exam))->first() : null;

        return ExamCourse::with('exam:id,short_name,name,color,slug,image')->where('status', 'published')->where('slug', 'not like', 'series-%')
            ->whereIn('exam_id', CompetitiveExam::where('status', 1)->select('id'))
            ->when($request->filled('exam'), fn ($q) => $q->where('exam_id', $exam?->id ?? 0))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('title', 'like', '%' . $request->q . '%')->orWhere('short_description', 'like', '%' . $request->q . '%')))
            ->when(in_array($request->type, ['free', 'paid'], true), fn ($q) => $q->where('course_type', $request->type))
            ->orderByDesc('is_featured')->orderBy('position')->orderByDesc('id');
    }

    /** GET api/app/courses?exam=kpsc&q=&type=free|paid&page=1&per=12 - published courses, newest / featured first, searchable, per exam type. */
    public function courses(Request $request)
    {
        auth()->shouldUse('api');
        $page = $this->courseQuery($request)->paginate(min(30, max(4, (int) $request->query('per', 12))));
        $mine = auth()->check() ? auth()->user()->activeAdmissions()->pluck('course_id')->all() : [];

        return response()->json(['status' => 200, 'data' => [
            'items' => $page->getCollection()->map(fn ($c) => $this->course($c) + ['short' => $c->short_description, 'level' => $c->level, 'hours' => $c->duration_hours, 'featured' => (bool) $c->is_featured, 'owned' => in_array($c->id, $mine)])->values(),
            'page' => $page->currentPage(), 'lastPage' => $page->lastPage(), 'total' => $page->total(),
        ]]);
    }

    /** GET api/app/courses/{slugOrId} - everything a course page shows: price, what you learn, what is included, tests (free ones can be tried), teachers. */
    public function courseDetail(Request $request, $slugOrId)
    {
        auth()->shouldUse('api');
        $c = ExamCourse::with(['exam:id,short_name,name,color,slug,image', 'features', 'members' => fn ($m) => $m->wherePivot('member_role', 'teacher')->select('users.id', 'users.name', 'users.image')])
            ->where('status', 'published')->where(fn ($q) => $q->where('slug', $slugOrId)->orWhere('id', ctype_digit((string) $slugOrId) ? $slugOrId : 0))->first();
        if (!$c) {
            return response()->json(['status' => 404, 'message' => 'Course not found.'], 404);
        }

        $user = auth()->user();
        $owned = $user && \App\Models\CourseAdmission::where('user_id', $user->id)->where('course_id', $c->id)->where('status', 'active')->get()->contains(fn ($a) => $a->grantsAccess());
        $engine = app(\App\Services\TestEngine::class);
        $tests = \App\Models\CourseExam::with('feature:id,name')->withCount('links')->where('course_id', $c->id)->where('status', 'published')->orderBy('position')->orderBy('id')->get()
            ->filter(fn ($t) => $t->links_count > 0)->each(fn ($t) => $t->free = $engine->access($user, $t)['state'] === 'free');
        $list = fn ($v) => collect(is_array($v) ? $v : (json_decode((string) $v, true) ?: []))->map(fn ($x) => is_array($x) ? ($x['text'] ?? $x['title'] ?? '') : (string) $x)->filter()->values();
        $off = $c->price > 0 && !$c->isFree() ? max(0, (int) round((1 - $c->effectivePrice() / $c->price) * 100)) : 0;

        return response()->json(['status' => 200, 'data' => $this->course($c) + [
            'short' => $c->short_description, 'description' => $c->description, 'audience' => $c->audience, 'level' => $c->level, 'language' => $c->language, 'hours' => $c->duration_hours,
            'certificate' => (bool) $c->certificate_enabled, 'promoVideo' => $c->promo_video_url, 'off' => $off, 'owned' => $owned, 'tags' => $c->tags ?? [],
            'learn' => $list($c->highlights), 'requirements' => $list($c->requirements),
            'includes' => $c->features->map(fn ($f) => ['name' => $f->name, 'image' => method_exists($f, 'imageUrl') ? $f->imageUrl() : null])->values(),
            'teachers' => $c->members->map(fn ($m) => ['name' => $m->name, 'image' => $m->image ? url('storage/users/' . $m->image) : null])->values(),
            'testCount' => $tests->count(), 'freeTests' => $tests->where('free', true)->count(),
            'groups' => $tests->groupBy(fn ($t) => $t->feature->name ?? 'Tests')->map(fn ($g, $name) => ['name' => $name, 'items' => $g->map(fn ($t) => ['title' => $t->title, 'free' => (bool) $t->free, 'minutes' => $t->duration_minutes ?? null])->values()])->values(),
        ]]);
    }

    /** POST api/auth/courses/{id}/join - free course: joined at once; paid: { url } payment page to open. */
    public function courseJoin(Request $request, $id, PaymentLinkService $links, \App\Services\Payments\AdmissionService $admissions)
    {
        $course = ExamCourse::where('status', 'published')->findOrFail($id);
        $user = $request->user();
        if (\App\Models\CourseAdmission::where('user_id', $user->id)->where('course_id', $course->id)->where('status', 'active')->get()->contains(fn ($a) => $a->grantsAccess())) {
            return response()->json(['status' => 200, 'owned' => true, 'message' => 'You already have this course.']);
        }
        try {
            if ($course->isFree()) {
                $admissions->checkout($user, $course, null, null, 'android');

                return response()->json(['status' => 200, 'owned' => true, 'message' => 'You have joined the course.']);
            }
            $link = $links->create($user, $course, 'android', $request->input('coupon_code'), 48, 'Bought from the app (course page)', null);

            return response()->json(['status' => 200, 'owned' => false, 'url' => $link->url()]);
        } catch (PaymentException $e) {
            return response()->json(['status' => 422, 'message' => $e->getMessage()], 422);
        }
    }

    // ------------------------------------------------------------------ free / live classes + playlists

    /** Number of live classes in a playlist and everything below it. */
    private function playlistCounts(): array
    {
        $all = \App\Models\ContentFolder::where('kind', 'promo_class')->get(['id', 'parent_id']);
        $own = ContentItem::ofKind('promo_class')->live()->whereNotNull('folder_id')->selectRaw('folder_id, count(*) c')->groupBy('folder_id')->pluck('c', 'folder_id');
        $count = function ($id) use (&$count, $own, $all) {
            return (int) ($own[$id] ?? 0) + $all->where('parent_id', $id)->sum(fn ($f) => $count($f->id));
        };

        return $all->mapWithKeys(fn ($f) => [$f->id => $count($f->id)])->all();
    }

    /**
     * GET api/app/classes?exam=kpsc&folder=&mode=live|recorded&page=1&per=12
     * Free classes, newest first, for one exam type (or all). A playlist = a folder of free classes: without ?folder the answer lists
     * the top level playlists + the loose classes; with ?folder=ID the playlists inside it + the classes in it.
     */
    public function classes(Request $request)
    {
        $exam = $request->filled('exam') ? CompetitiveExam::where(fn ($q) => ctype_digit((string) $request->exam) ? $q->where('id', $request->exam) : $q->where('slug', $request->exam))->first() : null;
        $folder = $request->filled('folder') ? \App\Models\ContentFolder::where('kind', 'promo_class')->find($request->folder) : null;
        $counts = $this->playlistCounts();

        $lists = \App\Models\ContentFolder::where('kind', 'promo_class')->where('parent_id', $folder?->id)->when($exam, fn ($q) => $q->where('exam_id', $exam->id))->orderBy('position')->orderBy('title')->get()
            ->filter(fn ($f) => ($counts[$f->id] ?? 0) > 0)->values();
        $covers = ContentItem::ofKind('promo_class')->live()->whereIn('folder_id', $lists->pluck('id'))->whereNotNull('image')->orderByDesc('id')->get(['folder_id', 'image'])->unique('folder_id')->pluck('image', 'folder_id');

        $q = ContentItem::ofKind('promo_class')->live()->with('exams:id,short_name,name,color')
            ->when($exam, fn ($w) => $w->forExam($exam->slug))
            ->when(in_array($request->mode, ['live', 'recorded'], true), fn ($w) => $w->where('mode', $request->mode))
            ->when($request->filled('q'), fn ($w) => $w->where('title', 'like', '%' . $request->q . '%'))
            // without a search, the list is the level being browsed; a search looks through every playlist
            ->when(!$request->filled('q'), fn ($w) => $w->where('folder_id', $folder?->id))
            ->orderByRaw('COALESCE(content_items.scheduled_at, content_items.created_at) desc')->orderByDesc('content_items.id');
        $page = $q->paginate(min(30, max(4, (int) $request->query('per', 12))));

        return response()->json(['status' => 200, 'data' => [
            'exam' => $exam ? $this->examType($exam) : null,
            'playlist' => $folder ? ['id' => $folder->id, 'title' => $folder->title, 'parent' => $folder->parent_id] : null,
            'playlists' => $request->integer('page', 1) > 1 ? [] : $lists->map(fn ($f) => ['id' => $f->id, 'title' => $f->title, 'count' => $counts[$f->id] ?? 0, 'image' => !empty($covers[$f->id]) ? $this->img($covers[$f->id]) : null])->values(),
            'items' => $page->getCollection()->map(fn ($c) => $this->promo($c) + ['exams' => $c->exams->map(fn ($e) => $e->short_name ?: $e->name)->values()])->values(),
            'page' => $page->currentPage(), 'lastPage' => $page->lastPage(), 'total' => $page->total(),
        ]]);
    }

    /** GET api/app/classes/playlists?exam=kpsc - the top level playlists of free classes (for the home / exam page strip). */
    public function playlists(Request $request)
    {
        $request->query->set('page', 1);

        return $this->classes($request);
    }

    // ------------------------------------------------------------------ ads

    /** GET api/app/ads - what the app may show (only what the panel has switched on; a student who bought a course may be exempt). */
    public function ads(Request $request)
    {
        auth()->shouldUse('api');
        $on = \App\Support\Ads::platformOn('app');
        $paid = $on && auth()->check() && auth()->user()->activeAdmissions()->isNotEmpty();
        $units = $on ? \App\Support\Ads::units('app')->filter(fn ($u) => $u->enabled && !($paid && $u->hide_for_paid))->map(fn ($u) => [
            'type' => $u->type, 'android' => $u->unit_android, 'ios' => $u->unit_ios, 'pages' => array_values((array) $u->pages),
            'everyN' => (int) $u->every_n, 'maxPerPage' => (int) $u->max_per_page, 'startPage' => (int) $u->start_page, 'capMinutes' => (int) $u->cap_minutes,
        ])->values() : [];

        return response()->json(['status' => 200, 'data' => ['enabled' => $on && count($units) > 0, 'units' => $units,
            'quietMinutes' => (int) \App\Models\SiteSetting::get('ads_quiet_minutes', '3'), 'sessionMax' => (int) \App\Models\SiteSetting::get('ads_session_max', '3')]]);
    }

    // ------------------------------------------------------------------ stories

    /** GET api/app/stories?exam=kpsc&page=1&per=10 - the pictures / videos strip of the home and exam type pages. */
    public function stories(Request $request)
    {
        $exam = $request->filled('exam') ? (ctype_digit((string) $request->exam) ? (int) $request->exam : (int) CompetitiveExam::where('slug', $request->exam)->value('id')) : 0;
        $request->merge(['exam' => $exam]);
        $d = json_decode(app(PublicCatalogController::class)->storiesFeed($request)->getContent(), true);
        $items = collect($d['items'] ?? [])->map(fn ($i) => array_map(fn ($v) => is_string($v) && str_starts_with($v, '/') && !str_starts_with($v, '//') ? url($v) : $v, $i))->values();

        return response()->json(['status' => 200, 'data' => ['items' => $items, 'total' => $d['total'] ?? 0, 'next' => (bool) ($d['next'] ?? false)]]);
    }

    // ------------------------------------------------------------------ article / announcement details

    private function contentDetail(string $kind, $id)
    {
        $c = ContentItem::ofKind($kind)->live()->with('exams:id,short_name,name,color')->findOrFail($id);

        return response()->json(['status' => 200, 'data' => [
            'id' => $c->id, 'kind' => $kind, 'title' => $c->title, 'image' => $this->img($c->image), 'date' => optional($c->published_at ?? $c->created_at)->toDateString(),
            'exams' => $c->exams->map(fn ($e) => $e->short_name ?: $e->name)->values(), 'html' => $c->descriptionHtml(), 'link' => $c->link_url,
            'attachment' => $c->attachment ? url(Storage::url('files/' . $c->attachment)) : null, 'url' => url($kind === 'article' ? 'articles/' . $c->id : 'announcements/' . $c->id),
            'audio' => $kind === 'article' ? $c->audioSrc() : null, 'speak' => $kind === 'article' ? $c->speakText() : null,
        ]]);
    }

    public function article(Request $request, $id)
    {
        return $this->contentDetail('article', $id);
    }

    public function announcement(Request $request, $id)
    {
        return $this->contentDetail('announcement', $id);
    }
}
