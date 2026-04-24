<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class RichContentService
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li',
        'blockquote', 'a', 'img', 'h3', 'h4',
    ];

    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt'],
    ];

    public static function sanitize(?string $content): ?string
    {
        if ($content === null) {
            return null;
        }

        $content = trim($content);

        if ($content === '') {
            return null;
        }

        if (!self::containsHtml($content)) {
            return self::plainTextToHtml($content);
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            mb_convert_encoding('<div id="root">' . $content . '</div>', 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        /** @var DOMElement|null $root */
        $root = $dom->getElementById('root');

        if (!$root) {
            return self::plainTextToHtml(strip_tags($content));
        }

        self::sanitizeNode($root);

        return trim(self::innerHtml($root)) ?: null;
    }

    public static function render(?string $content): string
    {
        return self::sanitize($content) ?? '';
    }

    public static function editorValue(?string $content): string
    {
        return self::sanitize($content) ?? '<p><br></p>';
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child instanceof DOMElement) {
                self::sanitizeNode($child);

                if (!in_array($child->tagName, self::ALLOWED_TAGS, true)) {
                    self::unwrapElement($child);
                    continue;
                }

                self::sanitizeAttributes($child);
            }
        }
    }

    private static function sanitizeAttributes(DOMElement $element): void
    {
        $allowed = self::ALLOWED_ATTRIBUTES[$element->tagName] ?? [];
        $attributes = [];

        foreach ($element->attributes as $attribute) {
            $attributes[] = $attribute->name;
        }

        foreach ($attributes as $attribute) {
            if (!in_array($attribute, $allowed, true)) {
                $element->removeAttribute($attribute);
            }
        }

        if ($element->tagName === 'a') {
            $href = trim($element->getAttribute('href'));
            if (!self::isAllowedUrl($href)) {
                $element->removeAttribute('href');
            } else {
                $element->setAttribute('target', '_blank');
                $element->setAttribute('rel', 'noopener noreferrer');
            }
        }

        if ($element->tagName === 'img') {
            $src = trim($element->getAttribute('src'));
            if (!self::isAllowedUrl($src)) {
                $element->parentNode?->removeChild($element);
                return;
            }

            if (!$element->getAttribute('alt')) {
                $element->setAttribute('alt', 'MD content image');
            }
        }
    }

    private static function unwrapElement(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (!$parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private static function plainTextToHtml(string $content): string
    {
        $paragraphs = preg_split("/\n{2,}/", trim($content)) ?: [];

        $html = array_map(function (string $paragraph) {
            return '<p>' . nl2br(e(trim($paragraph))) . '</p>';
        }, array_filter($paragraphs, fn ($paragraph) => trim($paragraph) !== ''));

        return implode('', $html);
    }

    private static function containsHtml(string $content): bool
    {
        return $content !== strip_tags($content);
    }

    private static function innerHtml(DOMElement $element): string
    {
        $html = '';

        foreach ($element->childNodes as $child) {
            $html .= $element->ownerDocument->saveHTML($child);
        }

        return $html;
    }

    private static function isAllowedUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        return str_starts_with($url, '/')
            || str_starts_with($url, 'http://')
            || str_starts_with($url, 'https://');
    }
}
