<?php

namespace App\Http\Controllers;

use App\Models\CompetitiveExam;
use App\Models\ContentItem;
use App\Models\ExamCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Public landing pages that banners, stories and push notifications can open: one course, one exam type. */
class PublicCatalogController extends Controller
{
    public function course($slugOrId)
    {
        $course = ExamCourse::with('exam')->where('status', 'published')
            ->where(fn ($q) => $q->where('slug', $slugOrId)->orWhere('id', ctype_digit((string) $slugOrId) ? $slugOrId : 0))->firstOrFail();

        // students who already own the course go straight to it
        if (auth()->check() && \App\Models\CourseAdmission::where('user_id', auth()->id())->where('course_id', $course->id)->where('status', 'active')->get()->contains(fn ($a) => $a->grantsAccess())) {
            return redirect(url('my-courses/' . $course->id));
        }
        \App\Support\Seo::set(['key' => 'course:' . $course->id, 'title' => ($course->meta_title ?: $course->title) . ' | Pachavellam', 'description' => $course->meta_description ?? strip_tags((string) ($course->description ?? '')) ?: null,
            'image' => !empty($course->thumbnail) ? \Illuminate\Support\Facades\Storage::url($course->thumbnail) : null]);

        // the course's tests, grouped by kind ("What you'll get"); free ones can be taken before buying
        $engine = app(\App\Services\TestEngine::class);
        $tests = \App\Models\CourseExam::with('feature:id,name', 'course:id,price,discount_price,course_type')->withCount('links')->where('course_id', $course->id)->where('status', 'published')
            ->orderBy('position')->orderBy('id')->get()->filter(fn ($t) => $t->links_count > 0)
            ->each(fn ($t) => $t->free = $engine->access(null, $t)['state'] === 'free');
        $groups = $tests->groupBy(fn ($t) => $t->feature->name ?? 'Tests');

        return view('kpsc.course', ['course' => $course, 'title' => $course->meta_title ?: $course->title, 'groups' => $groups, 'testCount' => $tests->count(), 'freeTests' => $tests->where('free', true)->count()]);
    }

    /**
     * Active Season of one exam series (daily / omr / ca / gk / special) of an exam type, each with its exams newest first,
     * its price (free or paid) and which exams are locked for this student. KPSC's old CA seasons are included for the CA series.
     * Returns [] when there is no active season (the card then shows the latest exams instead).
     */
    private function seasonsFor(CompetitiveExam $exam, string $kind, bool $kpsc, int $seasonLimit = 3, int $itemLimit = 6): array
    {
        $today = today();
        $user = auth()->user();
        $access = app(\App\Services\SeasonAccess::class);
        $legacy = ['daily' => 'Daily Exam', 'omr' => 'Model Exam', 'ca' => 'CA-Daily-Exam'][$kind] ?? null;
        $money = fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.');
        $out = [];

        if (\Illuminate\Support\Facades\Schema::hasTable('exam_seasons')) {
            $seasons = \App\Models\ExamSeason::where('exam_id', $exam->id)->where('kind', $kind)->where('status', 'published')
                ->where(fn ($w) => $w->whereNull('starts_on')->orWhere('starts_on', '<=', $today))->where(fn ($w) => $w->whereNull('ends_on')->orWhere('ends_on', '>=', $today))
                ->orderBy('position')->orderByDesc('id')->limit($seasonLimit)->get();   // the show order the admin set
            $now = now();
            foreach ($seasons as $se) {
                $items = collect();
                if ($kpsc && $legacy) {
                    $items = $items->concat(\App\Models\DailyExam::where('series_season_id', $se->id)->where('section', $legacy)->where('status', 1)->get()->map(function ($d) use ($se, $access, $user) {
                        $on = $d->started_at && $d->ended_at && now()->between($d->started_at, $d->ended_at);
                        $st = $access->state($user, $se, 'legacy', $d->id);

                        return (object) ['title' => $d->exam_title ?: ($d->examtitle ?: 'Exam'), 'sub' => $d->exam_date ? \Illuminate\Support\Carbon::parse($d->exam_date)->format('d M Y') : '', 'sort' => $d->exam_date . sprintf('%08d', $d->id),
                            'tag' => $st === 'locked' ? 'Paid' : ($st === 'free' && $access->isPaid($se) ? 'Free' : ($on ? 'Live now' : ($d->started_at && \Illuminate\Support\Carbon::parse($d->started_at)->isFuture() ? 'Upcoming' : 'Exam'))),
                            'url' => $st === 'locked' ? $access->buyUrl($se) : url(($d->section === 'Model Exam' ? 'kpsc/model-exam' : 'kpsc/daily-exam') . '/' . $d->exam_date . '/' . $d->id), 'live' => $on, 'lock' => $st === 'locked'];
                    }));
                }
                $items = $items->concat(\App\Models\CourseExam::with('course:id,slug')->where('season_id', $se->id)->where('status', 'published')->get()->map(function ($t) use ($se, $access, $user, $now) {
                    $on = $t->start_at && $t->end_at && $now->between($t->start_at, $t->end_at);
                    $st = $access->state($user, $se, 'course', $t->id);

                    return (object) ['title' => $t->title, 'sub' => $t->start_at ? $t->start_at->format('d M Y, h:i a') : ($t->entry_date ? \Illuminate\Support\Carbon::parse($t->entry_date)->format('d M Y') : ''), 'sort' => ($t->start_at ? $t->start_at->format('Y-m-d') : '0000-00-00') . sprintf('%08d', $t->id),
                        'tag' => $st === 'locked' ? 'Paid' : ($st === 'free' && $access->isPaid($se) ? 'Free' : ($on ? 'Live now' : ($t->start_at && $t->start_at->isFuture() ? 'Upcoming' : 'Exam'))),
                        'url' => $st === 'locked' ? $access->buyUrl($se) : url('courses/' . ($t->course->slug ?? $t->course_id)), 'live' => $on, 'lock' => $st === 'locked'];
                }));
                $paid = $access->isPaid($se);
                $out[] = (object) ['title' => $se->title, 'sub' => trim(($se->starts_on ? $se->starts_on->format('d M Y') : '') . ($se->ends_on ? ' - ' . $se->ends_on->format('d M Y') : ' - running')),
                    'paid' => $paid, 'price' => $paid ? $money($access->effectivePrice($se)) : null, 'url' => $se->publicUrl(),
                    'total' => $items->count(), 'live' => $items->contains('live', true), 'items' => $items->sortByDesc('sort')->values()->take($itemLimit)->all()];
            }
        }

        // KPSC's old current-affairs seasons (free)
        if ($kpsc && $kind === 'ca' && \Illuminate\Support\Facades\Schema::hasTable('kpsc_ca_seasons')) {
            foreach (\App\Models\CaSeason::where('status', 'active')->orderByDesc('is_featured')->orderByDesc('id')->limit($seasonLimit)->get() as $cs) {
                $q = fn () => \App\Models\DailyExam::where('season_id', $cs->id)->where('section', 'CA-Daily-Exam')->where('status', 1)->whereNull('series_season_id');
                $rows = $q()->orderByDesc('exam_date')->orderByDesc('slot_no')->orderByDesc('id')->limit($itemLimit)->get();
                $out[] = (object) ['title' => $cs->name, 'sub' => trim(($cs->starts_on ? \Illuminate\Support\Carbon::parse($cs->starts_on)->format('d M Y') : '') . ($cs->ends_on ? ' - ' . \Illuminate\Support\Carbon::parse($cs->ends_on)->format('d M Y') : ' - running')),
                    'paid' => false, 'price' => null, 'url' => null, 'total' => $q()->count(), 'live' => $rows->contains(fn ($d) => $d->started_at && $d->ended_at && now()->between($d->started_at, $d->ended_at)),
                    'items' => $rows->map(function ($d) {
                        $on = $d->started_at && $d->ended_at && now()->between($d->started_at, $d->ended_at);

                        return (object) ['title' => $d->exam_title ?: ($d->examtitle ?: 'CA exam'), 'sub' => $d->exam_date ? \Illuminate\Support\Carbon::parse($d->exam_date)->format('d M Y') : '',
                            'tag' => $on ? 'Live now' : ($d->started_at && \Illuminate\Support\Carbon::parse($d->started_at)->isFuture() ? 'Upcoming' : ucfirst((string) ($d->exam_kind ?: 'Exam'))), 'url' => url('kpsc/daily-exam/' . $d->exam_date . '/' . $d->id), 'live' => $on, 'lock' => false];
                    })->all()];
            }
        }

        return $out;
    }

    /**
     * "What we provide" on an exam type page: each feature with its real, current content - what is on now (or the latest),
     * how many there are, and where to see all. Features with nothing yet show their description.
     */
    public function provide(CompetitiveExam $exam, $features): array
    {
        $kpsc = $exam->slug === 'kpsc' && \Illuminate\Support\Facades\Schema::hasTable('kpsc_daily_exams');
        $now = now();
        $exams = function (string $section, string $base, int $n) use ($now) {
            $q = fn () => \App\Models\DailyExam::where('section', $section)->where('status', 1);
            $on = $q()->where('started_at', '<=', $now)->where('ended_at', '>=', $now)->orderBy('ended_at')->limit($n)->get();
            $rows = $on->count() ? $on : $q()->orderByDesc('exam_date')->orderByDesc('id')->limit($n)->get();
            $isOn = $on->count() > 0;

            return ['total' => $q()->count(), 'live' => $isOn, 'items' => $rows->map(fn ($d) => (object) [
                'title' => $d->examtitle ?: $section, 'sub' => $d->exam_date ? \Illuminate\Support\Carbon::parse($d->exam_date)->format('d M Y') : '',
                'tag' => $isOn ? 'Live now' : 'Latest', 'url' => url($base . '/' . $d->exam_date . '/' . $d->id)])->all()];
        };
        $content = fn (string $kind, callable $row, int $n) => (function () use ($kind, $row, $exam, $n) {
            $q = fn () => ContentItem::ofKind($kind)->live($exam->slug);

            return ['total' => $q()->count(), 'live' => false, 'items' => $q()->orderByDesc('published_at')->orderByDesc('id')->limit($n)->get()->map($row)->all()];
        })();

        $out = [];
        foreach ($features as $f) {
            $n = (int) ($f->list_limit ?? 3);
            $d = ['total' => 0, 'live' => false, 'items' => [], 'seasons' => [], 'all' => null, 'icon' => 'bi-check2-circle'];
            switch ($f->slug) {
                case 'daily-exams':
                    $d = ['icon' => 'bi-calendar-check', 'all' => url($exam->slug . '/daily-exams')] + ($kpsc ? $exams('Daily Exam', 'kpsc/daily-exam', $n) : $d);
                    break;
                case 'omr-exams':
                    $d = ['icon' => 'bi-ui-checks-grid', 'all' => url($exam->slug . '/omr-exams')] + ($kpsc ? $exams('Model Exam', 'kpsc/model-exam', $n) : $d);
                    break;
                case 'premium-daily-ca':
                    $d = ['icon' => 'bi-newspaper', 'all' => url($exam->slug . '/ca-exams')] + ($kpsc ? $exams('CA-Daily-Exam', 'kpsc/psc-daily-current-affairs', $n) : $d);
                    break;
                case 'free-classes':
                    $q = fn () => ContentItem::ofKind('promo_class')->live($exam->slug);
                    $liveNow = $q()->where('mode', 'live')->whereBetween('scheduled_at', [$now->copy()->subHours(3), $now->copy()->addDays(7)])->orderBy('scheduled_at')->limit($n)->get();
                    $rows = $liveNow->count() ? $liveNow : $q()->orderByDesc('id')->limit($n)->get();
                    $d = ['icon' => 'bi-play-btn', 'all' => url('free-classes'), 'total' => $q()->count(), 'live' => $liveNow->count() > 0,
                        'items' => $rows->map(fn ($c) => (object) ['title' => $c->title, 'sub' => $c->mode === 'live' && $c->scheduled_at ? $c->scheduled_at->format('d M, h:i a') : 'Recorded class',
                            'tag' => $c->mode === 'live' ? ($c->scheduled_at && $c->scheduled_at->isPast() ? 'Live now' : 'Upcoming') : 'Free', 'url' => $c->publicUrl()])->all()];
                    break;
                case 'articles':
                    $d = ['icon' => 'bi-journal-text', 'all' => url('articles')] + $content('article', fn ($a) => (object) ['title' => $a->title, 'sub' => optional($a->published_at ?? $a->created_at)->format('d M Y'), 'tag' => 'New', 'url' => url('articles/' . $a->id)], $n);
                    break;
                case 'materials':
                    $d = ['icon' => 'bi-file-earmark-arrow-down', 'all' => null] + $content('material', fn ($m) => (object) ['title' => $m->title, 'sub' => optional($m->published_at ?? $m->created_at)->format('d M Y'),
                        'tag' => 'Download', 'url' => $m->attachment ? Storage::url('files/' . $m->attachment) : ($m->landing_url ?: '#')], $n);
                    break;
                case 'paid-classes':
                    $c = ExamCourse::where('exam_id', $exam->id)->where('status', 'published')->where('slug', 'not like', 'series-%');
                    $d = ['icon' => 'bi-collection-play', 'all' => url('courses'), 'total' => (clone $c)->count(), 'live' => false,
                        'items' => (clone $c)->orderByDesc('id')->limit($n)->get()->map(fn ($x) => (object) ['title' => $x->title, 'sub' => 'Course', 'tag' => 'Course', 'url' => url('courses/' . ($x->slug ?: $x->id))])->all()];
                    break;
                case 'previous-questions':
                    $d = ['icon' => 'bi-folder2-open', 'all' => $kpsc ? url('kpsc/prev-qstn-ans') : null] + $d;
                    break;
                case 'psc-bulletin':
                    $d = ['icon' => 'bi-book', 'all' => $kpsc ? url('kpsc/psc-bulletin') : null] + $d;
                    break;
                case 'gk-exams':
                    $d = ['icon' => 'bi-lightbulb', 'all' => url($exam->slug . '/gk-exams')] + $d;
                    break;
                case 'special-exams':
                    $d = ['icon' => 'bi-stars', 'all' => url($exam->slug . '/special-exams')] + $d;
                    break;
            }
            $d += ['seasons' => [], 'items' => [], 'total' => 0, 'live' => false, 'all' => null];
            // the series are kept in seasons: show the active seasons, each with its exams newest first
            $seriesKind = ['daily-exams' => 'daily', 'omr-exams' => 'omr', 'premium-daily-ca' => 'ca', 'gk-exams' => 'gk', 'special-exams' => 'special'][$f->slug] ?? null;
            if ($seriesKind && ($seasons = $this->seasonsFor($exam, $seriesKind, $kpsc, (int) ($f->season_limit ?? 3), (int) ($f->season_items_limit ?? 6)))) {
                $d['seasons'] = $seasons;
                $d['items'] = [];
                $d['total'] = array_sum(array_map(fn ($x) => $x->total, $seasons));
                $d['live'] = collect($seasons)->contains(fn ($x) => $x->live);
            }
            // exam type series (daily / OMR / CA / GK ... built in the admin for this exam type) when the old KPSC tables have nothing
            if (!count($d['items']) && !count($d['seasons']) && $f->content_kind === 'exam') {
                $q = fn () => \App\Models\CourseExam::with('course:id,slug')->where('exam_id', $exam->id)->where('feature_id', $f->id)->where('status', 'published');
                $on = $q()->where(fn ($w) => $w->whereNull('start_at')->orWhere('start_at', '<=', $now))->where(fn ($w) => $w->whereNull('end_at')->orWhere('end_at', '>=', $now))->orderByDesc('id')->limit($n)->get();
                $rows = $on->count() ? $on : $q()->orderByDesc('start_at')->orderByDesc('id')->limit($n)->get();
                if ($rows->count()) {
                    $d['total'] = $q()->count();
                    $d['live'] = $on->count() > 0 && $on->contains(fn ($t) => $t->start_at && $t->end_at);
                    $d['items'] = $rows->map(fn ($t) => (object) ['title' => $t->title, 'sub' => $t->start_at ? $t->start_at->format('d M Y, h:i a') : ($t->entry_date ? \Illuminate\Support\Carbon::parse($t->entry_date)->format('d M Y') : ''),
                        'tag' => $t->start_at && $t->start_at->isFuture() ? 'Upcoming' : ($on->contains('id', $t->id) && $t->end_at ? 'Live now' : 'Latest'), 'url' => url('courses/' . ($t->course->slug ?? $t->course_id))])->all();
                }
            }
            // the series are called the same everywhere
            $names = ['daily-exams' => 'Daily exams', 'omr-exams' => 'OMR exams', 'gk-exams' => 'GK exam series', 'premium-daily-ca' => 'CA exam series', 'special-exams' => 'Special exams'];
            $out[] = (object) (['name' => $names[$f->slug] ?? $f->name, 'description' => $f->description, 'slug' => $f->slug] + $d);
        }

        // notes the staff keep for this exam type
        foreach ([['syllabus', 'Syllabus', 'bi-files'], ['result', 'Results', 'bi-clipboard-check']] as [$kind, $name, $icon]) {
            $q = fn () => ContentItem::ofKind($kind)->live($exam->slug);
            if ($q()->exists()) {
                $out[] = (object) ['name' => $name, 'description' => '', 'slug' => $kind, 'icon' => $icon, 'all' => null, 'total' => $q()->count(), 'live' => false,
                    'items' => $q()->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get()->map(fn ($m) => (object) ['title' => $m->title, 'sub' => optional($m->published_at ?? $m->created_at)->format('d M Y'), 'tag' => 'Download',
                        'url' => $m->attachment ? Storage::url('files/' . $m->attachment) : ($m->landing_url ?: '#')])->all()];
            }
        }

        return $out;
    }

    public function exam($slug)
    {
        return view('kpsc.exam', $this->examData($slug));
    }

    /** Everything the exam type page shows (also read by the app API). */
    public function examData($slug): array
    {
        $exam = CompetitiveExam::where('slug', $slug)->where('status', 1)->firstOrFail();
        \App\Support\Seo::set(['key' => 'exam:' . $exam->slug]);
        $features = \App\Models\ExamFeature::active()->forExam($exam->id)->get();
        // special tabs shown for this exam type, and its subject categories
        $tabs = ContentItem::ofKind('whats_new')->live($exam->slug)->orderBy('position')->get();
        $subjects = $exam->slug === 'kpsc'
            ? \App\Models\KpscSubject::where('status', 'show')->where('type', 'parent')->orderBy('position')->get()->map(fn ($k) => (object) ['title' => $k->subject_title, 'image' => $k->image ? \Illuminate\Support\Facades\Storage::url('files/' . $k->image) : null, 'url' => url('kpsc/subject/' . $k->slug_name), 'color' => $k->bg_color])
            : \App\Models\ExamSubject::where('exam_id', $exam->id)->where('status', 1)->whereNull('parent_id')->orderBy('position')->get()->map(fn ($k) => (object) ['title' => $k->title, 'image' => $k->image ? \Illuminate\Support\Facades\Storage::url($k->image) : null, 'url' => url('free-classes') . '?subject=' . $k->id, 'color' => $k->color]);
        $courses = ExamCourse::where('exam_id', $exam->id)->where('status', 'published')->where('slug', 'not like', 'series-%')->orderByDesc('is_featured')->orderByDesc('id')->get();
        $today = today();

        // everything staff set up in the admin for this exam type, in one landing page
        $banners = ContentItem::ofKind('banner')->live($exam->slug)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $today))->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $today))
            ->orderBy('position')->get();
        $stories = \App\Models\Banner::forExam($exam->slug)->where('status', 1)->orderBy('position')->get();
        $liveClasses = ContentItem::ofKind('promo_class')->live($exam->slug)->where('mode', 'live')->where('scheduled_at', '>=', now())->orderBy('scheduled_at')->limit(8)->get();
        $recorded = ContentItem::ofKind('promo_class')->live($exam->slug)->where(fn ($q) => $q->where('mode', 'recorded')->orWhere('scheduled_at', '<', now()))->orderByDesc('id')->limit(8)->get();
        $tests = \App\Models\CourseExam::with('course:id,slug,title,course_type')->where('exam_id', $exam->id)->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()))
            ->orderByRaw('start_at is null')->orderBy('start_at')->limit(16)->get();
        $announcements = ContentItem::ofKind('announcement')->live($exam->slug)->with('exams:id,short_name,name,color')->orderByDesc('is_pinned')->orderByDesc('published_at')->orderByDesc('id')->limit(8)->get();
        $articles = ContentItem::ofKind('article')->live($exam->slug)->with('exams:id,short_name,name,color')->orderByDesc('published_at')->orderByDesc('id')->limit(8)->get();
        $mine = auth()->check() ? \App\Models\CourseAdmission::where('user_id', auth()->id())->where('status', 'active')->pluck('course_id')->all() : [];

        $rankRows = app(\App\Services\StudentProgress::class)->publicBoard($exam->slug, 20);

        $provide = $this->provide($exam, $features);

        return compact('provide', 'rankRows', 'exam', 'courses', 'features', 'tabs', 'subjects', 'banners', 'stories', 'liveClasses', 'recorded', 'tests', 'announcements', 'articles', 'mine') + ['title' => $exam->name];
    }

    /** Free promo classes (YouTube live / recorded), with exam type chips. */
    /**
     * Playlist (folder) browsing shared by free classes and free articles: pick an exam type (?exam=kpsc), then go down folder by folder (?folder=ID).
     * Returns the folders at this level (with how many items they hold, empty ones left out), the folder being viewed and its trail.
     */
    private function folderLevel(Request $request, string $kind): array
    {
        $exam = $request->filled('exam') ? CompetitiveExam::where('status', 1)->where('slug', $request->exam)->first() : null;
        $current = $request->filled('folder') ? \App\Models\ContentFolder::where('kind', $kind)->find($request->folder) : null;
        $all = \App\Models\ContentFolder::where('kind', $kind)->orderBy('position')->orderBy('title')->get(['id', 'exam_id', 'parent_id', 'title']);
        $own = ContentItem::ofKind($kind)->live()->whereNotNull('folder_id')->selectRaw('folder_id, count(*) c')->groupBy('folder_id')->pluck('c', 'folder_id');
        $total = null;
        $total = function ($id) use (&$total, $own, $all) {
            return (int) ($own[$id] ?? 0) + $all->where('parent_id', $id)->sum(fn ($f) => $total($f->id));
        };
        // a folder with nothing in it (nor in its sub folders) is not shown at all
        $folders = $all->where('parent_id', $current?->id)->when($exam, fn ($c) => $c->where('exam_id', $exam->id))->map(function ($f) use ($total) {
            $f->items_count = $total($f->id);
            $f->exam_ids = [$f->exam_id];

            return $f;
        })->filter(fn ($f) => $f->items_count > 0)->values();

        return [$exam, $current, $current ? $current->trail() : [], $folders];
    }

    public function freeClasses(Request $request)
    {
        [$exam, $current, $trail, $folders] = $this->folderLevel($request, 'promo_class');
        $classes = ContentItem::ofKind('promo_class')->live()->with('exams:id')->when($exam, fn ($q) => $q->forExam($exam->slug))->where('folder_id', $current?->id)
            ->orderByRaw("mode = 'live' desc")->orderByDesc('scheduled_at')->orderByDesc('id')->paginate(24)->withQueryString();

        return view('kpsc.free-classes', ['classes' => $classes, 'folders' => $folders, 'current' => $current, 'trail' => $trail, 'exam' => $exam,
            'exams' => CompetitiveExam::where('status', 1)->orderBy('position')->get(), 'title' => 'Free classes']);
    }

    public function freeClassById($id)
    {
        return redirect(ContentItem::ofKind('promo_class')->live()->findOrFail($id)->publicUrl(), 301);
    }

    public function freeClass($uid)
    {
        $c = ContentItem::ofKind('promo_class')->live()->with('exams:id,short_name,name,color')->where('uid', $uid)->firstOrFail();
        $vid = \App\Services\ClassSource::youtubeId((string) $c->link_url);
        \App\Support\Seo::set(['key' => 'class:' . $c->id, 'title' => $c->title . ' - free class | Pachavellam', 'description' => $c->excerpt(200) ?: 'Free class on Pachavellam.',
            'image' => $c->image ? \Illuminate\Support\Facades\Storage::url('files/' . $c->image) : ($vid ? 'https://img.youtube.com/vi/' . $vid . '/hqdefault.jpg' : null)]);
        $embed = $vid ? 'https://www.youtube.com/embed/' . $vid . '?rel=0' . ($c->mode === 'live' ? '&autoplay=0' : '') : null;
        $more = ContentItem::ofKind('promo_class')->live()->where('id', '!=', $c->id)->whereIn('id', fn ($q) => $q->select('item_id')->from('content_item_exams')->whereIn('exam_id', $c->exams->pluck('id')))->latest('id')->limit(8)->get();

        return view('kpsc.free-class', ['c' => $c, 'embed' => $embed, 'vid' => $vid, 'title' => $c->title, 'more' => $more]);
    }

    /**
     * The common player page: website pages put it in an iframe, the Flutter app opens it in a WebView.
     * Address is /player/{uid} - the YouTube id is looked up here and never put in a link.
     * ?chat=0 hides the chat; autoplay is on (muted, as browsers require); ?autoplay=0 turns it off.
     */
    public function player(Request $request, $uid)
    {
        $item = ContentItem::ofKind('promo_class')->live()->where('uid', $uid)->first();
        $vid = null;
        $chat = null;
        if ($item) {
            $vid = \App\Services\ClassSource::youtubeId((string) $item->link_url);
            $title = $item->title;
            $chat = $item->chat_mode !== 'off' && $request->query('chat', '1') !== '0' ? url('player/' . $uid . '/chat') : null;
            $live = $item->mode === 'live';
        } elseif ($sv = \App\Models\SpecialVideo::where('status', 1)->where('uid', $uid)->first()) {
            $vid = $sv->redirection;
            $title = $sv->title;
            $live = false;
            $chat = ($sv->chat_mode ?? 'off') === 'always' && $request->query('chat', '1') !== '0' ? url('player/' . $uid . '/chat') : null;
        }
        abort_unless($vid, 404);

        return response()->view('kpsc.player', [
            'vid' => $vid, 'title' => $title, 'chatUrl' => $chat, 'live' => $live, 'autoplay' => $request->query('autoplay', '1') !== '0',
            'loggedIn' => auth()->check(),
        ])->header('X-Frame-Options', 'SAMEORIGIN')->header('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * /{exam type}/daily-exams | omr-exams | gk-exams | ca-exams : one exam series of an exam type.
     * The active seasons come first, each with its exams newest first; every other exam of the series is listed below. Each row has the attempt button.
     */
    public function series(Request $request, $slug, $series)
    {
        return view('kpsc.series', $this->seriesData($request, $slug, $series));
    }

    /** JSON for the two drawers of an OMR exam card: the student's own attempts (latest first) and the leaderboard (top 10, once the exam has ended). */
    public function omrSummary(Request $request, $id)
    {
        $exam = \App\Models\DailyExam::where('section', 'Model Exam')->where('status', 1)->findOrFail($id);
        $user = auth()->user();
        $cutoff = $exam->cutoff_mark !== null ? (float) $exam->cutoff_mark : null;
        $fmt = fn ($a) => ['name' => $a->full_name, 'right' => (int) $a->right_answer, 'wrong' => (int) $a->wrong_answer, 'mark' => round((float) $a->total_mark, 2), 'below' => $cutoff !== null && (float) $a->total_mark < $cutoff,
            'at' => $a->answer_uploaded ? \Illuminate\Support\Carbon::parse($a->answer_uploaded)->format('d M Y, h:i a') : ''];
        $mine = $user ? \App\Models\ModelExamAttempt::where('exam_id', $exam->id)->where('user_id', $user->id)->orderByDesc('id')->limit(20)->get()->map($fmt)->values() : collect();
        $ended = $exam->ended_at && \Illuminate\Support\Carbon::parse($exam->ended_at)->isPast();
        return response()->json(['loggedIn' => (bool) $user, 'ended' => $ended, 'cutoff' => $cutoff, 'mine' => $mine, 'title' => $exam->exam_title ?: ($exam->examtitle ?: 'Exam')]);
    }

    /** JSON for the "My attempts" dropdown of a daily exam card: the student's own finished attempts, latest first. */
    public function dailySummary(Request $request, $id)
    {
        $exam = \App\Models\DailyExam::where('section', '<>', 'Model Exam')->where('status', 1)->findOrFail($id);
        $user = auth()->user();
        $mine = $user ? \App\Models\DailyExamattempt::where('exam_id', $exam->id)->where('user_id', $user->id)->where('status', '2')->orderByDesc('id')->limit(20)->get()->map(fn ($a) => [
            'name' => $user->name, 'right' => (int) $a->right, 'wrong' => (int) $a->wrong, 'mark' => round((float) $a->total_mark, 2), 'below' => false,
            'at' => $a->attend_ended_at ? \Illuminate\Support\Carbon::parse($a->attend_ended_at)->format('d M Y, h:i a') : ''])->values() : collect();

        return response()->json(['loggedIn' => (bool) $user, 'ended' => true, 'cutoff' => null, 'mine' => $mine, 'title' => $exam->exam_title ?: ($exam->examtitle ?: 'Exam')]);
    }

    /** The leaderboard drawer of a daily exam: first attempts finished inside the exam window, 25 per page, plus the signed-in student's own rank. */
    public function dailyLeaderboard(Request $request, $id)
    {
        $exam = \App\Models\DailyExam::where('section', '<>', 'Model Exam')->where('status', 1)->findOrFail($id);
        $per = 25;
        $page = max(1, (int) $request->query('page', 1));
        $base = fn () => \App\Models\DailyExamattempt::leftJoin('users', 'users.id', 'kpsc_daily_exams_attempt.user_id')->where('kpsc_daily_exams_attempt.exam_id', $exam->id)
            ->where('kpsc_daily_exams_attempt.status', '2')->where('kpsc_daily_exams_attempt.attempt_no', 1)
            ->when($exam->ended_at, fn ($q) => $q->where('kpsc_daily_exams_attempt.attend_ended_at', '<=', $exam->ended_at));
        $order = fn ($q) => $q->orderByDesc('kpsc_daily_exams_attempt.total_mark')->orderBy('kpsc_daily_exams_attempt.attempt_time')->orderBy('kpsc_daily_exams_attempt.id');
        $total = $base()->count();
        $user = auth()->user();
        $rows = $order($base())->select('kpsc_daily_exams_attempt.*', 'users.name as uname')->skip(($page - 1) * $per)->take($per)->get();
        $out = $rows->values()->map(fn ($a, $i) => ['rank' => ($page - 1) * $per + $i + 1, 'name' => $a->uname ?: 'Student', 'right' => (int) $a->right, 'wrong' => (int) $a->wrong,
            'mark' => round((float) $a->total_mark, 2), 'me' => $user && $a->user_id == $user->id]);

        $me = null;
        if ($user) {
            $best = $order($base())->where('kpsc_daily_exams_attempt.user_id', $user->id)->select('kpsc_daily_exams_attempt.*')->first();
            if ($best) {
                $rank = $base()->where(fn ($q) => $q->where('kpsc_daily_exams_attempt.total_mark', '>', $best->total_mark)
                    ->orWhere(fn ($w) => $w->where('kpsc_daily_exams_attempt.total_mark', $best->total_mark)->where(fn ($x) => $x->where('kpsc_daily_exams_attempt.attempt_time', '<', $best->attempt_time)
                        ->orWhere(fn ($y) => $y->where('kpsc_daily_exams_attempt.attempt_time', $best->attempt_time)->where('kpsc_daily_exams_attempt.id', '<', $best->id)))))->count() + 1;
                $me = ['rank' => $rank, 'name' => $user->name ?: 'You', 'mark' => round((float) $best->total_mark, 2), 'right' => (int) $best->right, 'wrong' => (int) $best->wrong, 'below' => false];
            }
        }

        return response()->json(['ended' => true, 'cutoff' => null, 'rows' => $out, 'total' => $total, 'more' => $page * $per < $total, 'me' => $me, 'loggedIn' => (bool) $user]);
    }

    /** The leaderboard drawer of an OMR exam: 25 students per page (ranked), plus the signed-in student's own best rank. Opens once the exam has ended. */
    public function omrLeaderboard(Request $request, $id)
    {
        $exam = \App\Models\DailyExam::where('section', 'Model Exam')->where('status', 1)->findOrFail($id);
        if (!$exam->ended_at || !\Illuminate\Support\Carbon::parse($exam->ended_at)->isPast()) {
            return response()->json(['ended' => false, 'rows' => [], 'total' => 0, 'more' => false, 'me' => null]);
        }
        $per = 25;
        $page = max(1, (int) $request->query('page', 1));
        $base = fn () => \App\Models\ModelExamAttempt::where('exam_id', $exam->id)->where('answer_uploaded', '<=', $exam->ended_at);
        $total = $base()->count();
        $user = auth()->user();
        $rows = $base()->orderByDesc('total_mark')->orderBy('created_at')->orderBy('id')->skip(($page - 1) * $per)->take($per)->get();
        $meId = $user ? $user->id : null;
        $out = $rows->values()->map(fn ($a, $i) => ['rank' => ($page - 1) * $per + $i + 1, 'name' => $a->full_name ?: 'Student', 'right' => (int) $a->right_answer, 'wrong' => (int) $a->wrong_answer,
            'mark' => round((float) $a->total_mark, 2), 'me' => $meId && $a->user_id == $meId]);

        $me = null;
        if ($user) {
            $best = $base()->where('user_id', $user->id)->orderByDesc('total_mark')->orderBy('created_at')->orderBy('id')->first();
            if ($best) {
                $rank = $base()->where(fn ($q) => $q->where('total_mark', '>', $best->total_mark)
                    ->orWhere(fn ($w) => $w->where('total_mark', $best->total_mark)->where(fn ($x) => $x->where('created_at', '<', $best->created_at)->orWhere(fn ($y) => $y->where('created_at', $best->created_at)->where('id', '<', $best->id)))))->count() + 1;
                $me = ['rank' => $rank, 'name' => $best->full_name ?: 'You', 'mark' => round((float) $best->total_mark, 2), 'right' => (int) $best->right_answer, 'wrong' => (int) $best->wrong_answer,
                    'below' => $exam->cutoff_mark !== null && (float) $best->total_mark < (float) $exam->cutoff_mark];
            }
        }

        return response()->json(['ended' => true, 'cutoff' => $exam->cutoff_mark !== null ? (float) $exam->cutoff_mark : null, 'rows' => $out, 'total' => $total, 'more' => $page * $per < $total, 'me' => $me, 'loggedIn' => (bool) $user]);
    }

    /** Everything a series page shows: active seasons and the other exams (also read by the app API). */
    public function seriesData(Request $request, $slug, $series): array
    {
        $exam = CompetitiveExam::where('slug', $slug)->where('status', 1)->firstOrFail();
        $cfg = [
            'daily-exams' => ['daily', 'daily-exams', 'Daily exams', 'Daily Exam', 'kpsc/daily-exam', 'bi-calendar-check', 'kpsc_daily_exams_attempt'],
            'omr-exams' => ['omr', 'omr-exams', 'OMR exams', 'Model Exam', 'kpsc/model-exam', 'bi-ui-checks-grid', 'kpsc_model_exam_attempt'],
            'gk-exams' => ['gk', 'gk-exams', 'GK exam series', null, null, 'bi-lightbulb', null],
            'ca-exams' => ['ca', 'premium-daily-ca', 'CA exam series', 'CA-Daily-Exam', 'kpsc/daily-exam', 'bi-newspaper', 'kpsc_ca_daily_exam_attempts'],
            'special-exams' => ['special', 'special-exams', 'Special exams', null, null, 'bi-stars', null],
        ][$series] ?? null;
        abort_unless($cfg, 404);
        [$kind, $featureSlug, $label, $legacy, $base, $icon, $attTable] = $cfg;
        $kpsc = $exam->slug === 'kpsc' && $legacy && \Illuminate\Support\Facades\Schema::hasTable('kpsc_daily_exams');
        $now = now();
        $user = auth()->user();
        $access = app(\App\Services\SeasonAccess::class);
        $seasonMap = \Illuminate\Support\Facades\Schema::hasTable('exam_seasons') ? \App\Models\ExamSeason::where('exam_id', $exam->id)->get()->keyBy('id') : collect();

        // exams this student has already finished (old KPSC tables only)
        $done = [];
        if ($kpsc && $user && $attTable && \Illuminate\Support\Facades\Schema::hasTable($attTable)) {
            $done = \Illuminate\Support\Facades\DB::table($attTable)->where('user_id', $user->id)
                ->when($attTable !== 'kpsc_model_exam_attempt', fn ($q) => $q->whereNotNull('attend_ended_at'))->pluck('exam_id')->all();
        }
        $tag = function ($start, $end) {
            $start = $start ? \Illuminate\Support\Carbon::parse($start) : null;
            $end = $end ? \Illuminate\Support\Carbon::parse($end) : null;
            if ($start && $start->isFuture()) {
                return ['Upcoming', 'up'];
            }
            if ($end && $end->isPast()) {
                return ['Ended', 'end'];
            }

            return [$start || $end ? 'Live now' : 'Open', 'live'];
        };

        // one row for an old KPSC exam or a builder exam; a paid season's locked exams send the student to the season page
        $row = function ($e, $se = null) use ($tag, $base, $done, $access, $user) {
            $legacyRow = $e instanceof \App\Models\DailyExam;
            [$t, $c] = $legacyRow ? $tag($e->started_at, $e->ended_at) : $tag($e->start_at, $e->end_at);
            $state = $access->state($user, $se, $legacyRow ? 'legacy' : 'course', $e->id);
            $examUrl = $legacyRow ? url(($e->section === 'Model Exam' ? 'kpsc/model-exam' : 'kpsc/daily-exam') . '/' . $e->exam_date . '/' . $e->id) : url('tests/' . $e->id);   // builder exams open the web test (instructions first)

            return (object) [
                'title' => $legacyRow ? ($e->exam_title ?: ($e->examtitle ?: 'Exam')) : $e->title,
                'date' => $legacyRow ? ($e->exam_date ? \Illuminate\Support\Carbon::parse($e->exam_date)->format('d M Y') : '') : ($e->start_at ? $e->start_at->format('d M Y, h:i a') : ($e->entry_date ? \Illuminate\Support\Carbon::parse($e->entry_date)->format('d M Y') : '')),
                'omr' => $legacyRow && $e->section === 'Model Exam', 'omrId' => $legacyRow ? $e->id : null,
                'answerKey' => $legacyRow && $e->section === 'Model Exam' ? url('kpsc/model-exam/' . $e->exam_date . '/' . $e->id . '/answerkey') : null,
                'dailyId' => $legacyRow && $e->section !== 'Model Exam' ? $e->id : null,
                'tag' => $t, 'tagc' => $c, 'state' => $state, 'inPaid' => $se && $access->isPaid($se), 'paid' => false, 'done' => $legacyRow && in_array($e->id, $done),
                'url' => $state === 'locked' && $se ? $access->buyUrl($se) : $examUrl,
                'sort' => ($legacyRow ? (string) $e->exam_date : ($e->start_at ? $e->start_at->format('Y-m-d') : '0000-00-00')) . sprintf('%08d', $e->id),
                'pos' => $e->season_position,
            ];
        };
        $money = fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.');

        // ---- active seasons (seasons), each with its exams newest first
        $active = [];
        $activeIds = [];
        $caIds = [];
        $today = today();
        foreach ($seasonMap->where('kind', $kind)->where('status', 'published')
            ->filter(fn ($se) => (!$se->starts_on || $se->starts_on->lte($today)) && (!$se->ends_on || $se->ends_on->gte($today)))
            ->sort(fn ($x, $y) => [(int) $x->position, $y->id] <=> [(int) $y->position, $x->id]) as $se) {   // the season order set in admin (arrows): smallest first, same number newest first
            $activeIds[] = $se->id;
            $items = collect();
            if ($legacy && \Illuminate\Support\Facades\Schema::hasTable('kpsc_daily_exams')) {
                $items = $items->concat(\App\Models\DailyExam::where('series_season_id', $se->id)->where('section', $legacy)->where('status', 1)->get()->map(fn ($d) => $row($d, $se)));
            }
            $items = $items->concat(\App\Models\CourseExam::with('course:id,slug')->where('season_id', $se->id)->where('status', 'published')->get()->map(fn ($c) => $row($c, $se)));
            $manual = $items->contains(fn ($i) => $i->pos !== null);   // the admin dragged the exams into an order: exams not placed yet first; otherwise newest first
            $paid = $access->isPaid($se);
            $eff = $access->effectivePrice($se);
            $active[] = (object) [
                'id' => $se->id, 'cover' => $se->cover_image ? \Illuminate\Support\Facades\Storage::url($se->cover_image) : null, 'title' => $se->title, 'sub' => ($se->starts_on ? $se->starts_on->format('d M Y') : '') . ($se->ends_on ? ' - ' . $se->ends_on->format('d M Y') : ' - running'),
                'basis' => $se->basis ? (\App\Models\ExamSeason::BASES[$se->basis] ?? '') . ($se->basis_name ? ': ' . $se->basis_name : '') : null,
                'paid' => $paid, 'price' => $paid ? $money($eff) : null, 'oldPrice' => $paid && $eff < $se->price ? $money($se->price) : null, 'validity' => $paid ? $access->validityLabel($se) : null,
                'freeN' => $paid ? $items->where('state', 'free')->count() : 0, 'owns' => $paid && $access->owns($user, $se), 'url' => $se->publicUrl(),
                'total' => $items->count(), 'items' => $items->sort(fn ($x, $y) => $manual
                    ? ([$x->pos === null ? 0 : 1, (int) $x->pos] <=> [$y->pos === null ? 0 : 1, (int) $y->pos]) ?: strcmp($y->sort, $x->sort)
                    : strcmp($y->sort, $x->sort))->values()->take(30)->all(),
            ];
        }
        // the old CA seasons of KPSC (free)
        if ($kpsc && $kind === 'ca' && \Illuminate\Support\Facades\Schema::hasTable('kpsc_ca_seasons')) {
            foreach (\App\Models\CaSeason::where('status', 'active')->orderByDesc('is_featured')->orderByDesc('id')->get() as $cs) {
                $caIds[] = $cs->id;
                $q = \App\Models\DailyExam::where('season_id', $cs->id)->where('section', $legacy)->where('status', 1)->whereNull('series_season_id');
                $active[] = (object) ['id' => null, 'title' => $cs->name, 'sub' => ($cs->starts_on ? \Illuminate\Support\Carbon::parse($cs->starts_on)->format('d M Y') : '') . ($cs->ends_on ? ' - ' . \Illuminate\Support\Carbon::parse($cs->ends_on)->format('d M Y') : ' - running'),
                    'basis' => null, 'paid' => false, 'price' => null, 'oldPrice' => null, 'validity' => null, 'freeN' => 0, 'owns' => false, 'url' => null,
                    'total' => (clone $q)->count(), 'items' => (clone $q)->orderByDesc('exam_date')->orderByDesc('slot_no')->orderByDesc('id')->limit(30)->get()->map(fn ($d) => $row($d, null))->all()];
            }
        }

        // ---- every other exam of the series, newest first
        if ($kpsc) {
            $others = \App\Models\DailyExam::where('section', $legacy)->where('status', 1)
                ->whereNull('series_season_id')   // an exam that sits in a season is listed inside that season only
                ->when($caIds, fn ($q) => $q->where(fn ($w) => $w->whereNull('season_id')->orWhereNotIn('season_id', $caIds)))
                ->orderByRaw('sort_no is null')->orderBy('sort_no')   // the sort number, smallest first; no number goes last
                ->orderByDesc('exam_date')->orderByDesc('id')->paginate(15)->withQueryString();
            $sid = fn ($e) => $e->series_season_id;
        } else {
            $feature = \App\Models\ExamFeature::where('slug', $featureSlug)->first();
            $others = \App\Models\CourseExam::with('course:id,slug')->where('exam_id', $exam->id)->where('status', 'published')->when($feature, fn ($q) => $q->where('feature_id', $feature->id), fn ($q) => $q->whereRaw('1 = 0'))
                ->whereNull('season_id')
                ->orderByRaw('sort_no is null')->orderBy('sort_no')   // the sort number, smallest first; no number goes last
                ->orderByDesc('start_at')->orderByDesc('entry_date')->orderByDesc('id')->paginate(15)->withQueryString();
            $sid = fn ($e) => $e->season_id;
        }
        $rows = $others->getCollection()->map(fn ($e) => $row($e, ($k = $sid($e)) ? ($seasonMap[$k] ?? null) : null))->all();

        \App\Support\Seo::set(['key' => 'series:' . $exam->slug . ':' . $series, 'title' => $label . ' - ' . ($exam->short_name ?: $exam->name) . ' | Pachavellam',
            'description' => 'Attempt the ' . strtolower($label) . ' of ' . $exam->name . '. Active seasons first, then every other exam.']);

        return ['exam' => $exam, 'series' => $series, 'label' => $label, 'icon' => $icon, 'active' => $active, 'others' => $others, 'rows' => $rows, 'title' => $label . ' - ' . ($exam->short_name ?: $exam->name),
            'accent' => $exam->color ?: '#0a8f5b'];
    }

    /** A "Latest YouTube class" from the home page, played on our own site. */
    public function watch($vid)
    {
        if ($c = ContentItem::ofKind('promo_class')->live()->where('uid', $vid)->first()) {
            return redirect($c->publicUrl());   // special videos are free classes now
        }
        $v = \App\Models\SpecialVideo::where('status', 1)->where('uid', $vid)->first();
        abort_unless($v, 404);
        $more = \App\Models\SpecialVideo::where('status', 1)->whereNull('section')->where('id', '!=', $v->id)->orderBy('position')->limit(12)->get();

        return view('kpsc.watch', ['v' => $v, 'more' => $more, 'title' => $v->title]);
    }

    /** Free articles (the common Articles content), filtered by exam type. */
    public function articles(Request $request)
    {
        [$exam, $current, $trail, $folders] = $this->folderLevel($request, 'article');
        $articles = ContentItem::ofKind('article')->live()->with('exams:id,slug,short_name,name,color')->when($exam, fn ($q) => $q->forExam($exam->slug))->where('folder_id', $current?->id)
            ->orderByDesc('published_at')->orderByDesc('id')->paginate(24)->withQueryString();

        return view('kpsc.articles', ['articles' => $articles, 'folders' => $folders, 'current' => $current, 'trail' => $trail, 'exam' => $exam,
            'exams' => CompetitiveExam::where('status', 1)->orderBy('position')->get(), 'title' => 'Free articles']);
    }

    /** /c/{category}/{sub category}: an exam category (or sub category) by slug - its sub categories, articles, free videos and courses. */
    public function category(Request $request, $path)
    {
        $slug = collect(explode('/', trim($path, '/')))->filter()->last();
        $cat = \App\Models\ExamCategory::with('exam')->where('status', 1)->where('slug', $slug)->firstOrFail();
        abort_unless($cat->exam && $cat->exam->status, 404);
        // the address must be the real chain of slugs (/c/parent/child); anything else goes to it
        if (trim($path, '/') !== $cat->urlPath()) {
            return redirect($cat->publicUrl(), 301);
        }
        $ids = array_merge([$cat->id], $cat->descendantIds());
        // courses of this category and every sub category below it; ?sub=slug narrows to one sub category (and its own sub categories)
        $children = $cat->children()->where('status', 1)->get();
        $sub = $request->filled('sub') ? $children->firstWhere('slug', $request->sub) : null;
        $courseIds = $sub ? array_merge([$sub->id], $sub->descendantIds()) : $ids;
        $courseQ = fn ($cids) => ExamCourse::where('status', 'published')->where('slug', 'not like', 'series-%')->whereIn('id', fn ($q) => $q->select('course_id')->from('exam_course_categories')->whereIn('category_id', $cids));
        $counts = $children->mapWithKeys(fn ($ch) => [$ch->id => $courseQ(array_merge([$ch->id], $ch->descendantIds()))->count()]);
        $courses = $courseQ($courseIds)->with('exam:id,short_name,name,color')->orderByDesc('is_featured')->orderBy('position')->orderByDesc('id')->paginate(12)->withQueryString();
        $items = fn ($kind) => ContentItem::ofKind($kind)->live()->whereIn('category_id', $ids)->with('exams:id,short_name,name,color');
        \App\Support\Seo::set(['key' => 'category:' . $cat->id, 'title' => $cat->name . ' - ' . ($cat->exam->short_name ?: $cat->exam->name) . ' | Pachavellam', 'description' => \Illuminate\Support\Str::limit(strip_tags((string) $cat->description), 160) ?: null]);

        // tests of the courses in this category: paid ones are "prime tests", free ones "free tests"
        $allCourseIds = $courseQ($ids)->pluck('id');
        $tests = fn ($paid) => \App\Models\CourseExam::with('course:id,slug,title')->where('status', 'published')->where('is_paid', $paid)->whereIn('course_id', $allCourseIds)->orderByDesc('start_at')->orderByDesc('id')->limit(24)->get();
        $page = \App\Support\CategorySections::for($cat);
        $data = [
            'sub_categories' => $children, 'courses' => $courses,
            'prime_tests' => in_array('prime_tests', $page['sections'], true) ? $tests(true) : collect(),
            'free_tests' => in_array('free_tests', $page['sections'], true) ? $tests(false) : collect(),
            'free_classes' => $items('promo_class')->orderByRaw('COALESCE(scheduled_at, created_at) desc')->limit(24)->get(),
            'articles' => $items('article')->orderByDesc('published_at')->orderByDesc('id')->limit(24)->get(),
        ];
        // only the sections the panel chose, in its order, and only those with something in them
        $filled = fn ($k) => $k === 'courses' ? $courses->total() > 0 : $data[$k]->count() > 0;
        $sections = collect($page['sections'])->filter($filled)->values()->all();

        return view('kpsc.category', $data + [
            'cat' => $cat, 'trail' => $cat->ancestors()->push($cat), 'title' => $cat->name, 'page' => $page, 'sections' => $sections,
            'children' => $children, 'sub' => $sub, 'childCounts' => $counts, 'totalCourses' => $courseQ($ids)->count(),
        ]);
    }

    public function article($id)
    {
        $a = ContentItem::ofKind('article')->live()->with('exams:id,short_name,name,color')->findOrFail($id);

        \App\Support\Seo::set(['key' => 'article:' . $a->id, 'title' => $a->title . ' | Pachavellam', 'description' => $a->excerpt(200), 'image' => $a->image ? \Illuminate\Support\Facades\Storage::url('files/' . $a->image) : null]);

        return view('kpsc.article', ['a' => $a, 'title' => $a->title]);
    }

    // ---- announcements, banners, stories, notification bell, account

    public function announcements()
    {
        return view('kpsc.announcements', [
            'items' => ContentItem::ofKind('announcement')->live()->with('exams:id,short_name,name,color')->orderByDesc('is_pinned')->orderByDesc('published_at')->orderByDesc('id')->limit(120)->get(),
            'exams' => CompetitiveExam::where('status', 1)->orderBy('position')->get(), 'title' => 'Announcements',
        ]);
    }

    public function announcement($id)
    {
        $a = ContentItem::ofKind('announcement')->live()->with('exams:id,short_name,name,color')->findOrFail($id);

        \App\Support\Seo::set(['key' => 'announcement:' . $a->id, 'title' => $a->title . ' | Pachavellam', 'description' => $a->excerpt(200), 'image' => $a->image ? \Illuminate\Support\Facades\Storage::url('files/' . $a->image) : null]);

        return view('kpsc.announcement', ['a' => $a, 'title' => $a->title]);
    }

    public function banners()
    {
        $today = today();

        return view('kpsc.banners', ['title' => 'Banners', 'banners' => ContentItem::ofKind('banner')->live()
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $today))->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $today))
            ->orderBy('position')->get()]);
    }

    public function stories()
    {
        return view('kpsc.stories', ['title' => 'Stories', 'stories' => \App\Models\Banner::with('exams:id')->where('status', 1)->orderBy('position')->get(), 'exams' => CompetitiveExam::where('status', 1)->orderBy('position')->get()]);
    }

    /** Latest announcements and notifications for the bell in the header. */
    public function feed()
    {
        $rows = ContentItem::whereIn('kind', ['announcement', 'notification'])->live()->orderByDesc('published_at')->orderByDesc('id')->limit(12)->get()
            ->map(fn ($i) => [
                'id' => $i->id, 'title' => $i->title, 'kind' => $i->kind, 'date' => optional($i->published_at ?? $i->created_at)->format('d M'),
                'url' => $i->kind === 'announcement' ? url('announcements/' . $i->id) : ($i->landing_url ?: ($i->attachment ? \Illuminate\Support\Facades\Storage::url('files/' . $i->attachment) : url('announcements'))),
                'thumb' => $i->image ? \Illuminate\Support\Facades\Storage::url('files/' . $i->image) : null,
            ]);

        return response()->json(['items' => $rows, 'latest' => $rows->max('id')]);
    }

    /**
     * Stories in pages (home strip and /stories load more as the visitor scrolls): normal stories first, then the stories synced from Instagram.
     * ?page=1&per=10&exam=<id>  - with an exam, only stories for that exam type plus the ones for every exam type.
     */
    public function storiesFeed(\Illuminate\Http\Request $request)
    {
        $per = max(4, min(30, (int) $request->input('per', 10)));
        $page = max(1, (int) $request->input('page', 1));
        $exam = (int) $request->input('exam');

        $stories = \App\Models\Banner::with('exams:id')->where('status', 1)->orderBy('position')->get()
            ->when($exam, fn ($c) => $c->filter(fn ($b) => $b->exams->isEmpty() || $b->exams->contains('id', $exam)))
            ->map(fn ($b) => ['kind' => 'story', 'href' => \App\Support\Landing::url($b->target_type, $b->target_value, $b->redirection) ?: '#', 'img' => \Illuminate\Support\Facades\Storage::url('files/' . $b->image)]);

        $ach = ContentItem::ofKind('achievement')->live()->with('exams:id')->orderByDesc('position')->orderByDesc('id')->get()
            ->when($exam, fn ($c) => $c->filter(fn ($a) => $a->exams->isEmpty() || $a->exams->contains('id', $exam)))
            ->map(function ($a) {
                $thumb = $a->image ? \Illuminate\Support\Facades\Storage::url('files/' . $a->image) : '';

                return ['kind' => 'ach', 'type' => $a->media_type === 'video' ? 'video' : 'image', 'poster' => $thumb,
                    'src' => $a->media_type === 'video' && $a->media_path ? \Illuminate\Support\Facades\Storage::url('files/' . $a->media_path) : $thumb,
                    'title' => $a->title && $a->title !== 'Achievement' ? $a->title : ''];
            });

        $all = $stories->concat($ach)->values();

        return response()->json(['items' => $all->slice(($page - 1) * $per, $per)->values(), 'total' => $all->count(), 'next' => $all->count() > $page * $per]);
    }
}
