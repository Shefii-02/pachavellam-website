<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;

use Illuminate\Database\Eloquent\Model;

/**
 * An exam type on the platform (KPSC, RRB, SSC, KTET, UGC NET, ...). Managed in General > Exam Types.
 * Categories, courses, features and coupons all hang off an exam type.
 */
class CompetitiveExam extends Model
{
    use LogsActivity;
    protected $table = 'competitive_exams';

    protected $guarded = ['id'];

    protected $casts = ['status' => 'boolean'];

    /** Exams that already have their own admin module with a sidebar (banners, notifications, ...). */
    public const MODULES = [
        'kpsc' => ['layout' => 'cms.section', 'home' => 'admin/kpsc'],
        'rrb' => ['layout' => 'admin.rrb.section', 'home' => 'admin/rrb'],
        'ssc' => ['layout' => 'admin.ssc.section', 'home' => 'admin/ssc'],
        'ktet' => ['layout' => 'admin.ktet.section', 'home' => 'admin/ktet'],
        'ugc-net' => ['layout' => 'admin.ugc_net.section', 'home' => 'admin/ugc-net'],
    ];

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_exam_interests', 'exam_id', 'user_id')
            ->withPivot('source')->withTimestamps();
    }

    public function categories()
    {
        return $this->hasMany(ExamCategory::class, 'exam_id');
    }

    public function courses()
    {
        return $this->hasMany(ExamCourse::class, 'exam_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1)->orderBy('position')->orderBy('id');
    }

    /** Layout (sidebar) used for this exam's pages. Exams added later get the generic exam sidebar. */
    public function layout(): string
    {
        return self::MODULES[$this->slug]['layout'] ?? 'admin.exam.section';
    }

    /** The exam's own home page in the admin. */
    public function homeUrl(): string
    {
        return url(self::MODULES[$this->slug]['home'] ?? 'admin/exam/' . $this->slug);
    }

    /** Common pages (categories, courses, ...) of this exam type, e.g. examUrl('courses'). */
    public function examUrl(string $path = ''): string
    {
        return url('admin/exam/' . $this->slug . ($path !== '' ? '/' . ltrim($path, '/') : ''));
    }

    /** Interested students page. Exams with a module keep their old address. */
    public function interestedUrl(): string
    {
        return isset(self::MODULES[$this->slug])
            ? url(self::MODULES[$this->slug]['home'] . '/interested-students')
            : url('admin/students/exam/' . $this->slug);
    }
}
