<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

/** One question of the library; the words live in library_question_texts, one row per language. */
class LibraryQuestion extends Model
{
    use LogsActivity;
    use SoftDeletes;
    protected bool $tracksDeleter = true;
    protected $table = 'library_questions';

    protected $guarded = ['id'];

    protected $casts = [
        'option_images' => 'array',
        'correct_options' => 'array',
        'is_rich' => 'boolean',
        'status' => 'boolean',
    ];

    public const TYPES = [
        'single' => 'Multiple choice - one right answer',
        'multiple' => 'Multiple choice - more than one right answer',
        'truefalse' => 'True / False',
    ];

    public const MAX_OPTIONS = 6;

    /** The right option numbers, e.g. [2] or [1, 3]. */
    public function correctList(): array
    {
        $list = $this->question_type === 'multiple' ? ($this->correct_options ?: []) : [];

        return $list ?: [(int) $this->correct_option];
    }

    /** "B" or "A, C". */
    public function answerLabel(): string
    {
        return collect($this->correctList())->map(fn ($n) => chr(64 + $n))->implode(', ');
    }

    /** Question as one line of plain text (lists, pickers); formatted questions lose their formatting. */
    public function plainQuestion(int $limit = 0): string
    {
        $raw = (string) ($this->mainText()?->question ?? '');
        $text = $this->is_rich ? \App\Support\SafeHtml::text($raw) : trim(preg_replace('/\s+/u', ' ', $raw));
        if ($text === '') {
            $text = '(picture question)';
        }

        return $limit ? \Illuminate\Support\Str::limit($text, $limit) : $text;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->question_type] ?? $this->question_type;
    }

    public function exam()
    {
        return $this->belongsTo(CompetitiveExam::class, 'exam_id');
    }

    public function folder()
    {
        return $this->belongsTo(QuestionFolder::class, 'folder_id');
    }

    public function texts()
    {
        return $this->hasMany(LibraryQuestionText::class, 'question_id')->orderBy('id');
    }

    public function categories()
    {
        return $this->belongsToMany(ExamCategory::class, 'library_question_categories', 'question_id', 'category_id');
    }

    public function batch()
    {
        return $this->belongsTo(LibraryImportBatch::class, 'import_batch_id');
    }

    /** Text used where one language is needed (lists, previews): the first one written. */
    public function mainText(): ?LibraryQuestionText
    {
        return $this->relationLoaded('texts') ? $this->texts->first() : $this->texts()->first();
    }

    public function hasImages(): bool
    {
        return $this->question_image || $this->explanation_image || !empty(array_filter($this->option_images ?? []));
    }

    /** Every picture file this question owns. */
    public function imagePaths(): array
    {
        return array_values(array_filter(array_merge([$this->question_image, $this->explanation_image], array_values($this->option_images ?? []))));
    }

    public static function hashOf(?string $text): string
    {
        return sha1(mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $text))));
    }
}
