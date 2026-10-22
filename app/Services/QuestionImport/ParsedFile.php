<?php

namespace App\Services\QuestionImport;

use ZipArchive;

/**
 * What was read from an uploaded file, in one shape whatever the format:
 *
 *   rows[] = [
 *     'row'         => source row / paragraph number (for messages)
 *     'no'          => question number written in the file, or null
 *     'language'    => text of the Language column, or null
 *     'question'    => string
 *     'options'     => [1 => string, 2 => ..., 3 => ..., 4 => ...]
 *     'answer'      => string as written (A, 2, option text ...)
 *     'explanation' => string
 *     'difficulty'  => string|null
 *     'images'      => ['question' => [ref], 'option_1' => [ref], ..., 'explanation' => [ref]]
 *   ]
 *   ref = ['kind' => 'docx', 'path' => 'word/media/image1.png']  or  ['kind' => 'zip', 'name' => 'q1.png']
 */
class ParsedFile
{
    public array $rows = [];
    public array $problems = [];   // [{row, message}] found while reading, before any question is built
    public ?DocxDocument $docx = null;
    public ?ZipArchive $imageZip = null;
    private array $zipIndex = [];

    public function problem($row, string $message): void
    {
        $this->problems[] = ['row' => $row, 'message' => $message];
    }

    public function attachImageZip(string $zipPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new ImportException('The pictures zip could not be opened. Make sure it is a normal .zip file.');
        }
        $this->imageZip = $zip;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!str_ends_with($name, '/') && !str_starts_with(basename($name), '.') && !str_contains($name, '__MACOSX')) {
                $this->zipIndex[mb_strtolower(basename($name))] = $name;   // picture names are matched without folders and ignoring case
            }
        }
    }

    /** Picture bytes for a reference, or null when it is not in the file / zip. */
    public function imageBytes(array $ref): ?string
    {
        if ($ref['kind'] === 'docx') {
            return $this->docx?->imageBytes($ref['path']);
        }

        if ($ref['kind'] === 'zip' && $this->imageZip) {
            $inside = $this->zipIndex[mb_strtolower(basename((string) $ref['name']))] ?? null;
            $bytes = $inside ? $this->imageZip->getFromName($inside) : false;

            return $bytes === false ? null : $bytes;
        }

        return null;
    }

    public function close(): void
    {
        if ($this->imageZip) {
            @$this->imageZip->close();
        }
    }
}
