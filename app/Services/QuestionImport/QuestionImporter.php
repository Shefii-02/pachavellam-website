<?php

namespace App\Services\QuestionImport;

use App\Models\CompetitiveExam;
use App\Models\ExamCategory;
use App\Models\LibraryImportBatch;
use App\Models\LibraryQuestion;
use App\Models\QuestionFolder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Saves the rows of a ParsedFile as library questions.
 *
 *   $batch = (new QuestionImporter)->import($parsed, [
 *       'exam' => CompetitiveExam, 'folder_id' => 12, 'category_ids' => [3, 4],
 *       'type' => 'english|native|image|multi', 'language' => 'ml', 'format' => 'docx|csv|xlsx',
 *       'file_name' => 'set1.docx', 'skip_duplicates' => true, 'user_id' => 1,
 *   ]);
 *
 * A question with a problem is skipped and reported; the others are still imported. If something unexpected
 * goes wrong nothing is kept (the database changes are rolled back and the stored pictures are removed).
 */
class QuestionImporter
{
    private const IMAGE_TYPES = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
    private const MAX_PROBLEMS = 300;

    private array $problems = [];
    private int $skipped = 0;
    private int $imagesStored = 0;
    private array $storedFiles = [];
    private ?LibraryImportBatch $batch = null;

    public function import(ParsedFile $file, array $cfg): LibraryImportBatch
    {
        /** @var CompetitiveExam $exam */
        $exam = $cfg['exam'];
        $type = $cfg['type'];
        $folder = QuestionFolder::where('exam_id', $exam->id)->findOrFail($cfg['folder_id']);
        $categoryIds = ExamCategory::where('exam_id', $exam->id)->whereIn('id', $cfg['category_ids'] ?? [])->pluck('id')->all();
        $multi = !(config("question_library.types.$type.single", true));
        $language = $multi ? null : ($cfg['language'] ?? config("question_library.types.$type.language") ?? 'en');

        $this->problems = array_map(fn ($p) => $p + ['level' => 'warning'], $file->problems);
        $this->skipped = 0;
        $this->imagesStored = 0;
        $this->storedFiles = [];

        $groups = $multi ? $this->groupTranslations($file->rows) : $this->singleLanguage($file->rows, $language);

        $seen = ($cfg['skip_duplicates'] ?? true)
            ? array_flip(LibraryQuestion::where('folder_id', $folder->id)->whereNotNull('question_hash')->pluck('question_hash')->all())
            : null;

        $imported = 0;

        try {
            DB::beginTransaction();

            $this->batch = LibraryImportBatch::create([
                'exam_id' => $exam->id, 'folder_id' => $folder->id, 'created_by' => $cfg['user_id'] ?? null,
                'file_name' => mb_substr($cfg['file_name'], 0, 250), 'file_format' => $cfg['format'], 'file_type' => $type,
                'language' => $language, 'category_ids' => $categoryIds, 'total_found' => count($groups),
            ]);

            foreach ($groups as $g) {
                $q = $this->build($g, $file);
                if ($q === null) {
                    continue;
                }

                $hash = LibraryQuestion::hashOf($q['hash_source']);
                if ($seen !== null && $q['hash_source'] !== '' && isset($seen[$hash])) {
                    $this->discard($q['images']);
                    $this->skip($g['label'], 'Already in this folder (same question text) - skipped.');
                    continue;
                }

                $question = LibraryQuestion::create([
                    'exam_id' => $exam->id, 'folder_id' => $folder->id, 'correct_option' => $q['correct'],
                    'question_image' => $q['images']['question'] ?? null,
                    'option_images' => array_filter([1 => $q['images']['option_1'] ?? null, 2 => $q['images']['option_2'] ?? null, 3 => $q['images']['option_3'] ?? null, 4 => $q['images']['option_4'] ?? null]) ?: null,
                    'explanation_image' => $q['images']['explanation'] ?? null,
                    'difficulty' => $q['difficulty'], 'question_hash' => $q['hash_source'] !== '' ? $hash : null,
                    'source' => $cfg['format'], 'import_batch_id' => $this->batch->id, 'created_by' => $cfg['user_id'] ?? null,
                ]);

                foreach ($q['texts'] as $lang => $t) {
                    $question->texts()->create([
                        'lang' => $lang, 'question' => $t['question'] !== '' ? $t['question'] : null,
                        'option_1' => $t['options'][1] ?: null, 'option_2' => $t['options'][2] ?: null,
                        'option_3' => $t['options'][3] ?: null, 'option_4' => $t['options'][4] ?: null,
                        'explanation' => $t['explanation'] !== '' ? $t['explanation'] : null,
                    ]);
                }
                if ($categoryIds) {
                    $question->categories()->sync($categoryIds);
                }

                if ($seen !== null && $q['hash_source'] !== '') {
                    $seen[$hash] = true;
                }
                $imported++;
            }

            $this->batch->update([
                'imported' => $imported, 'skipped' => $this->skipped, 'images_stored' => $this->imagesStored,
                'problems' => array_slice($this->problems, 0, self::MAX_PROBLEMS),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            foreach ($this->storedFiles as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e instanceof ImportException ? $e : new ImportException('The import stopped because of an unexpected problem and nothing was saved. (' . Str::limit($e->getMessage(), 160) . ')');
        } finally {
            $file->close();
        }

        return $this->batch;
    }

    // ------------------------------------------------------------ grouping

    /** One row = one question in one language. */
    private function singleLanguage(array $rows, string $language): array
    {
        return array_map(fn ($r) => [
            'label' => $this->label($r),
            'rows' => [$r],
            'langs' => [$language => $r],
        ], $rows);
    }

    /** Rows with the same No are the same question in different languages; a row without No joins the question above. */
    private function groupTranslations(array $rows): array
    {
        $groups = [];
        $current = null;

        foreach ($rows as $r) {
            $lang = $this->languageCode($r['language']);
            $no = $r['no'];

            $joins = $current !== null
                && (($no !== null && (string) $no === (string) $current['no']) || ($no === null && $lang !== null && !isset($current['langs'][$lang])));

            if (!$joins) {
                if ($current) {
                    $groups[] = $current;
                }
                $current = ['label' => $this->label($r), 'no' => $no, 'rows' => [], 'langs' => []];
            }

            $current['rows'][] = $r;
            if ($lang === null) {
                $current['bad_language'][] = $r;
            } elseif (isset($current['langs'][$lang])) {
                $current['duplicate_language'][] = $lang;
            } else {
                $current['langs'][$lang] = $r;
            }
        }
        if ($current) {
            $groups[] = $current;
        }

        return $groups;
    }

    private function languageCode(?string $name): ?string
    {
        $needle = mb_strtolower(trim((string) $name));
        foreach (config('question_library.languages') as $code => $info) {
            if (in_array($needle, array_map('mb_strtolower', $info['aliases']), true)) {
                return $code;
            }
        }

        return null;
    }

    private function label(array $row): string
    {
        return $row['no'] !== null ? "Question {$row['no']} (row {$row['row']})" : "Row {$row['row']}";
    }

    // --------------------------------------------------------- one question

    /** The question ready to save, or null when it has a problem (already reported). */
    private function build(array $g, ParsedFile $file): ?array
    {
        $label = $g['label'];

        foreach ($g['bad_language'] ?? [] as $r) {
            $this->warn($label, 'A row has a language that is not known (' . ($r['language'] ?: 'empty') . '). Use English, Malayalam, Tamil, Hindi, Kannada or the codes en, ml, ta, hi, kn. That row was left out.');
        }
        foreach (array_unique($g['duplicate_language'] ?? []) as $lang) {
            $this->warn($label, "The language {$lang} appears twice for this question. The first one was kept.");
        }
        if (!$g['langs']) {
            $this->skip($label, 'No row with a known language - skipped.');
            return null;
        }

        // pictures of the whole question: first picture found for each place, across its language rows
        $images = [];
        foreach (['question', 'option_1', 'option_2', 'option_3', 'option_4', 'explanation'] as $slot) {
            foreach ($g['rows'] as $r) {
                foreach ($r['images'][$slot] ?? [] as $ref) {
                    $path = $this->storeImage($ref, $file, $label, $slot);
                    if ($path) {
                        $images[$slot] = $path;
                        break 2;
                    }
                }
            }
        }

        $texts = [];
        foreach ($g['langs'] as $lang => $r) {
            $texts[$lang] = [
                'question' => $this->clean($r['question']),
                'options' => array_map(fn ($o) => $this->clean($o), $r['options']),
                'explanation' => $this->clean($r['explanation']),
            ];
        }

        $main = $texts[array_key_first($texts)];
        $hasOption = fn (int $n) => $main['options'][$n] !== '' || isset($images["option_$n"]);

        if ($main['question'] === '' && !isset($images['question'])) {
            $this->discard($images);
            $this->skip($label, 'The question is empty (no text and no picture) - skipped.');
            return null;
        }
        if (count(array_filter([1, 2, 3, 4], $hasOption)) < 2) {
            $this->discard($images);
            $this->skip($label, 'A question needs at least two options (text or picture) - skipped.');
            return null;
        }

        // the right answer: the first row of the question that gives one
        $correct = null;
        $answerRaw = '';
        foreach ($g['langs'] as $lang => $r) {
            if (trim($r['answer']) === '') {
                continue;
            }
            $answerRaw = $r['answer'];
            $correct = $this->resolveAnswer($r['answer'], $texts[$lang]['options']);
            break;
        }
        if ($answerRaw === '') {
            $this->discard($images);
            $this->skip($label, 'No answer is given - skipped.');
            return null;
        }
        if ($correct === null || !$hasOption($correct)) {
            $this->discard($images);
            $this->skip($label, "The answer \"{$answerRaw}\" is not one of the options (use A, B, C or D) - skipped.");
            return null;
        }

        $first = reset($g['langs']);

        return [
            'texts' => $texts, 'images' => $images, 'correct' => $correct,
            'difficulty' => $this->difficulty($first['difficulty'] ?? null),
            'hash_source' => $main['question'],
        ];
    }

    /** A, b, (C), 2, "Option D", or the full text of one of the options. */
    private function resolveAnswer(string $raw, array $options): ?int
    {
        $a = trim($raw);
        $a = trim(preg_replace('/^(option|opt|ans(wer)?)\s*[:\-]?\s*/iu', '', $a), " \t.:()[]");

        if (preg_match('/^[A-Da-d]$/', $a)) {
            return ord(strtoupper($a)) - 64;
        }
        if (preg_match('/^[1-4]$/', $a)) {
            return (int) $a;
        }

        $norm = fn ($s) => mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $s)));
        foreach ($options as $n => $text) {
            if ($text !== '' && $norm($text) === $norm($raw)) {
                return $n;
            }
        }

        return null;
    }

    private function difficulty(?string $v): ?string
    {
        $v = mb_strtolower(trim((string) $v));

        return match (true) {
            in_array($v, ['easy', 'e', 'simple'], true) => 'easy',
            in_array($v, ['medium', 'm', 'moderate', 'average'], true) => 'medium',
            in_array($v, ['hard', 'h', 'difficult'], true) => 'hard',
            default => null,
        };
    }

    private function clean(?string $text): string
    {
        $text = preg_replace("/[ \t]+/u", ' ', str_replace(["\r", "\u{00A0}"], ['', ' '], (string) $text));
        $text = preg_replace("/ ?\n ?/u", "\n", $text);

        return trim(preg_replace("/\n{3,}/u", "\n\n", $text));
    }

    // -------------------------------------------------------------- pictures

    /** Checks a picture and saves it; returns its path or null (a problem is recorded). */
    private function storeImage(array $ref, ParsedFile $file, string $label, string $slot): ?string
    {
        $bytes = $file->imageBytes($ref);
        $name = $ref['kind'] === 'zip' ? "\"{$ref['name']}\"" : 'a picture';
        $where = str_replace('_', ' ', $slot);

        if ($bytes === null || $bytes === '') {
            $this->warn($label, $ref['kind'] === 'zip'
                ? "The picture {$ref['name']} ({$where}) was not found in the pictures zip."
                : "A picture in the {$where} could not be read.");
            return null;
        }
        if (strlen($bytes) > (int) config('question_library.max_image_bytes')) {
            $this->warn($label, "The picture in the {$where} is bigger than 3 MB and was left out.");
            return null;
        }

        $info = @getimagesizefromstring($bytes);
        $ext = $info ? (self::IMAGE_TYPES[$info[2]] ?? null) : null;
        if (!$ext) {
            $this->warn($label, "The picture in the {$where} is not a JPG, PNG, GIF or WEBP image (Word equations and EMF / WMF drawings cannot be imported - paste them as a normal picture) and was left out.");
            return null;
        }

        $path = 'question-library/' . $this->batch->exam->slug . '/' . $this->batch->id . '/' . Str::random(32) . '.' . $ext;
        Storage::disk('public')->put($path, $bytes);
        $this->storedFiles[] = $path;
        $this->imagesStored++;

        return $path;
    }

    /** A question that was skipped does not keep the pictures that were already saved for it. */
    private function discard(array $images): void
    {
        foreach ($images as $path) {
            Storage::disk('public')->delete($path);
            $this->storedFiles = array_values(array_diff($this->storedFiles, [$path]));
            $this->imagesStored--;
        }
    }

    // --------------------------------------------------------------- reporting

    private function skip(string $label, string $message): void
    {
        $this->skipped++;
        $this->problems[] = ['row' => $label, 'message' => $message, 'level' => 'error'];
    }

    private function warn(string $label, string $message): void
    {
        $this->problems[] = ['row' => $label, 'message' => $message, 'level' => 'warning'];
    }
}
