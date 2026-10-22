<?php

namespace App\Services;

use App\Models\CourseAdmission;
use App\Models\CourseExam;
use App\Models\ExamCourse;
use App\Models\ExamSeason;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * Free / paid seasons of the exam series (Daily, OMR, CA, GK).
 * A free season: every exam is free. A paid season is bought like a course (price, discount, validity); its first
 * `free_exams` exams (oldest first) are free for everyone, the rest open after the purchase and only for its validity.
 * The purchase is made on a hidden course (slug series-season-{id}) so payments, coupons, admissions and refunds work as for any course.
 */
class SeasonAccess
{
    private array $memo = [];

    public function isPaid(ExamSeason $s): bool
    {
        return $s->is_paid && (float) $s->price > 0;
    }

    public function effectivePrice(ExamSeason $s): float
    {
        if (!$this->isPaid($s)) {
            return 0.0;
        }

        return ($s->discount_price !== null && (float) $s->discount_price > 0 && (float) $s->discount_price < (float) $s->price) ? (float) $s->discount_price : (float) $s->price;
    }

    public function validityLabel(ExamSeason $s): string
    {
        return match ($s->validity_type) {
            'days' => $s->validity_days . ' days after buying',
            'date' => 'until ' . optional($s->valid_until)->format('d M Y'),
            default => 'Lifetime',
        };
    }

    /** Has this student bought the season (and is it still valid)? */
    public function owns(?User $user, ExamSeason $s): bool
    {
        if (!$user || !$s->course_id) {
            return false;
        }

        return $this->memo['own' . $user->id . ':' . $s->id] ??= CourseAdmission::where('user_id', $user->id)->where('course_id', $s->course_id)->where('status', 'active')
            ->get()->contains(fn ($a) => $a->grantsAccess());
    }

    /** Ids of the exams that are free in a paid season: the ones marked "free access" when they were created, plus the first N by date ('legacy' = old KPSC exams, 'course' = builder exams). */
    public function freeIds(ExamSeason $s, string $source): array
    {
        if (!$this->isPaid($s)) {
            return [];
        }

        return $this->memo['free' . $s->id . $source] ??= array_values(array_unique(array_merge(
            $source === 'legacy'
                ? \App\Models\DailyExam::where('series_season_id', $s->id)->where('status', 1)->where('free_access', 1)->pluck('id')->all()
                : CourseExam::where('season_id', $s->id)->where('status', 'published')->where('free_access', 1)->pluck('id')->all(),
            $s->free_exams < 1 ? [] : ($source === 'legacy'
                ? \App\Models\DailyExam::where('series_season_id', $s->id)->where('status', 1)->orderBy('exam_date')->orderBy('id')->limit($s->free_exams)->pluck('id')->all()
                : CourseExam::where('season_id', $s->id)->where('status', 'published')->orderByRaw('start_at is null')->orderBy('start_at')->orderBy('entry_date')->orderBy('id')->limit($s->free_exams)->pluck('id')->all())
        )));
    }

    /**
     * What a student can do with one exam: 'free' (everyone), 'unlocked' (bought the season), 'locked' (needs the season).
     * $s null = the exam belongs to no season: free.
     */
    public function state(?User $user, ?ExamSeason $s, string $source, int $examId): string
    {
        if (!$s || !$this->isPaid($s)) {
            return 'free';
        }
        if (in_array($examId, $this->freeIds($s, $source), true)) {
            return 'free';
        }

        return $this->owns($user, $s) ? 'unlocked' : 'locked';
    }

    private array $seasonCache = [];

    public function seasonOfExam(\App\Models\DailyExam $e): ?ExamSeason
    {
        if (!$e->series_season_id) {
            return null;
        }

        return $this->seasonCache[$e->series_season_id] ??= ExamSeason::find($e->series_season_id);
    }

    /** The paid session that keeps this old KPSC exam locked for the student, or null when it is open to them. */
    public function lockedSeason(?User $user, \App\Models\DailyExam $e): ?ExamSeason
    {
        $s = $this->seasonOfExam($e);

        return $s && $this->state($user, $s, 'legacy', $e->id) === 'locked' ? $s : null;
    }

    /** Where a locked exam sends the student: the season page with the Buy button. */
    public function buyUrl(ExamSeason $s): string
    {
        return url('seasons/' . $s->id);
    }

    /** Keep the hidden purchase course of a season in step with its price and validity. */
    public function syncCourse(ExamSeason $s, ?int $userId = null): void
    {
        if (!Schema::hasColumn('exam_seasons', 'course_id')) {
            return;
        }
        $exam = $s->exam ?: \App\Models\CompetitiveExam::find($s->exam_id);
        $paid = $this->isPaid($s);
        $attrs = [
            'exam_id' => $s->exam_id, 'title' => ($exam ? ($exam->short_name ?: $exam->name) . ' - ' : '') . $s->title, 'slug' => 'series-season-' . $s->id,
            'course_type' => $paid ? 'paid' : 'free', 'price' => $paid ? $s->price : 0, 'discount_price' => $paid ? $s->discount_price : null,
            'validity_type' => $s->validity_type ?: 'lifetime', 'validity_days' => $s->validity_type === 'days' ? $s->validity_days : null, 'valid_until' => $s->validity_type === 'date' ? $s->valid_until : null,
            'short_description' => 'Internal: the purchase of an exam series season.',
            // published = can be bought; hidden from the course lists by its series-* slug
            'status' => $paid && $s->status === 'published' ? 'published' : 'archived',
        ];
        $course = $s->course_id ? ExamCourse::find($s->course_id) : null;
        if (!$course && !$paid) {
            return;                                   // free season: nothing to buy
        }
        if ($course) {
            $course->update($attrs);
        } else {
            $course = ExamCourse::create($attrs + ['created_by' => $userId]);
            $s->forceFill(['course_id' => $course->id])->saveQuietly();
        }
    }
}
