<?php

namespace App\Services\QuestionImport;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use ZipArchive;

/**
 * Sample files for every kind of import, built on the fly (nothing is stored).
 *
 *   SampleFiles::make('docx', 'multi')  -> path of a temporary file
 *   Word : english (table), text (numbered paragraphs), native (Malayalam), image, multi
 *   CSV / Excel : single, multi, image   (image needs the pictures zip: SampleFiles::make('zip', 'images'))
 */
class SampleFiles
{
    public const KEYS = [
        'docx' => ['english', 'text', 'native', 'image', 'multi'],
        'csv' => ['single', 'multi', 'image'],
        'xlsx' => ['single', 'multi', 'image'],
        'zip' => ['images'],
    ];

    private const HEAD = ['No', 'Question', 'Option A', 'Option B', 'Option C', 'Option D', 'Answer', 'Explanation'];

    public static function exists(string $ext, string $key): bool
    {
        return in_array($key, self::KEYS[$ext] ?? [], true);
    }

    public static function make(string $ext, string $key): string
    {
        $path = tempnam(sys_get_temp_dir(), 'qlsample');

        match ($ext) {
            'docx' => self::docx($path, $key),
            'csv', 'xlsx' => self::sheet($path, $ext, $key),
            'zip' => self::zipImages($path),
        };

        return $path;
    }

    // ----------------------------------------------------------------- data

    private static function english(): array
    {
        return [
            ['1', 'Who was the first Chief Minister of Kerala?', 'E. M. S. Namboodiripad', 'C. Achutha Menon', 'K. Karunakaran', 'A. K. Antony', 'A', 'E. M. S. Namboodiripad took office in 1957.'],
            ['2', 'Which river is the longest in Kerala?', 'Bharathappuzha', 'Periyar', 'Pamba', 'Chaliyar', 'B', 'Periyar is about 244 km long.'],
            ['3', 'The value of 2³ is', '6', '8', '9', '12', 'B', '2 × 2 × 2 = 8'],
        ];
    }

    private static function native(): array
    {
        return [
            ['1', 'കേരളത്തിലെ ആദ്യത്തെ മുഖ്യമന്ത്രി ആരായിരുന്നു?', 'ഇ.എം.എസ്. നമ്പൂതിരിപ്പാട്', 'സി. അച്യുതമേനോൻ', 'കെ. കരുണാകരൻ', 'എ.കെ. ആന്റണി', 'A', '1957-ൽ ഇ.എം.എസ്. അധികാരമേറ്റു.'],
            ['2', 'കേരളത്തിലെ ഏറ്റവും നീളം കൂടിയ നദി ഏത്?', 'ഭാരതപ്പുഴ', 'പെരിയാർ', 'പമ്പ', 'ചാലിയാർ', 'B', 'പെരിയാറിന് ഏകദേശം 244 കി.മീ. നീളമുണ്ട്.'],
        ];
    }

    /** Rows with the same No are one question: one row per language. */
    private static function multi(): array
    {
        return [
            ['1', 'English', 'Who was the first Chief Minister of Kerala?', 'E. M. S. Namboodiripad', 'C. Achutha Menon', 'K. Karunakaran', 'A. K. Antony', 'A', 'He took office in 1957.'],
            ['1', 'Malayalam', 'കേരളത്തിലെ ആദ്യത്തെ മുഖ്യമന്ത്രി ആരായിരുന്നു?', 'ഇ.എം.എസ്. നമ്പൂതിരിപ്പാട്', 'സി. അച്യുതമേനോൻ', 'കെ. കരുണാകരൻ', 'എ.കെ. ആന്റണി', 'A', '1957-ൽ അധികാരമേറ്റു.'],
            ['2', 'English', 'Which river is the longest in Kerala?', 'Bharathappuzha', 'Periyar', 'Pamba', 'Chaliyar', 'B', ''],
            ['2', 'Malayalam', 'കേരളത്തിലെ ഏറ്റവും നീളം കൂടിയ നദി ഏത്?', 'ഭാരതപ്പുഴ', 'പെരിയാർ', 'പമ്പ', 'ചാലിയാർ', 'B', ''],
        ];
    }

    /** Picture questions: [no, question, A, B, C, D, answer, explanation] where 'img:name' is a picture. */
    private static function image(): array
    {
        return [
            ['1', 'What is the area of the square shown in the figure?', 'img:square', '16', '20', '25', 'D', 'Side 5 → area 25.'],
            ['2', 'Which shape below is a triangle?', 'img:shape_a', 'img:shape_b', 'img:shape_c', 'img:shape_d', 'B', ''],
            ['3', 'Find x: 2x + 3 = 11', '4', '5', '6', '7', 'A', '2x = 8 → x = 4'],
        ];
    }

    // ---------------------------------------------------------- pictures (GD)

    /** A small drawn picture (PNG bytes). */
    private static function picture(string $name): string
    {
        $im = imagecreatetruecolor(240, 160);
        $white = imagecolorallocate($im, 255, 255, 255);
        $ink = imagecolorallocate($im, 30, 60, 120);
        $fill = imagecolorallocate($im, 190, 210, 245);
        imagefill($im, 0, 0, $white);

        switch ($name) {
            case 'square':
                imagefilledrectangle($im, 70, 30, 170, 130, $fill);
                imagerectangle($im, 70, 30, 170, 130, $ink);
                imagestring($im, 4, 112, 72, '5 cm', $ink);
                break;
            case 'shape_b':
                imagefilledpolygon($im, [120, 25, 50, 135, 190, 135], 3, $fill);
                imagepolygon($im, [120, 25, 50, 135, 190, 135], 3, $ink);
                break;
            case 'shape_a':
                imagefilledellipse($im, 120, 80, 110, 110, $fill);
                imageellipse($im, 120, 80, 110, 110, $ink);
                break;
            case 'shape_c':
                imagefilledrectangle($im, 60, 40, 180, 120, $fill);
                imagerectangle($im, 60, 40, 180, 120, $ink);
                break;
            default:
                $pts = [120, 20, 175, 60, 155, 125, 85, 125, 65, 60];
                imagefilledpolygon($im, $pts, 5, $fill);
                imagepolygon($im, $pts, 5, $ink);
        }

        ob_start();
        imagepng($im);
        $png = ob_get_clean();
        imagedestroy($im);

        return $png;
    }

    private static function zipImages(string $path): void
    {
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::OVERWRITE | ZipArchive::CREATE);
        foreach (['square', 'shape_a', 'shape_b', 'shape_c', 'shape_d'] as $n) {
            $zip->addFromString($n . '.png', self::picture($n));
        }
        $zip->close();
    }

    // ------------------------------------------------------------ CSV / Excel

    private static function sheet(string $path, string $ext, string $key): void
    {
        if ($key === 'multi') {
            $head = ['No', 'Language', 'Question', 'Option A', 'Option B', 'Option C', 'Option D', 'Answer', 'Explanation'];
            $rows = self::multi();
        } elseif ($key === 'image') {
            $head = ['No', 'Question', 'Question Image', 'Option A', 'Option B', 'Option C', 'Option D', 'Option A Image', 'Option B Image', 'Option C Image', 'Option D Image', 'Answer', 'Explanation'];
            $rows = [
                ['1', 'What is the area of the square shown in the figure?', 'square.png', '9', '16', '25', '30', '', '', '', '', 'C', 'Side 5 → area 25.'],
                ['2', 'Which shape below is a triangle?', '', '', '', '', '', 'shape_a.png', 'shape_b.png', 'shape_c.png', 'shape_d.png', 'B', ''],
                ['3', 'Find x: 2x + 3 = 11', '', '4', '5', '6', '7', '', '', '', '', 'A', '2x = 8 → x = 4'],
            ];
        } else {
            $head = self::HEAD;
            $rows = array_merge(self::english(), self::native());
            foreach ($rows as $i => &$r) {
                $r[0] = (string) ($i + 1);
            }
            unset($r);
        }

        $book = new Spreadsheet();
        $book->getActiveSheet()->fromArray(array_merge([$head], $rows), null, 'A1', true);
        $sheet = $book->getActiveSheet();
        foreach ($sheet->getColumnIterator() as $col) {
            $sheet->getColumnDimension($col->getColumnIndex())->setAutoSize(true);
        }

        if ($ext === 'csv') {
            $writer = new CsvWriter($book);
            $writer->setUseBom(true);   // Excel opens Malayalam correctly only with the BOM
        } else {
            $writer = new XlsxWriter($book);
        }
        $writer->save($path);
    }

    // ------------------------------------------------------------------ Word

    private static function docx(string $path, string $key): void
    {
        $media = [];   // name => bytes
        $body = '';

        $intro = static fn (string $t) => self::p($t, true);

        switch ($key) {
            case 'text':
                $body .= $intro('Question Library - sample (numbered paragraphs, English). Delete this line and the next one.');
                $body .= self::p('Each question: number, the question, options A-D one per line, then Answer: and (optional) Explanation:.');
                foreach (self::english() as [$no, $q, $a, $b, $c, $d, $ans, $exp]) {
                    $body .= self::p("$no. $q") . self::p("A) $a") . self::p("B) $b") . self::p("C) $c") . self::p("D) $d");
                    $body .= self::p("Answer: $ans") . self::p("Explanation: $exp");
                }
                break;

            case 'native':
                $body .= $intro('Question Library - sample (native language: Malayalam, one language per file).');
                $body .= self::table(self::HEAD, array_map(fn ($r) => array_map(fn ($v) => ['t' => $v], $r), self::native()));
                break;

            case 'multi':
                $body .= $intro('Question Library - sample (one question, many languages). Same No = same question; one row per language.');
                $body .= self::table(
                    ['No', 'Language', 'Question', 'Option A', 'Option B', 'Option C', 'Option D', 'Answer', 'Explanation'],
                    array_map(fn ($r) => array_map(fn ($v) => ['t' => $v], $r), self::multi())
                );
                break;

            case 'image':
                $body .= $intro('Question Library - sample (pictures inside the table: put a picture in the cell of the question, an option or the explanation).');
                $rows = [];
                foreach (self::image() as $r) {
                    $row = [];
                    foreach ($r as $i => $v) {
                        if (is_string($v) && str_starts_with($v, 'img:')) {
                            $n = substr($v, 4);
                            $media[$n] = self::picture($n);
                            $row[] = ['img' => $n];
                        } else {
                            $row[] = ['t' => $v];
                        }
                    }
                    // the figure of question 1 goes inside the question cell
                    if ($r[0] === '1') {
                        $media['square'] = self::picture('square');
                        $row[1] = ['t' => $r[1], 'img' => 'square'];
                        $row[2] = ['t' => '16'];
                        $row[3] = ['t' => '20'];
                        $row[4] = ['t' => '25'];
                        $row[5] = ['t' => '30'];
                        $row[6] = ['t' => 'C'];
                    }
                    $rows[] = $row;
                }
                $body .= self::table(self::HEAD, $rows);
                break;

            default:   // english
                $body .= $intro('Question Library - sample (English only, table). Keep the header row; delete these sample rows.');
                $body .= self::table(self::HEAD, array_map(fn ($r) => array_map(fn ($v) => ['t' => $v], $r), self::english()));
        }

        self::writeDocx($path, $body, $media);
    }

    private static function x(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function p(string $text, bool $bold = false): string
    {
        $rpr = $bold ? '<w:rPr><w:b/></w:rPr>' : '';

        return '<w:p><w:r>' . $rpr . '<w:t xml:space="preserve">' . self::x($text) . '</w:t></w:r></w:p>';
    }

    /** @param array $rows list of rows; a cell is ['t' => text, 'img' => picture name] */
    private static function table(array $head, array $rows): string
    {
        $cell = function (array $c, bool $bold = false): string {
            $xml = '<w:p>';
            if (($c['t'] ?? '') !== '' || !isset($c['img'])) {
                $xml .= '<w:r>' . ($bold ? '<w:rPr><w:b/></w:rPr>' : '') . '<w:t xml:space="preserve">' . self::x((string) ($c['t'] ?? '')) . '</w:t></w:r>';
            }
            if (isset($c['img'])) {
                $xml .= self::drawing($c['img']);
            }

            return '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr>' . $xml . '</w:p></w:tc>';
        };

        $xml = '<w:tbl><w:tblPr><w:tblStyle w:val="TableGrid"/><w:tblW w:w="0" w:type="auto"/><w:tblBorders>';
        foreach (['top', 'left', 'bottom', 'right', 'insideH', 'insideV'] as $side) {
            $xml .= '<w:' . $side . ' w:val="single" w:sz="4" w:space="0" w:color="888888"/>';
        }
        $xml .= '</w:tblBorders></w:tblPr>';
        $xml .= '<w:tr><w:trPr><w:tblHeader/></w:trPr>' . implode('', array_map(fn ($h) => $cell(['t' => $h], true), $head)) . '</w:tr>';
        foreach ($rows as $row) {
            $xml .= '<w:tr>' . implode('', array_map($cell, $row)) . '</w:tr>';
        }

        return $xml . '</w:tbl>' . self::p('');
    }

    /** The relationship id of a picture is "rId" + its position in the media list (set in writeDocx). */
    private static array $mediaOrder = [];

    private static function drawing(string $name): string
    {
        if (!in_array($name, self::$mediaOrder, true)) {
            self::$mediaOrder[] = $name;
        }
        $rid = 'rIdImg' . (array_search($name, self::$mediaOrder, true) + 1);
        $id = array_search($name, self::$mediaOrder, true) + 1;
        $cx = 1371600;   // 1.5 in x 1 in (EMU)
        $cy = 914400;

        return '<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="' . $cx . '" cy="' . $cy . '"/>'
            . '<wp:docPr id="' . $id . '" name="' . self::x($name) . '"/>'
            . '<a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic>'
            . '<pic:nvPicPr><pic:cNvPr id="' . $id . '" name="' . self::x($name) . '.png"/><pic:cNvPicPr/></pic:nvPicPr>'
            . '<pic:blipFill><a:blip r:embed="' . $rid . '"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
            . '<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>'
            . '</pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r>';
    }

    private static function writeDocx(string $path, string $body, array $media): void
    {
        $order = self::$mediaOrder;
        self::$mediaOrder = [];

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::OVERWRITE | ZipArchive::CREATE);

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/>'
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');

        $rels = '';
        foreach ($order as $i => $name) {
            $rels .= '<Relationship Id="rIdImg' . ($i + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/' . $name . '.png"/>';
            $zip->addFromString('word/media/' . $name . '.png', $media[$name] ?? self::picture($name));
        }
        $zip->addFromString('word/_rels/document.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $rels . '</Relationships>');

        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'
            . ' xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"'
            . ' xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'
            . ' xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><w:body>'
            . $body . '<w:sectPr><w:pgSz w:w="15840" w:h="12240" w:orient="landscape"/><w:pgMar w:top="1080" w:right="1080" w:bottom="1080" w:left="1080" w:header="720" w:footer="720" w:gutter="0"/></w:sectPr>'
            . '</w:body></w:document>');

        $zip->close();
    }
}
