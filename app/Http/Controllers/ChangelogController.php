<?php

namespace App\Http\Controllers;

use App\Services\ChangelogService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ChangelogController extends Controller
{
    public function index(ChangelogService $service)
    {
        // Rewrite /docs/screenshots/<file>.png → /changelog/image/<file>.png
        $versions = array_map(function ($v) {
            $v['images'] = array_map(function ($img) {
                if (preg_match('~/docs/screenshots/([^/]+)$~', $img['src'], $m)) {
                    $img['src'] = route('changelog.image', ['file' => $m[1]]);
                }
                return $img;
            }, $v['images'] ?? []);
            return $v;
        }, $service->all());

        return response()->json([
            'app_version' => config('app.version', '1.3.0'),
            'versions' => $versions,
        ]);
    }

    public function image(string $file): BinaryFileResponse
    {
        abort_unless(preg_match('/^[a-z0-9_\-]+\.(png|jpg|jpeg|gif|webp)$/i', $file), 404);
        $path = base_path('docs/screenshots/' . $file);
        abort_unless(is_file($path), 404);
        return response()->file($path, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
