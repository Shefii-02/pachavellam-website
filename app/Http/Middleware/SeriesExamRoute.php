<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Exam\ExamSeriesController;
use App\Models\CompetitiveExam;
use App\Models\ExamFeature;
use Closure;
use Illuminate\Http\Request;

/**
 * Exams of a series are made season by season, not course by course:
 *   admin/exam/{exam}/series/{kind}/exams/...                    the exams of the series
 *   admin/exam/{exam}/series/{kind}/seasons/{season}/exams/...   the same, for one season (the exam is placed in it)
 * The exam builder is the course exam builder; this finds the internal series course and the feature for the series
 * and hands them over, so nobody has to be added to a course to work here.
 */
class SeriesExamRoute
{
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $params = $route->parameters();
        abort_unless(isset(ExamSeriesController::KINDS[$params['kind'] ?? '']), 404);

        $exam = CompetitiveExam::where('slug', $params['exam'])->firstOrFail();
        $course = ExamSeriesController::seriesCourse($exam, (int) $request->user()->id);
        $feature = ExamFeature::where('slug', ExamSeriesController::KINDS[$params['kind']][0])->firstOrFail();
        if (!$course->features()->where('exam_features.id', $feature->id)->exists()) {
            $course->features()->attach($feature->id, ['scope' => 'all']);
        }
        if (isset($params['season'])) {
            $request->merge(['season' => $params['season']]);
        }
        $request->attributes->set('series_kind', $params['kind']);   // the pages show the series / season instead of the course

        // the builder expects (exam, course, feature, ...the rest) in this order
        $rest = array_diff_key($params, array_flip(['exam', 'kind', 'season']));
        foreach (array_keys($params) as $name) {
            $route->forgetParameter($name);
        }
        $route->setParameter('exam', $params['exam']);
        $route->setParameter('course', $course->id);
        $route->setParameter('feature', $feature->slug);
        foreach ($rest as $name => $value) {
            $route->setParameter($name, $value);
        }

        return $next($request);
    }
}
