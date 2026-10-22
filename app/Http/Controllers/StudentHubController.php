<?php

namespace App\Http\Controllers;

use App\Models\CompetitiveExam;
use App\Models\ContentItem;
use App\Models\CourseAdmission;
use App\Models\CourseClass;
use App\Models\CourseExam;
use App\Models\ExamCategory;
use App\Models\ExamCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * The student tabs of the website's bottom bar: Exams (interests + favourite categories), Courses (enrolled first),
 * Learn (upcoming classes, tests and articles) and Profile (everything the student manages about their account).
 * Everyone can look; choosing an exam / favourite needs a sign in, after which the choice is completed.
 */
class StudentHubController extends Controller
{
    // ============================================================ Exams tab

    public function exams(Request $request)
    {
        $user = $request->user();
        $exams = CompetitiveExam::where('status', 1)->orderBy('position')
            ->with(['categories' => fn ($q) => $q->whereNull('parent_id')->where('status', 1)->orderBy('position')->orderBy('id')->with(['children' => fn ($c) => $c->where('status', 1)])])->get();
        $mine = $user ? $user->examInterests()->pluck('competitive_exams.id')->all() : [];
        $favs = $user && Schema::hasTable('student_favourite_categories') ? $user->favouriteCategories()->pluck('exam_categories.id')->all() : [];
        $courseCounts = ExamCourse::where('status', 'published')->where('slug', 'not like', 'series-%')->selectRaw('exam_id, count(*) c')->groupBy('exam_id')->pluck('c', 'exam_id');

        return view('kpsc.hub.exams', [
            'title' => 'Exams', 'user' => $user, 'courseCounts' => $courseCounts, 'favs' => $favs, 'mine' => $mine,
            'myExams' => $exams->whereIn('id', $mine)->values(), 'otherExams' => $exams->whereNotIn('id', $mine)->values(),
        ]);
    }

    /** Toggle "I'm preparing for this" (JSON for signed in pages; a GET after sign in finishes a guest's tap). */
    public function interest(Request $request, $id)
    {
        $exam = CompetitiveExam::where('status', 1)->findOrFail($id);
        $user = $request->user();
        $has = $user->examInterests()->where('competitive_exams.id', $exam->id)->exists();
        $on = $request->isMethod('get') ? true : !$has;      // coming back from sign in: always add

        if ($on && !$has) {
            $user->examInterests()->attach($exam->id, ['source' => 'web']);
        } elseif (!$on && $has) {
            $user->examInterests()->detach($exam->id);
        }

        return $request->expectsJson() ? response()->json(['on' => $on]) : redirect(url('exams') . '#exam-' . $exam->id)->with('hub_msg', $exam->name . ' added to your exams.');
    }

    public function favourite(Request $request, $id)
    {
        $cat = ExamCategory::where('status', 1)->findOrFail($id);
        $user = $request->user();
        $has = $user->favouriteCategories()->where('exam_categories.id', $cat->id)->exists();
        $on = $request->isMethod('get') ? true : !$has;

        if ($on && !$has) {
            $user->favouriteCategories()->attach($cat->id);
            $user->examInterests()->syncWithoutDetaching([$cat->exam_id => ['source' => 'web']]);   // a favourite category means the exam is of interest too
        } elseif (!$on && $has) {
            $user->favouriteCategories()->detach($cat->id);
        }

        return $request->expectsJson() ? response()->json(['on' => $on]) : redirect(url('exams') . '#exam-' . $cat->exam_id)->with('hub_msg', $cat->name . ' added to your favourites.');
    }

    // ============================================================ Courses tab

    public function courses(Request $request)
    {
        $user = $request->user();
        $exams = CompetitiveExam::where('status', 1)->orderBy('position')->get();
        $exam = $request->filled('exam') ? $exams->firstWhere('slug', $request->exam) : null;
        $price = in_array($request->price, ['free', 'paid'], true) ? $request->price : null;

        $admissions = $user ? $user->activeAdmissions()->filter(fn ($a) => $a->course) : collect();
        $myIds = $admissions->pluck('course_id')->all();
        $mineFirst = $user ? $user->examInterests()->pluck('competitive_exams.id')->all() : [];

        $courses = ExamCourse::with('exam:id,slug,short_name,name,color')->where('status', 'published')->where('slug', 'not like', 'series-%')
            ->whereNotIn('id', $myIds ?: [0])
            ->when($exam, fn ($q) => $q->where('exam_id', $exam->id))
            ->get()
            ->filter(fn ($c) => !$price || ($price === 'free') === $c->isFree())
            // the student's exams first, then featured, then newest
            ->sortBy(fn ($c) => [in_array($c->exam_id, $mineFirst) ? 0 : 1, $c->is_featured ? 0 : 1, -$c->id])->values();

        return view('kpsc.hub.courses', ['title' => 'Courses', 'user' => $user, 'exams' => $exams, 'exam' => $exam, 'price' => $price, 'admissions' => $admissions, 'courses' => $courses]);
    }

    // ============================================================ Learn tab

    public function learn(Request $request)
    {
        $user = $request->user();
        $admissions = $user ? $user->activeAdmissions()->filter(fn ($a) => $a->course) : collect();
        $myIds = $admissions->pluck('course_id')->all();
        $examIds = array_values(array_unique(array_merge($admissions->pluck('course.exam_id')->all(), $user ? $user->examInterests()->pluck('competitive_exams.id')->all() : [])));
        $courseNames = $admissions->pluck('course.title', 'course_id');
        $tab = in_array($request->tab, ['classes', 'tests', 'articles'], true) ? $request->tab : 'classes';

        // classes: my courses' classes unlocking soon (or opened this week), then free live / recorded promo classes
        $myClasses = $myIds ? CourseClass::with('libraryClass:id,title,thumbnail,source,url,instructor,duration_seconds,class_date')
            ->whereIn('course_id', $myIds)->whereNotNull('unlock_at')->where('unlock_at', '>=', now()->subDays(7))
            ->orderBy('unlock_at')->limit(20)->get()->filter(fn ($c) => $c->libraryClass)
            ->map(fn ($c) => (object) ['title' => $c->libraryClass->title, 'sub' => $courseNames[$c->course_id] ?? '', 'thumb' => $c->libraryClass->thumbnailUrl(),
                'starts' => $c->unlock_at, 'url' => url('courses/' . $c->course_id), 'tag' => 'My course', 'exams' => []]) : collect();
        $promo = ContentItem::ofKind('promo_class')->live()->with('exams:id')
            ->when($examIds, fn ($q) => $q->whereIn('content_items.id', fn ($s) => $s->select('item_id')->from('content_item_exams')->whereIn('exam_id', $examIds)))
            ->where(fn ($q) => $q->where('mode', 'recorded')->orWhere('scheduled_at', '>=', now()->subHours(2)))
            ->orderByRaw("mode = 'live' desc")->orderBy('scheduled_at')->orderByDesc('id')->limit(16)->get()
            ->map(fn ($c) => (object) ['title' => $c->title, 'sub' => $c->mode === 'live' ? 'Free live class' : 'Free recorded class',
                'thumb' => $c->image ? Storage::url('files/' . $c->image) : (($v = \App\Services\ClassSource::youtubeId((string) $c->link_url)) ? 'https://img.youtube.com/vi/' . $v . '/mqdefault.jpg' : null),
                'starts' => $c->mode === 'live' ? $c->scheduled_at : null, 'url' => $c->publicUrl(), 'tag' => 'Free', 'exams' => $c->exams->pluck('id')->all()]);

        // tests: my courses' tests that are open or coming, then free tests of my exams (or all)
        $testRow = fn ($t, $tag) => (object) ['title' => $t->title, 'sub' => $t->course->title ?? '', 'thumb' => null, 'starts' => $t->start_at, 'ends' => $t->end_at,
            'url' => url('tests/' . $t->id), 'tag' => $tag, 'mins' => $t->duration_minutes];
        $openTests = fn ($q) => $q->with('course:id,slug,title')->where('status', 'published')->where(fn ($w) => $w->whereNull('end_at')->orWhere('end_at', '>=', now()));
        $myTests = $myIds ? $openTests(CourseExam::query())->whereIn('course_id', $myIds)->orderByRaw('start_at is null')->orderBy('start_at')->limit(20)->get()->map(fn ($t) => $testRow($t, 'My course')) : collect();
        $freeTests = $openTests(CourseExam::query())->where('is_paid', false)->whereNotIn('course_id', $myIds ?: [0])
            ->when($examIds, fn ($q) => $q->whereIn('exam_id', $examIds))->whereNotNull('start_at')->where('start_at', '<=', now()->addDays(15))
            ->orderBy('start_at')->limit(12)->get()->map(fn ($t) => $testRow($t, 'Free'));
        if (Schema::hasTable('kpsc_daily_exams') && (!$examIds || CompetitiveExam::whereIn('id', $examIds)->where('slug', 'kpsc')->exists())) {
            $freeTests = $freeTests->concat(\App\Models\DailyExam::where('section', 'Daily Exam')->where('status', 1)->where('ended_at', '>', now())->orderBy('started_at')->limit(6)->get()
                ->map(fn ($d) => (object) ['title' => $d->examtitle ?: 'Daily exam', 'sub' => 'Kerala PSC daily exam', 'thumb' => null, 'starts' => $d->started_at ? \Illuminate\Support\Carbon::parse($d->started_at) : null,
                    'ends' => $d->ended_at ? \Illuminate\Support\Carbon::parse($d->ended_at) : null, 'url' => url('kpsc/daily-exam/' . $d->exam_date . '/' . $d->id), 'tag' => 'Free', 'mins' => null]));
        }

        // articles: for my exams first, then the latest of everything else
        $articleQ = fn () => ContentItem::ofKind('article')->live()->with('exams:id,short_name,name,color')->orderByDesc('published_at')->orderByDesc('id');
        $forMe = $examIds ? $articleQ()->whereIn('content_items.id', fn ($s) => $s->select('item_id')->from('content_item_exams')->whereIn('exam_id', $examIds))->limit(12)->get() : collect();
        $moreArticles = $articleQ()->whereNotIn('content_items.id', $forMe->pluck('id')->all() ?: [0])->limit(12)->get();

        return view('kpsc.hub.learn', [
            'title' => 'Learn', 'user' => $user, 'tab' => $tab, 'admissions' => $admissions,
            'myClasses' => $myClasses, 'promo' => $promo, 'myTests' => $myTests, 'freeTests' => $freeTests, 'forMe' => $forMe, 'moreArticles' => $moreArticles,
        ]);
    }

    // ============================================================ Profile tab

    public function account(Request $request)
    {
        $user = $request->user();
        $orders = CourseAdmission::with('course:id,slug,title')->where('user_id', $user->id)->where('status', '!=', 'pending')->orderByDesc('id')->limit(30)->get();

        $progress = app(\App\Services\StudentProgress::class);

        return view('kpsc.hub.account', [
            'progress' => $progress->summary($user->id), 'board' => $progress->leaderboard($user->id), 'activity' => $progress->activity($user->id),
            'trend' => $progress->trend($user->id, 'day'), 'referral' => app(\App\Services\ReferralService::class)->forStudent($user),
            'title' => 'My profile', 'user' => $user, 'admissions' => $user->activeAdmissions()->filter(fn ($a) => $a->course),
            'interests' => $user->examInterests()->orderBy('position')->get(),
            'favs' => Schema::hasTable('student_favourite_categories') ? $user->favouriteCategories()->with('exam:id,short_name,name,color')->get() : collect(),
            'orders' => $orders, 'section' => in_array($request->section, ['profile', 'password', 'courses', 'orders', 'exams', 'settings', 'referral'], true) ? $request->section : null,
        ]);
    }

    /** Graph data for the period tabs (day / month / year) on My account. */
    public function trend(Request $request)
    {
        return response()->json(app(\App\Services\StudentProgress::class)->trend($request->user()->id, (string) $request->query('period', 'day')));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'mobile' => ['nullable', 'regex:/^[0-9+\-\s]{10,15}$/'],
            'gender' => ['nullable', 'in:Male,Female,Other'],
            'dob' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:255'], 'city' => ['nullable', 'string', 'max:80'], 'state' => ['nullable', 'string', 'max:80'],
            'pincode' => ['nullable', 'regex:/^[0-9]{6}$/'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], ['mobile.regex' => 'Enter a valid mobile number.', 'pincode.regex' => 'Pincode has 6 digits.']);

        foreach (['name', 'email', 'mobile', 'gender', 'dob', 'address', 'city', 'state', 'pincode'] as $f) {
            $user->{$f} = $data[$f] ?? null;
        }
        if ($request->hasFile('photo')) {
            if ($user->image && !preg_match('#^https?://#', $user->image)) {
                Storage::disk('public')->delete(str_contains($user->image, '/') ? $user->image : 'users/' . $user->image);
            }
            $name = Str::random(40) . '.' . $request->file('photo')->extension();
            $request->file('photo')->storeAs('users', $name, 'public');
            $user->image = $name;
        } elseif ($request->boolean('remove_photo') && $user->image) {
            $user->image = null;
        }
        $user->save();

        return redirect(url('my-account?section=profile'))->with('hub_msg', 'Your profile is saved.');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();
        $rules = ['password' => ['required', 'string', 'min:6', 'confirmed']];
        if ($user->password) {
            $rules['current_password'] = ['required', fn ($a, $v, $fail) => Hash::check($v, $user->password) ? null : $fail('Your current password is not correct.')];
        }
        $request->validate($rules);
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect(url('my-account?section=password'))->with('hub_msg', 'Your password is changed.');
    }
}
