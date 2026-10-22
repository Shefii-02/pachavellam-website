<?php

namespace App\Services;

use App\Models\CompetitiveExam;
use App\Models\LibraryQuestion;
use App\Models\QuestionFolder;
use App\Support\SafeHtml;
use Illuminate\Support\Facades\DB;

/**
 * Copies the questions of the old KPSC daily / CA daily / model exams (kpsc_daily_exams_details) into the question library,
 * so every question lives in one place:
 *
 *   Daily Exams (synced) > Daily Exam | CA Daily Exam | Model Exam > 2024-05
 *
 * Safe to run again: a question already copied (legacy_ref "daily:ID") is updated, never duplicated. The old answer column
 * holds the TEXT of the right option, so it is matched against the four options; a question whose answer matches none is
 * counted as "no answer" and left out.
 */
class LegacyQuestionSync
{
    public const ROOT = 'Daily Exams (synced)';

    private ?CompetitiveExam $exam = null;
    private array $folders = [];
    private ?array $seasonNames = null;

    public function __construct()
    {
        $this->exam = CompetitiveExam::where('slug', 'kpsc')->first();
    }

    /** @return array{created:int, updated:int, no_answer:int, empty:int, linked:int} */
    public function syncAll(?callable $progress = null): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'no_answer' => 0, 'empty' => 0, 'linked' => 0];
        if (!$this->exam) {
            return $stats;
        }

        DB::table('kpsc_daily_exams_details as d')
            ->join('kpsc_daily_exams as e', 'e.id', '=', 'd.exam_id')
            ->select('d.*', 'e.section as exam_section', 'e.exam_date', 'e.created_at as exam_created', 'e.season_id', 'e.exam_title', 'e.examtitle', 'e.exam_kind', 'e.slot_no')
            ->orderBy('d.id')
            ->chunk(500, function ($rows) use (&$stats, $progress) {
                DB::transaction(function () use ($rows, &$stats) {
                    foreach ($rows as $row) {
                        $stats[$this->syncRow($row)]++;
                    }
                });
                if ($progress) {
                    $progress($stats);
                }
            });

        return $stats;
    }

    /** One legacy question by its id (used when the old module saves a question). */
    public function syncOne(int $detailId): ?string
    {
        $row = DB::table('kpsc_daily_exams_details as d')
            ->join('kpsc_daily_exams as e', 'e.id', '=', 'd.exam_id')
            ->select('d.*', 'e.section as exam_section', 'e.exam_date', 'e.created_at as exam_created', 'e.season_id', 'e.exam_title', 'e.examtitle', 'e.exam_kind', 'e.slot_no')
            ->where('d.id', $detailId)->first();

        return $row && $this->exam ? $this->syncRow($row) : null;
    }

    /** @return string created|updated|no_answer|empty|linked */
    private function syncRow(object $row): string
    {
        if (!empty($row->library_question_id)) {
            return 'linked';   // it was copied FROM the library: that library question is its twin already
        }

        $text = trim((string) $row->question);
        $options = [1 => trim((string) $row->option_1), 2 => trim((string) $row->option_2), 3 => trim((string) $row->option_3), 4 => trim((string) $row->option_4)];

        if ($text === '' || count(array_filter($options)) < 2) {
            return 'empty';
        }

        $correct = null;
        $answer = mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $row->currect_ans)));
        if ($answer !== '') {
            foreach ($options as $n => $o) {
                if ($o !== '' && mb_strtolower(preg_replace('/\s+/u', ' ', $o)) === $answer) {
                    $correct = $n;
                    break;
                }
            }
        }
        if (!$correct) {
            return 'no_answer';
        }

        $folderId = $this->folderFor($row);
        $ref = 'daily:' . $row->id;
        $lang = preg_match('/\p{Malayalam}/u', $text) ? 'ml' : 'en';

        $question = LibraryQuestion::withTrashed()->where('exam_id', $this->exam->id)->where('legacy_ref', $ref)->first();
        if ($question && $question->trashed()) {
            return 'linked';   // staff moved it to Trash: do not bring it back
        }
        $created = !$question;

        $values = [
            'folder_id' => $folderId, 'correct_option' => $correct, 'question_type' => 'single',
            'question_hash' => LibraryQuestion::hashOf($text), 'status' => (bool) $row->status,
        ];
        if ($created) {
            $question = LibraryQuestion::create($values + ['exam_id' => $this->exam->id, 'source' => 'daily', 'legacy_ref' => $ref]);
        } else {
            $question->update($values);
        }

        $question->texts()->updateOrCreate(['lang' => $lang], [
            'question' => $text, 'option_1' => $options[1] ?: null, 'option_2' => $options[2] ?: null,
            'option_3' => $options[3] ?: null, 'option_4' => $options[4] ?: null,
            'explanation' => trim((string) $row->notes) !== '' ? trim((string) $row->notes) : null,
        ]);

        return $created ? 'created' : 'updated';
    }

    private function folderFor(object $row): int
    {
        if ($row->exam_section === 'CA-Daily-Exam') {
            return $this->caFolder($row);
        }
        $section = match ($row->exam_section) {
            'CA-Daily-Exam' => 'CA Daily Exam',
            'Model Exam' => 'Model Exam',
            default => 'Daily Exam',
        };
        $month = substr((string) ($row->exam_date ?: $row->exam_created ?: ''), 0, 7) ?: 'Undated';
        $key = $section . '|' . $month;

        return $this->folders[$key] ??= $this->folder(
            $this->folder(
                $this->folder(null, self::ROOT, 'Questions copied from the daily, CA daily and model exams. Kept up to date automatically.'),
                $section
            ),
            $month
        );
    }

    /** CA daily exam series: season > exam (the day / weekend / special exam of the series). */
    private function caFolder(object $row): int
    {
        $season = $row->season_id ? ($this->seasons()[$row->season_id] ?? 'Season ' . $row->season_id) : 'No season';
        $title = trim((string) ($row->exam_title ?: $row->examtitle)) ?: ('Exam ' . $row->exam_id);
        $key = 'ca|' . $row->season_id . '|' . $row->exam_id;

        return $this->folders[$key] ??= $this->folder(
            $this->folder(
                $this->folder($this->folder(null, self::ROOT, 'Questions copied from the daily, CA daily and model exams. Kept up to date automatically.'), 'CA Daily Exam Series'),
                $season
            ),
            $title
        );
    }

    private function seasons(): array
    {
        return $this->seasonNames ??= DB::table('kpsc_ca_seasons')->pluck('name', 'id')->all();
    }

    private function folder(?int $parentId, string $name, ?string $description = null): int
    {
        $cacheKey = $parentId . '>' . $name;

        return $this->folders[$cacheKey] ??= QuestionFolder::firstOrCreate(
            ['exam_id' => $this->exam->id, 'parent_id' => $parentId, 'name' => $name],
            ['description' => $description, 'position' => 0]
        )->id;
    }
}
