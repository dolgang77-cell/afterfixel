<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class DocsController extends Controller
{
    private const DEFAULT_DOC = 'index.md';

    private const BLOCKED_TOP_LEVEL = [
        'backups',
    ];

    public function show(Request $request, ?string $path = null): Response|View
    {
        $doc = $this->resolveDoc($path);
        $markdown = file_get_contents($doc['absolute_path']);

        if ($request->boolean('raw')) {
            return response($markdown, 200, [
                'Content-Type' => 'text/markdown; charset=UTF-8',
            ]);
        }

        $content = Str::markdown(
            $this->rewriteMarkdownLinks($markdown, $doc['relative_path']),
            [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]
        );

        return view('docs.show', [
            'currentDoc' => $doc,
            'docsNavigation' => $this->navigation(),
            'renderedContent' => $content,
        ]);
    }

    private function resolveDoc(?string $path): array
    {
        $candidate = trim((string) ($path ?: self::DEFAULT_DOC), '/');

        if ($candidate === '' || Str::endsWith($candidate, '/')) {
            $candidate = trim($candidate, '/') . '/index.md';
        }

        if (!Str::contains($candidate, '.')) {
            $candidate .= '.md';
        }

        $absolutePath = realpath(base_path('docs/' . $candidate));
        $docsRoot = realpath(base_path('docs'));

        abort_unless($absolutePath && $docsRoot, 404);
        abort_unless(Str::startsWith($absolutePath, $docsRoot . DIRECTORY_SEPARATOR), 404);

        $relativePath = str_replace('\\', '/', ltrim(Str::after($absolutePath, $docsRoot), DIRECTORY_SEPARATOR));

        $firstSegment = Str::before($relativePath, '/');
        abort_if(in_array($firstSegment, self::BLOCKED_TOP_LEVEL, true), 404);
        abort_unless(Str::endsWith($relativePath, '.md'), 404);
        abort_unless(is_file($absolutePath), 404);

        return [
            'absolute_path' => $absolutePath,
            'relative_path' => $relativePath,
            'title' => $this->extractTitle($relativePath, file_get_contents($absolutePath)),
            'updated_at' => filemtime($absolutePath) ?: null,
        ];
    }

    private function navigation(): Collection
    {
        $docsRoot = realpath(base_path('docs'));

        if (!$docsRoot) {
            return collect();
        }

        $files = collect();

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($docsRoot)) as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $absolutePath = $file->getRealPath();
            $relativePath = str_replace('\\', '/', ltrim(Str::after($absolutePath, $docsRoot), DIRECTORY_SEPARATOR));
            $firstSegment = Str::before($relativePath, '/');

            if (in_array($firstSegment, self::BLOCKED_TOP_LEVEL, true)) {
                continue;
            }

            if (!Str::endsWith($relativePath, '.md')) {
                continue;
            }

            $files->push([
                'path' => $relativePath,
                'title' => $this->extractTitle($relativePath, file_get_contents($absolutePath)),
            ]);
        }

        return $files
            ->sortBy(function (array $doc) {
                return $doc['path'] === self::DEFAULT_DOC ? '0-index.md' : '1-' . $doc['path'];
            })
            ->values();
    }

    private function extractTitle(string $relativePath, string $markdown): string
    {
        foreach (preg_split('/\R/u', $markdown) as $line) {
            $trimmed = trim($line);

            if (Str::startsWith($trimmed, '# ')) {
                return trim(Str::after($trimmed, '# '));
            }
        }

        return Str::headline(pathinfo($relativePath, PATHINFO_FILENAME));
    }

    private function rewriteMarkdownLinks(string $markdown, string $currentRelativePath): string
    {
        return preg_replace_callback('/\[(?<label>[^\]]+)\]\((?<target>[^)]+)\)/', function (array $matches) use ($currentRelativePath) {
            $target = trim($matches['target']);

            if ($target === '' || Str::startsWith($target, ['http://', 'https://', 'mailto:', '#'])) {
                return $matches[0];
            }

            [$pathPart, $fragment] = array_pad(explode('#', $target, 2), 2, null);
            $normalized = $this->normalizeRelativeDocPath(dirname($currentRelativePath), $pathPart);

            if (!$normalized) {
                return $matches[0];
            }

            $url = route('docs.show', ['path' => $normalized]);

            if ($fragment) {
                $url .= '#' . $fragment;
            }

            return sprintf('[%s](%s)', $matches['label'], $url);
        }, $markdown) ?? $markdown;
    }

    private function normalizeRelativeDocPath(string $baseDir, string $target): ?string
    {
        $candidate = str_replace('\\', '/', $target);

        if ($candidate === '' || Str::startsWith($candidate, ['/'])) {
            return null;
        }

        $segments = [];
        $baseSegments = $baseDir === '.' ? [] : explode('/', trim($baseDir, '/'));

        foreach (array_merge($baseSegments, explode('/', $candidate)) as $segment) {
            $segment = trim($segment);

            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                if (empty($segments)) {
                    return null;
                }

                array_pop($segments);
                continue;
            }

            $segments[] = $segment;
        }

        $normalized = implode('/', $segments);

        if ($normalized === '' || !Str::endsWith($normalized, '.md')) {
            return null;
        }

        if (in_array(Str::before($normalized, '/'), self::BLOCKED_TOP_LEVEL, true)) {
            return null;
        }

        return $normalized;
    }
}
