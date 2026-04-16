<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Services\QrGeneratorService;
use Illuminate\Http\Request;

class QrExportController extends Controller
{
    public function __construct(
        private QrGeneratorService $generator,
    ) {}

    public function download(Request $request, QrCode $qrCode, string $format)
    {
        if ($qrCode->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        if (!in_array($format, ['png', 'svg', 'eps'])) {
            abort(400, 'Unsupported format.');
        }

        $result = $this->generator->generate($qrCode, $format);
        $mimeType = match ($format) {
            'svg' => 'image/svg+xml',
            'eps' => 'application/postscript',
            default => 'image/png',
        };

        $filename = str_replace(' ', '_', $qrCode->name) . '.' . $format;

        return response($result->getString())
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
