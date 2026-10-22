<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Keeps only simple formatting from text typed in the editor (bold, lists, sub / superscript, tables ...).
 * No attributes, scripts, styles, links or pictures survive, so the result is safe to print unescaped.
 */
class SafeHtml
{
    private const ALLOWED = [
        'p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'sub', 'sup', 'ul', 'ol', 'li', 'blockquote',
        'h3', 'h4', 'h5', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'span', 'div',
    ];
    private const DROP_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'noscript', 'template'];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div id="root">' . $html . '</div>', LIBXML_NONET | LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = $dom->getElementById('root');
        if (!$root) {
            return e(strip_tags($html));
        }
        self::walk($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim(preg_replace('/(<p>(&nbsp;|\s)*<\/p>)+$/u', '', trim($out)));
    }

    /** Plain text (for lists, search and the duplicate check). */
    public static function text(?string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(preg_replace('/<(br|\/p|\/li|\/tr|\/div)[^>]*>/i', ' ', (string) $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (!$child instanceof DOMElement) {
                continue;   // text and comments stay (comments are harmless but removed below)
            }
            $name = strtolower($child->tagName);

            if (in_array($name, self::DROP_WITH_CONTENT, true)) {
                $node->removeChild($child);
                continue;
            }

            self::walk($child);

            if (!in_array($name, self::ALLOWED, true)) {
                // unknown tag (a, font, img ...): keep its text, drop the tag
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attr) {
                $child->removeAttributeNode($attr);
            }
        }
        foreach (iterator_to_array($node->childNodes) as $c) {
            if ($c->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($c);
            }
        }
    }
}
