<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamCourseNode extends Model
{
    protected $table = 'exam_course_nodes';

    protected $guarded = ['id'];

    protected $casts = [
        'is_free_preview' => 'boolean',
        'status' => 'boolean',
    ];

    /** extension => kind */
    public const KINDS = [
        'pdf' => 'pdf',
        'doc' => 'doc', 'docx' => 'doc', 'txt' => 'doc', 'xls' => 'doc', 'xlsx' => 'doc', 'ppt' => 'doc', 'pptx' => 'doc',
        'mp3' => 'audio', 'm4a' => 'audio', 'aac' => 'audio', 'wav' => 'audio', 'ogg' => 'audio',
        'mp4' => 'video', 'mov' => 'video', 'webm' => 'video',
        'jpg' => 'image', 'jpeg' => 'image', 'png' => 'image', 'webp' => 'image',
        'zip' => 'other',
    ];

    public const KIND_ICONS = [
        'pdf' => 'bi-file-earmark-pdf text-danger',
        'doc' => 'bi-file-earmark-text text-primary',
        'audio' => 'bi-file-earmark-music text-success',
        'video' => 'bi-file-earmark-play text-info',
        'image' => 'bi-file-earmark-image text-warning',
        'link' => 'bi-link-45deg text-secondary',
        'other' => 'bi-file-earmark-zip text-secondary',
    ];

    public function course()
    {
        return $this->belongsTo(ExamCourse::class, 'course_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('id');
    }

    /** Notes / solutions linked to this node (e.g. a question paper's quick learn notes). */
    public function links()
    {
        return $this->belongsToMany(self::class, 'exam_course_node_links', 'node_id', 'related_node_id')
            ->withPivot('relation')->withTimestamps();
    }

    public function ancestors()
    {
        $list = collect();
        $node = $this->parent;
        while ($node) {
            $list->prepend($node);
            $node = $node->parent;
        }

        return $list;
    }

    public function descendantIds(): array
    {
        $ids = [];
        $frontier = [$this->id];
        while ($frontier) {
            $frontier = self::whereIn('parent_id', $frontier)->pluck('id')->all();
            $ids = array_merge($ids, $frontier);
        }

        return $ids;
    }

    public static function kindFor(?string $extension): string
    {
        return self::KINDS[strtolower((string) $extension)] ?? 'other';
    }
}
