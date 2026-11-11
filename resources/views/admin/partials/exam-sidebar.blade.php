{{-- The sidebar of RRB, SSC, KTET, UGC NET and any exam type added in General > Exam Types: the same groups as KPSC.
     Needs $slug (the exam type's slug). --}}
@php
    $examModel = \App\Models\CompetitiveExam::where('slug', $slug)->first();
    $me = auth()->user();
    $isAdmin = \App\Support\StaffAccess::can($me, 'Admissions view') || \App\Support\StaffAccess::can($me, 'Revenue view');
    $link = fn ($href, $icon, $label, $active) => '<li class="nav-item"><a class="nav-link ' . $active . '" href="' . e($href) . '"><i class="bi ' . $icon . ' fa-fw me-2"></i>' . $label . '</a></li>';
    $heading = fn ($title) => '<li class="nav-item mt-3 mb-1 px-3 small text-uppercase text-white-50" style="letter-spacing:.06em;font-size:.7rem">' . $title . '</li>';
    $show = fn ($key) => \App\Support\ExamFeatures::shows($examModel, $me, $key);   // the exam type has the feature (General > Exam Types) and the person may View it
    $any = fn (...$keys) => collect($keys)->contains(fn ($k) => $show($k));
    $is = fn (...$patterns) => request()->is(...$patterns) ? 'active' : '';
@endphp

{!! $link(url('admin'), 'bi-house', 'Home', '') !!}

{!! $heading('Overview') !!}
@if ($show('dashboard'))
{!! $link($examModel ? $examModel->homeUrl() : url('admin/exam/' . $slug), 'bi-speedometer2', 'Dashboard', active_exam_menu('dashboard') ?: active_menu('dashboard')) !!}
@endif
@if ($show('interested_students'))
{!! $link($examModel ? $examModel->interestedUrl() : url('admin/' . $slug . '/interested-students'), 'bi-people', 'Interested Students', active_menu('interested-students') ?: $is('admin/students/exam/*')) !!}
@endif

@if ($show('new_joins'))
    {!! $link(url('admin/new-joins?exam=' . ($examModel->id ?? '')), 'bi-person-plus', 'New joins', $is('admin/new-joins*')) !!}
@endif

{!! $heading('Courses &amp; Libraries') !!}
@if ($show('categories'))
{!! $link(url('admin/exam/' . $slug . '/category'), 'bi-diagram-3', 'Categories', active_exam_menu('category')) !!}
@endif
@if ($show('courses'))
{!! $link(url('admin/exam/' . $slug . '/courses'), 'bi-mortarboard', 'Courses', active_exam_menu('courses')) !!}
@endif
@if ($show('question_library'))
{!! $link(url('admin/exam/' . $slug . '/question-library'), 'bi-collection', 'Question Library', active_exam_menu('question-library')) !!}
@endif
@if ($show('class_library'))
{!! $link(url('admin/exam/' . $slug . '/class-library'), 'bi-collection-play', 'Class Library', active_exam_menu('class-library')) !!}
@endif
@if ($show('roaring_study'))
{!! $link(url('admin/exam/' . $slug . '/roaring-study'), 'bi-lightning-charge', 'Roaring Study', $is('admin/roaring-study*', 'admin/exam/*/roaring-study*')) !!}
@endif


@if ($slug === 'kpsc')
    @if ($any('daily', 'omr', 'exam_instructions', 'exam_reports', 'ca', 'gk', 'special')){!! $heading('Exams') !!}@endif
    @if ($show('daily'))
    {!! $link(url('admin/exam/kpsc/series/daily'), 'bi-list-task', 'Daily Exam Series', (request()->is('admin/exam/kpsc/series/daily*') || request()->is('admin/kpsc/daily-exam*')) ? 'active' : '') !!}
    @endif
    @if ($show('omr'))
    {!! $link(url('admin/exam/kpsc/series/omr'), 'bi-pencil-square', 'OMR Exam Series', (request()->is('admin/exam/kpsc/series/omr*') || request()->is('admin/kpsc/model-exam*')) ? 'active' : '') !!}
    @endif
    @if ($show('exam_instructions'))
    {!! $link(url('admin/kpsc/exam-instructions'), 'bi-card-text', 'Exam instructions', request()->is('admin/kpsc/exam-instructions*') ? 'active' : '') !!}
    @endif
    @if ($show('exam_reports'))
    {!! $link(url('admin/kpsc/exam-reports'), 'bi-flag', 'Reported errors', request()->is('admin/kpsc/exam-reports*') ? 'active' : '') !!}
    @endif
    @if ($show('ca'))
    {!! $link(url('admin/exam/kpsc/series/ca'), 'bi-calendar2-check', 'CA Exam Series', (request()->is('admin/exam/kpsc/series/ca*') || request()->is('admin/kpsc/ca-daily-exam*')) ? 'active' : '') !!}
    @endif
    @if ($any('gk', 'special'))
        @php $kpscSeries = $examModel ? \App\Http\Controllers\Exam\ExamSeriesController::seriesCourseId($examModel) : null; @endphp
        @if ($show('gk'))
        {!! $link(url('admin/exam/kpsc/series/gk'), 'bi-globe2', 'GK Exam Series', (request()->is('admin/exam/kpsc/series/gk*') || ($kpscSeries && request()->is('admin/exam/kpsc/courses/' . $kpscSeries . '/exams/gk-exams*'))) ? 'active' : '') !!}
        @endif
        @if ($show('special'))
        {!! $link(url('admin/exam/kpsc/series/special'), 'bi-stars', 'Special Exam Series', request()->is('admin/exam/kpsc/series/special*') ? 'active' : '') !!}
        @endif
    @endif

    @if ($any('study_materials', 'syllabus', 'subjects', 'pyq', 'psc_bulletin', 'daily_ca', 'youtube_classes')){!! $heading('Study Content') !!}@endif
    @if ($show('study_materials'))
    {!! $link(url('admin/content/materials?exam=kpsc'), 'bi-file-earmark-arrow-down-fill', 'Study Material', $is('admin/content/materials*')) !!}
    @endif
    @if ($show('syllabus'))
    {!! $link(url('admin/content/syllabus?exam=kpsc'), 'bi-plus-slash-minus', 'Syllabus', $is('admin/content/syllabus*')) !!}
    @endif
    @if ($show('subjects'))
    {!! $link(url('admin/kpsc/subjects'), 'bi-journal-text', 'Subjects', active_menu('subjects')) !!}
    @endif
    @if ($show('pyq'))
    {!! $link(url('admin/kpsc/prev-qstn'), 'bi-question-circle', 'PYQ', active_menu('prev-qstn')) !!}
    @endif
    @if ($show('psc_bulletin'))
    {!! $link(url('admin/kpsc/psc-bulletin'), 'bi-patch-question-fill', 'Psc Bulletin', active_menu('psc-bulletin')) !!}
    @endif
    @if ($show('daily_ca'))
    {!! $link(url('admin/kpsc/daily-ca'), 'bi-bookmark-star-fill', 'Daily CA', active_menu('daily-ca')) !!}
    @endif
    @if ($show('youtube_classes'))
    {!! $link(url('admin/kpsc/special-videos'), 'bi-camera-reels', 'Youtube Class', active_menu('special-videos')) !!}
    @endif

    @if ($show('results')){!! $heading('Results') !!}@endif
    @if ($show('results'))
    {!! $link(url('admin/content/results?exam=kpsc'), 'bi-file-earmark-spreadsheet', 'Results', $is('admin/content/results*')) !!}
    @endif
@else
    @if ($examModel && $any('daily', 'omr', 'ca', 'gk', 'special'))
        @php $seriesCourse = \App\Http\Controllers\Exam\ExamSeriesController::seriesCourseId($examModel); @endphp
        {!! $heading('Exams') !!}
        @foreach (\App\Http\Controllers\Exam\ExamSeriesController::KINDS as $kind => [$featureSlug, $label])
          @if ($show($kind))
            {!! $link(url('admin/exam/' . $slug . '/series/' . $kind), ['daily' => 'bi-list-task', 'omr' => 'bi-pencil-square', 'ca' => 'bi-calendar2-check', 'gk' => 'bi-globe2', 'special' => 'bi-stars'][$kind], $label, (request()->is('admin/exam/' . $slug . '/series/' . $kind . '*') || ($seriesCourse && request()->is('admin/exam/' . $slug . '/courses/' . $seriesCourse . '/exams/' . $featureSlug . '*'))) ? 'active' : '') !!}
          @endif
        @endforeach
    @endif
    @if ($any('syllabus', 'study_materials', 'subjects', 'youtube_classes', 'results'))
        @if ($any('syllabus', 'study_materials', 'subjects', 'youtube_classes')){!! $heading('Study Content') !!}@endif
        @if ($show('syllabus')){!! $link(url('admin/content/syllabus?exam=' . $slug), 'bi-plus-slash-minus', 'Syllabus', $is('admin/content/syllabus*')) !!}@endif
        @if ($show('study_materials')){!! $link(url('admin/content/materials?exam=' . $slug), 'bi-file-earmark-arrow-down-fill', 'Study Materials', $is('admin/content/materials*')) !!}@endif
        @if ($show('subjects')){!! $link(url('admin/exam/' . $slug . '/subjects'), 'bi-journal-text', 'Subjects', $is('admin/exam/' . $slug . '/subjects*')) !!}@endif
        @if ($show('youtube_classes')){!! $link(url('admin/content/free-classes?exam=' . $slug), 'bi-play-btn', 'YouTube Classes', $is('admin/content/free-classes*')) !!}@endif
        @if ($show('results')){!! $heading('Results') !!}@endif
        @if ($show('results')){!! $link(url('admin/content/results?exam=' . $slug), 'bi-file-earmark-spreadsheet', 'Results', $is('admin/content/results*')) !!}@endif
    @endif
@endif
@if ($slug !== 'kpsc' && isset(\App\Models\CompetitiveExam::MODULES[$slug]))
    @if ($any(...collect(config('exam_sections'))->keys()->diff(array_keys(config('exam_sections_common')))->map(fn ($k) => 'section:' . $k)->all())){!! $heading('Content &amp; announcements') !!}@endif
    @foreach (config('exam_sections') as $key => $sec)
        @unless (isset(config('exam_sections_common')[$key]) || !$show('section:' . $key))   {{-- banners, what's new, announcements, notifications, stories are in the main menu --}}
            {!! $link(url('admin/' . $slug . '/' . $key), $sec['icon'], e($sec['label']), active_menu($key)) !!}
        @endunless
    @endforeach
@endif
