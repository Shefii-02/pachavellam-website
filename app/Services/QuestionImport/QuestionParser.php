<?php

namespace App\Services\QuestionImport;

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Turns an uploaded Word / CSV / Excel file into ParsedFile rows. It does not touch the database.
 *
 * Word  : a table (header row: No, Question, Option A-D, Answer, Explanation - plus Language for "many languages")
 *         or, for single language files, plain numbered paragraphs:  1. question / A) .. D) options / Answer: B / Explanation: ...
 * CSV, Excel: the same columns as the table, one question per row.
 */
class QuestionParser
{
    public function parse(string $path, string $format, string $type): ParsedFile
    {
        $file = new ParsedFile();

        if ($format === 'docx') {
            $this->parseDocx($path, $type, $file);
        } else {
            $this->parseSheet($path, $file);
        }

        $max = (int) config('question_library.max_questions_per_file', 2000);
        if (count($file->rows) > $max) {
            throw new ImportException("This file has more than {$max} rows. Split it into smaller files.");
        }
        if (!$file->rows) {
            throw new ImportException($format === 'docx'
                ? 'No questions were found in the Word file. Use one of the sample files: a table with the header row (No, Question, Option A ... Answer, Explanation), or numbered questions with A) B) C) D) options and an "Answer:" line.'
                : 'No questions were found. The first row must be the header row (No, Question, Option A ... Answer, Explanation). Download the sample file to see it.');
        }

        return $file;
    }

    // ============================================================== Word

    private function parseDocx(string $path, string $type, ParsedFile $file): void
    {
        $doc = DocxDocument::open($path);
        $file->docx = $doc;

        // 1. tables: the table whose first rows contain a Question and an Answer heading
        $tableRows = 0;
        foreach ($doc->tables() as $table) {
            [$map, $headerAt] = $this->findHeader(array_map(fn ($row) => array_column($row, 'text'), $table));
            if ($map === null) {
                continue;
            }
            foreach ($table as $i => $cells) {
                if ($i <= $headerAt) {
                    continue;
                }
                $row = $this->rowFromCells($map, $cells, $i + 1, true);
                if ($row === 'header') {   // a header row repeated on the next page
                    continue;
                }
                if ($row) {
                    $file->rows[] = $row;
                    $tableRows++;
                }
            }
        }
        if ($tableRows) {
            return;
        }

        // 2. no table: numbered paragraphs (single language files only - the many languages file needs the Language column)
        if ($type === 'multi') {
            throw new ImportException('A "one question, many languages" Word file must be a table with a Language column. Download the sample file for this type.');
        }
        $file->rows = $this->parseParagraphs($doc->paragraphs());
    }

    /**
     * Numbered paragraphs:
     *   1. Question text            (Q1) / 1) / 1- also work; more lines are added to the question)
     *   A) option   B) option   C) option   D) option     (A. / (a) also work)
     *   Answer: B                  (Ans / Correct answer / ഉത്തരം)
     *   Explanation: ...           (Exp / Solution / വിശദീകരണം)
     * A picture on the question line belongs to the question, on an option line to that option, after Answer to the explanation.
     */
    private function parseParagraphs(array $paragraphs): array
    {
        $rows = [];
        $cur = null;
        $mode = null;   // 'question' | 'option' | 'explanation'
        $opt = 0;

        $flush = function () use (&$rows, &$cur) {
            if ($cur) {
                $rows[] = $cur;
            }
            $cur = null;
        };

        foreach ($paragraphs as $pi => $p) {
            $lines = preg_split('/\n/u', $p['text']);
            $images = $p['images'];

            foreach ($lines as $li => $line) {
                $line = trim($line);
                $imgs = $li === 0 ? $images : [];
                if ($line === '' && !$imgs) {
                    continue;
                }

                // a new question
                if (preg_match('/^\s*(?:Q(?:uestion)?\.?\s*)?(\d{1,4})\s*[\.\)\-:]\s*(.*)$/isu', $line, $m)) {
                    // inside a question, a line like "2) ..." is only a new question when the options are complete or the answer was given
                    if (!$cur || $opt >= 2 || $mode === 'explanation' || $cur['answer'] !== '') {
                        $flush();
                        $cur = $this->blankRow($pi + 1);
                        $cur['no'] = $m[1];
                        $cur['question'] = trim($m[2]);
                        $this->addImages($cur, 'question', $imgs);
                        $mode = 'question';
                        $opt = 0;
                        continue;
                    }
                }
                if (!$cur) {
                    continue;   // headings and instructions before the first question
                }

                // an option: A) B) C) D) in order
                if (preg_match('/^\s*\(?([A-Da-d])\s*[\)\.\:]\s*(.*)$/su', $line, $m) && (ord(strtoupper($m[1])) - 64) === $opt + 1 && $cur['answer'] === '' && $mode !== 'explanation') {
                    $opt++;
                    $cur['options'][$opt] = trim($m[2]);
                    $this->addImages($cur, 'option_' . $opt, $imgs);
                    $mode = 'option';
                    continue;
                }

                if (preg_match('/^\s*(?:answer|ans|correct\s*answer|correct|ഉത്തരം)\s*[:\-\.]?\s*(.+)$/isu', $line, $m)) {
                    $cur['answer'] = trim($m[1]);
                    $this->addImages($cur, 'explanation', $imgs);
                    $mode = 'answer';
                    continue;
                }

                if (preg_match('/^\s*(?:explanation|exp|solution|വിശദീകരണം)\s*[:\-\.]?\s*(.*)$/isu', $line, $m)) {
                    $cur['explanation'] = trim($m[1]);
                    $this->addImages($cur, 'explanation', $imgs);
                    $mode = 'explanation';
                    continue;
                }

                // a continuation of whatever came before
                $this->addImages($cur, $mode === 'option' ? 'option_' . $opt : ($mode === 'question' ? 'question' : 'explanation'), $imgs);
                if ($mode === 'explanation' || $mode === 'answer') {
                    $cur['explanation'] = trim($cur['explanation'] . "\n" . $line);
                } elseif ($mode === 'option') {
                    $cur['options'][$opt] = trim($cur['options'][$opt] . ' ' . $line);
                } else {
                    $cur['question'] = trim($cur['question'] . "\n" . $line);
                }
            }
        }
        $flush();

        return $rows;
    }

    private function blankRow($rowNo): array
    {
        return [
            'row' => $rowNo, 'no' => null, 'language' => null, 'question' => '', 'options' => [1 => '', 2 => '', 3 => '', 4 => ''],
            'answer' => '', 'explanation' => '', 'difficulty' => null,
            'images' => ['question' => [], 'option_1' => [], 'option_2' => [], 'option_3' => [], 'option_4' => [], 'explanation' => []],
        ];
    }

    private function addImages(array &$row, string $slot, array $paths): void
    {
        foreach ($paths as $p) {
            $row['images'][$slot][] = ['kind' => 'docx', 'path' => $p];
        }
    }

    // =========================================================== CSV / Excel

    private function parseSheet(string $path, ParsedFile $file): void
    {
        try {
            $reader = IOFactory::createReaderForFile($path);
            if ($reader instanceof Csv) {
                $reader->setInputEncoding(Csv::GUESS_ENCODING);   // Excel often saves "CSV" as UTF-16 / Windows text
            }
            $reader->setReadDataOnly(true);
            $data = $reader->load($path)->getActiveSheet()->toArray(null, false, false, false);
        } catch (\Throwable $e) {
            throw new ImportException('The file could not be read as CSV / Excel: ' . $e->getMessage());
        }

        $grid = array_map(fn ($r) => array_map([$this, 'cellToString'], $r), $data);
        [$map, $headerAt] = $this->findHeader($grid);

        if ($map === null) {
            throw new ImportException('The header row was not found. The first row must contain the column names (No, Question, Option A, Option B, Option C, Option D, Answer, Explanation). Download the sample file.');
        }

        foreach ($grid as $i => $cells) {
            if ($i <= $headerAt) {
                continue;
            }
            $row = $this->rowFromCells($map, $cells, $i + 1, false);
            if ($row && $row !== 'header') {
                $file->rows[] = $row;
            }
        }
    }

    private function cellToString($v): string
    {
        if (is_float($v) && floor($v) == $v) {
            return (string) (int) $v;   // Excel stores 2 as 2.0
        }

        return trim(str_replace("\r", '', (string) $v));
    }

    // ============================================================ shared

    /**
     * Finds the header row among the first rows. Returns [column index => key, row index] or [null, null].
     */
    private function findHeader(array $rows): array
    {
        $aliases = $this->aliasLookup();

        foreach (array_slice($rows, 0, 6, true) as $i => $cells) {
            $map = [];
            foreach ($cells as $col => $text) {
                $key = $aliases[$this->normalize((string) $text)] ?? null;
                if ($key && !in_array($key, $map, true)) {
                    $map[$col] = $key;
                }
            }
            $options = count(array_intersect($map, ['option_1', 'option_2', 'option_3', 'option_4']));
            if (in_array('question', $map, true) && in_array('answer', $map, true) && $options >= 2) {
                return [$map, $i];
            }
        }

        return [null, null];
    }

    private function aliasLookup(): array
    {
        static $lookup = null;
        if ($lookup === null) {
            $lookup = [];
            foreach (config('question_library.headers') as $key => $names) {
                foreach ($names as $name) {
                    $lookup[$this->normalize($name)] = $key;
                }
            }
        }

        return $lookup;
    }

    private function normalize(string $s): string
    {
        return mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', '', $s));
    }

    /**
     * One table / sheet row -> a canonical row, null for an empty row, 'header' for a repeated header.
     * $cells: Word cells ['text', 'images'] when $word, plain strings otherwise.
     */
    private function rowFromCells(array $map, array $cells, int $rowNo, bool $word)
    {
        $get = fn ($col) => $word ? ($cells[$col]['text'] ?? '') : (string) ($cells[$col] ?? '');
        $imgs = fn ($col) => $word ? ($cells[$col]['images'] ?? []) : [];

        $row = $this->blankRow($rowNo);
        $filled = false;
        $seenHeader = 0;
        $questionIsHeading = false;
        $aliases = $this->aliasLookup();

        foreach ($map as $col => $key) {
            $text = trim($get($col));
            if ($text !== '' && ($aliases[$this->normalize($text)] ?? null) === $key) {
                $seenHeader++;
                if ($key === 'question') {
                    $questionIsHeading = true;   // options named "a", "b" are real answers; the word "Question" in the question column is not
                }
            }
            $images = $imgs($col);
            if ($text !== '' || $images) {
                $filled = true;
            }

            switch ($key) {
                case 'no': $row['no'] = $text !== '' ? $text : null; break;
                case 'language': $row['language'] = $text !== '' ? $text : null; break;
                case 'question': $row['question'] = $text; $this->addImages($row, 'question', $images); break;
                case 'option_1': case 'option_2': case 'option_3': case 'option_4':
                    $n = (int) substr($key, -1);
                    $row['options'][$n] = $text;
                    $this->addImages($row, 'option_' . $n, $images);
                    break;
                case 'answer': $row['answer'] = $text; break;
                case 'explanation': $row['explanation'] = $text; $this->addImages($row, 'explanation', $images); break;
                case 'difficulty': $row['difficulty'] = $text !== '' ? $text : null; break;
                // picture file names (CSV / Excel): the picture is looked up in the uploaded zip
                case 'question_image': case 'explanation_image':
                case 'option_1_image': case 'option_2_image': case 'option_3_image': case 'option_4_image':
                    if ($text !== '') {
                        $slot = str_replace('_image', '', $key);
                        $row['images'][$slot][] = ['kind' => 'zip', 'name' => $text];
                    }
                    break;
            }
        }

        if (!$filled) {
            return null;
        }
        if ($seenHeader >= 3 && $questionIsHeading) {
            return 'header';
        }

        return $row;
    }
}
