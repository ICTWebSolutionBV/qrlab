<?php

namespace App\Services;

class ChangelogService
{
    /**
     * Optional image attachments per version. Paths are resolved relative to
     * the `public/` directory. The frontend prepends `APP_URL` when rendering.
     *
     * @var array<string, array<int, array{src:string, alt:string}>>
     */
    public const IMAGES = [
        '1.2.0' => [
            ['src' => '/docs/screenshots/bulk-import-upload.png', 'alt' => 'Bulk import — choose type, download template, upload CSV'],
            ['src' => '/docs/screenshots/dashboard-batches.png',  'alt' => 'Dashboard with collapsible batches section'],
            ['src' => '/docs/screenshots/batch-detail.png',       'alt' => 'Batch detail page with ZIP export'],
        ],
    ];

    /**
     * Parse CHANGELOG.md into a structured array of versions → sections → items.
     *
     * @return array<int, array{version:string, date:?string, sections: array<int, array{title:string, items: array<int,string>}>, images: array<int, array{src:string, alt:string}>}>
     */
    public function all(): array
    {
        $path = base_path('CHANGELOG.md');
        if (!is_file($path)) return [];

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $versions = [];
        $vi = -1;      // current version index in $versions
        $si = -1;      // current section index within the current version

        foreach ($lines as $line) {
            // Version header: "## [1.2.0] — 2026-04-17"  or  "## [Unreleased]"
            if (preg_match('/^##\s+\[([^\]]+)\](?:\s*[—-]\s*(.+))?\s*$/u', $line, $m)) {
                $version = trim($m[1]);
                if (strtolower($version) === 'unreleased') {
                    $vi = -1;
                    $si = -1;
                    continue;
                }
                $versions[] = [
                    'version' => $version,
                    'date' => isset($m[2]) ? trim($m[2]) : null,
                    'sections' => [],
                    'images' => self::IMAGES[$version] ?? [],
                ];
                $vi = count($versions) - 1;
                $si = -1;
                continue;
            }

            if ($vi < 0) continue;

            // Section header: "### Added"
            if (preg_match('/^###\s+(.+)$/', $line, $m)) {
                $versions[$vi]['sections'][] = [
                    'title' => trim($m[1]),
                    'items' => [],
                ];
                $si = count($versions[$vi]['sections']) - 1;
                continue;
            }

            // List item: "- something"
            if (preg_match('/^-\s+(.+)$/', $line, $m) && $si >= 0) {
                $versions[$vi]['sections'][$si]['items'][] = trim($m[1]);
                continue;
            }

            // Continuation of a list item (indented)
            if (preg_match('/^\s{2,}(.+)$/', $line, $m) && $si >= 0) {
                $items = &$versions[$vi]['sections'][$si]['items'];
                if (!empty($items)) {
                    $items[count($items) - 1] .= ' ' . trim($m[1]);
                }
                unset($items);
                continue;
            }
        }

        return $versions;
    }
}
