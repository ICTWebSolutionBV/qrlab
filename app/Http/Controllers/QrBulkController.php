<?php

namespace App\Http\Controllers;

use App\Models\QrCodeBatch;
use App\Services\QrBulkImportService;
use App\Services\QrGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use ZipArchive;

class QrBulkController extends Controller
{
    public function __construct(
        private QrBulkImportService $importer,
        private QrGeneratorService $generator,
    ) {}

    // ---- Import flow ----

    public function create(Request $request)
    {
        $type = $request->query('type', 'url');
        if (!$this->importer->hasType($type)) $type = 'url';

        $preview = $request->session()->get("bulk_preview.{$type}");

        return Inertia::render('QrCode/BulkImport', [
            'type' => $type,
            'schemas' => QrBulkImportService::SCHEMAS,
            'preview' => $preview,
        ]);
    }

    public function template(string $type)
    {
        abort_unless($this->importer->hasType($type), 404);
        $csv = $this->importer->template($type);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="qrlab-' . $type . '-template.csv"');
    }

    public function preview(Request $request, string $type)
    {
        abort_unless($this->importer->hasType($type), 404);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $request->file('file');
        $result = $this->importer->parse($type, $file);

        // Stash parsed rows in session for confirm step (keyed by type so tabs don't collide)
        $request->session()->put("bulk_preview.{$type}", [
            'file_name' => $file->getClientOriginalName(),
            'total' => $result['total'],
            'rows' => array_slice($result['rows'], 0, 5), // preview only
            'all_rows' => $result['rows'],                // full set for import
            'errors' => $result['errors'],
            'valid' => empty($result['errors']),
        ]);

        return redirect()->route('qr.bulk.create', ['type' => $type]);
    }

    public function discard(Request $request, string $type)
    {
        $request->session()->forget("bulk_preview.{$type}");
        return redirect()->route('qr.bulk.create', ['type' => $type]);
    }

    public function store(Request $request, string $type)
    {
        abort_unless($this->importer->hasType($type), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $preview = $request->session()->get("bulk_preview.{$type}");
        if (!$preview || !$preview['valid']) {
            return back()->with('error', 'No valid preview to import. Please upload a file first.');
        }

        $batch = $this->importer->import($request->user(), $type, $data['name'], $preview['all_rows']);
        $request->session()->forget("bulk_preview.{$type}");

        return redirect()->route('qr.bulk.show', $batch)
            ->with('success', "Imported {$preview['total']} QR codes into batch \"{$batch->name}\".");
    }

    // ---- Batch detail ----

    public function show(Request $request, QrCodeBatch $batch)
    {
        $this->authorize($batch, $request);

        $batch->load(['qrCodes' => fn ($q) => $q->withCount('scans')->orderBy('created_at')]);

        return Inertia::render('QrCode/Batch', [
            'batch' => [
                'id' => $batch->id,
                'name' => $batch->name,
                'type' => $batch->type,
                'created_at' => optional($batch->created_at)->toISOString(),
                'qr_codes' => $batch->qrCodes->map(fn ($qr) => [
                    'id' => $qr->id,
                    'name' => $qr->name,
                    'type' => $qr->type,
                    'url' => $qr->url,
                    'ssid' => $qr->ssid,
                    'tracking_enabled' => (bool) $qr->tracking_enabled,
                    'short_code' => $qr->short_code,
                    'scans_count' => (int) $qr->scans_count,
                    'created_at' => optional($qr->created_at)->toISOString(),
                ])->all(),
            ],
        ]);
    }

    public function destroy(Request $request, QrCodeBatch $batch)
    {
        $this->authorize($batch, $request);

        // QR codes in the batch are cascaded via soft delete
        foreach ($batch->qrCodes as $qr) {
            $qr->delete();
        }
        $batch->delete();

        return redirect()->route('dashboard')->with('success', "Batch \"{$batch->name}\" deleted.");
    }

    public function exportZip(Request $request, QrCodeBatch $batch, string $format = 'png')
    {
        $this->authorize($batch, $request);

        if (!in_array($format, ['png', 'svg', 'eps'], true)) abort(400, 'Unsupported format.');

        $batch->load('qrCodes');
        if ($batch->qrCodes->isEmpty()) {
            return back()->with('error', 'Batch is empty.');
        }

        $tmpZip = tempnam(sys_get_temp_dir(), 'qrlab_batch_') . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($tmpZip, ZipArchive::CREATE) !== true) {
            abort(500, 'Could not create ZIP archive.');
        }

        $usedNames = [];
        foreach ($batch->qrCodes as $qr) {
            $result = $this->generator->generate($qr, $format);
            $base = Str::slug($qr->name ?: 'qr-code', '_');
            if ($base === '') $base = 'qr-code';
            $candidate = $base . '.' . $format;
            $i = 2;
            while (isset($usedNames[$candidate])) {
                $candidate = $base . '-' . $i . '.' . $format;
                $i++;
            }
            $usedNames[$candidate] = true;
            $zip->addFromString($candidate, $result->getString());
        }
        $zip->close();

        $zipName = Str::slug($batch->name ?: 'batch', '_') . '-' . $format . '.zip';

        return response()->download($tmpZip, $zipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    private function authorize(QrCodeBatch $batch, Request $request): void
    {
        if ($batch->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }
    }
}
