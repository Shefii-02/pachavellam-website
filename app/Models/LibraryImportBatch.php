<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryImportBatch extends Model
{
    protected $table = 'library_import_batches';

    protected $guarded = ['id'];

    protected $casts = [
        'category_ids' => 'array',
        'problems' => 'array',
        'undone_at' => 'datetime',
    ];

    public function exam()
    {
        return $this->belongsTo(CompetitiveExam::class, 'exam_id');
    }

    public function folder()
    {
        return $this->belongsTo(QuestionFolder::class, 'folder_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(LibraryQuestion::class, 'import_batch_id');
    }
}
