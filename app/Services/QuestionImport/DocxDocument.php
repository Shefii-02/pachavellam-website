<?php

namespace App\Services\QuestionImport;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use ZipArchive;

/**
 * Reads a Word (.docx) file: its tables and paragraphs as plain text, and the pictures inside them.
 *
 * A .docx is a zip of XML files. Text comes from <w:t>, pictures from <a:blip r:embed> (normal pictures) or
 * <v:imagedata r:id> (older ones), and the picture bytes from word/media/*.
 * Superscripts / subscripts become Unicode (x², H₂O) and Word equations are written out as plain text (√(x)/(2)).
 * Bold, colours and fonts are not kept: questions are stored as plain text.
 */
class DocxDocument
{
    private const NS_W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
    private const NS_R = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private const NS_A = 'http://schemas.openxmlformats.org/drawingml/2006/main';
    private const NS_M = 'http://schemas.openxmlformats.org/officeDocument/2006/math';
    private const NS_V = 'urn:schemas-microsoft-com:vml';

    private ZipArchive $zip;
    private DOMXPath $xp;
    private DOMDocument $dom;
    /** relationship id => path inside the zip (only pictures stored in the file) */
    private array $media = [];

    private const SUP = ['0' => '⁰', '1' => '¹', '2' => '²', '3' => '³', '4' => '⁴', '5' => '⁵', '6' => '⁶', '7' => '⁷', '8' => '⁸', '9' => '⁹', '+' => '⁺', '-' => '⁻', '=' => '⁼', '(' => '⁽', ')' => '⁾', 'n' => 'ⁿ', 'i' => 'ⁱ'];
    private const SUB = ['0' => '₀', '1' => '₁', '2' => '₂', '3' => '₃', '4' => '₄', '5' => '₅', '6' => '₆', '7' => '₇', '8' => '₈', '9' => '₉', '+' => '₊', '-' => '₋', '=' => '₌', '(' => '₍', ')' => '₎'];

    public static function open(string $path): self
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true || $zip->locateName('word/document.xml') === false) {
            throw new ImportException('This is not a Word (.docx) file. Save it as "Word Document (.docx)" and try again. Old .doc files are not supported.');
        }

        $doc = new self();
        $doc->zip = $zip;
        $doc->dom = new DOMDocument();

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false || !@$doc->dom->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT | LIBXML_PARSEHUGE)) {
            throw new ImportException('The Word file is damaged and could not be read.');
        }

        $doc->xp = new DOMXPath($doc->dom);
        foreach (['w' => self::NS_W, 'r' => self::NS_R, 'a' => self::NS_A, 'm' => self::NS_M, 'v' => self::NS_V] as $p => $ns) {
            $doc->xp->registerNamespace($p, $ns);
        }
        $doc->loadRelationships();

        return $doc;
    }

    public function __destruct()
    {
        if (isset($this->zip)) {
            @$this->zip->close();
        }
    }

    private function loadRelationships(): void
    {
        $xml = $this->zip->getFromName('word/_rels/document.xml.rels');
        if ($xml === false) {
            return;
        }
        $rels = new DOMDocument();
        if (!@$rels->loadXML($xml, LIBXML_NONET)) {
            return;
        }
        foreach ($rels->getElementsByTagName('Relationship') as $rel) {
            if ($rel->getAttribute('TargetMode') === 'External') {
                continue;   // linked pictures live on someone's disk, not in the file
            }
            $type = $rel->getAttribute('Type');
            if (!str_ends_with($type, '/image')) {
                continue;
            }
            $target = ltrim($rel->getAttribute('Target'), '/');
            $this->media[$rel->getAttribute('Id')] = str_starts_with($target, 'word/') ? $target : 'word/' . $target;
        }
    }

    /** Bytes of a picture by the path returned in an image reference. */
    public function imageBytes(string $path): ?string
    {
        $bytes = $this->zip->getFromName($path);

        return $bytes === false ? null : $bytes;
    }

    // ------------------------------------------------------------------ tables

    /**
     * Every top level table: a list of rows, each a list of cells ['text' => string, 'images' => [path, ...]].
     */
    public function tables(): array
    {
        $tables = [];
        foreach ($this->xp->query('/w:document/w:body/w:tbl') as $tbl) {
            $rows = [];
            foreach ($this->xp->query('./w:tr', $tbl) as $tr) {
                $cells = [];
                foreach ($this->xp->query('./w:tc', $tr) as $tc) {
                    $cells[] = $this->cell($tc);
                }
                $rows[] = $cells;
            }
            $tables[] = $rows;
        }

        return $tables;
    }

    private function cell(DOMElement $tc): array
    {
        $lines = [];
        $images = [];
        foreach ($this->xp->query('./w:p', $tc) as $p) {
            [$text, $imgs] = $this->paragraph($p);
            $lines[] = $text;
            $images = array_merge($images, $imgs);
        }

        return ['text' => $this->tidy(implode("\n", $lines)), 'images' => array_values(array_unique($images))];
    }

    // -------------------------------------------------------------- paragraphs

    /** The paragraphs that are not inside a table, in order: ['text' => string, 'images' => [path, ...]]. */
    public function paragraphs(): array
    {
        $out = [];
        foreach ($this->xp->query('/w:document/w:body/w:p') as $p) {
            [$text, $images] = $this->paragraph($p);
            $out[] = ['text' => $text, 'images' => array_values(array_unique($images))];
        }

        return $out;
    }

    /** @return array{0: string, 1: array} text and the pictures of one paragraph */
    private function paragraph(DOMElement $p): array
    {
        $text = '';
        $images = [];
        $this->walk($p, $text, $images);

        return [$text, $images];
    }

    private function walk(DOMNode $node, string &$text, array &$images): void
    {
        foreach ($node->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            $name = $child->localName;

            switch ($name) {
                case 'r':
                    $this->run($child, $text, $images);
                    break;
                case 'oMath':
                case 'oMathPara':
                    $text .= ' ' . trim($this->math($child)) . ' ';
                    break;
                case 'Fallback':          // mc:AlternateContent keeps a second copy of everything: skip it
                case 'pPr':
                case 'rPr':
                case 'txbxContent':
                    break;
                default:
                    $this->walk($child, $text, $images);   // hyperlinks, smart tags, content controls, insertions ...
            }
        }
    }

    private function run(DOMElement $run, string &$text, array &$images): void
    {
        $align = null;
        foreach ($run->getElementsByTagNameNS(self::NS_W, 'vertAlign') as $va) {
            $align = $va->getAttributeNS(self::NS_W, 'val');
            break;
        }

        foreach ($run->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }
            switch ($child->localName) {
                case 't':
                    $text .= $this->script($child->textContent, $align);
                    break;
                case 'tab':
                    $text .= ' ';
                    break;
                case 'br':
                case 'cr':
                    $text .= "\n";
                    break;
                case 'drawing':
                    foreach ($child->getElementsByTagNameNS(self::NS_A, 'blip') as $blip) {
                        $this->addImage($blip->getAttributeNS(self::NS_R, 'embed'), $images);
                    }
                    break;
                case 'pict':
                case 'object':
                    foreach ($child->getElementsByTagNameNS(self::NS_V, 'imagedata') as $img) {
                        $this->addImage($img->getAttributeNS(self::NS_R, 'id'), $images);
                    }
                    break;
            }
        }
    }

    private function addImage(string $rid, array &$images): void
    {
        if ($rid !== '' && isset($this->media[$rid])) {
            $images[] = $this->media[$rid];
        }
    }

    /** x² / H₂O: superscript and subscript runs become Unicode where a character exists, ^(...) / _(...) otherwise. */
    private function script(string $text, ?string $align): string
    {
        if ($text === '' || !in_array($align, ['superscript', 'subscript'], true)) {
            return $text;
        }
        $map = $align === 'superscript' ? self::SUP : self::SUB;
        $out = '';
        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
            if (!isset($map[$ch])) {
                return ($align === 'superscript' ? '^(' : '_(') . $text . ')';
            }
            $out .= $map[$ch];
        }

        return $out;
    }

    // ------------------------------------------------------------ equations

    /** A Word equation written as plain text: fractions (a)/(b), roots √(x), powers x^(2), brackets. */
    private function math(DOMNode $node): string
    {
        $out = '';
        foreach ($node->childNodes as $c) {
            if (!$c instanceof DOMElement) {
                continue;
            }
            switch ($c->localName) {
                case 't':
                    $out .= $c->textContent;
                    break;
                case 'f':   // fraction
                    $out .= '(' . $this->mathPart($c, 'num') . ')/(' . $this->mathPart($c, 'den') . ')';
                    break;
                case 'sSup':
                    $out .= $this->mathPart($c, 'e') . $this->script($this->mathPart($c, 'sup'), 'superscript');
                    break;
                case 'sSub':
                    $out .= $this->mathPart($c, 'e') . $this->script($this->mathPart($c, 'sub'), 'subscript');
                    break;
                case 'rad':
                    $deg = trim($this->mathPart($c, 'deg'));
                    $out .= ($deg !== '' ? $deg : '') . '√(' . $this->mathPart($c, 'e') . ')';
                    break;
                case 'd':   // brackets
                    $out .= '(' . $this->math($c) . ')';
                    break;
                case 'ctrlPr':
                case 'fPr':
                case 'dPr':
                case 'radPr':
                    break;
                default:
                    $out .= $this->math($c);
            }
        }

        return $out;
    }

    private function mathPart(DOMElement $parent, string $tag): string
    {
        foreach ($parent->childNodes as $c) {
            if ($c instanceof DOMElement && $c->localName === $tag) {
                return $this->math($c);
            }
        }

        return '';
    }

    private function tidy(string $text): string
    {
        $text = str_replace(["\u{00A0}", "\r"], [' ', ''], $text);
        $text = preg_replace("/[ \t]+\n/u", "\n", $text);
        $text = preg_replace("/\n{3,}/u", "\n\n", $text);

        return trim($text);
    }
}
