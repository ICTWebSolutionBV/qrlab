<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $type = (string) $request->string('type');
        $tracking = (string) $request->string('tracking');
        $sort = (string) $request->string('sort', 'latest');

        $query = $request->user()->qrCodes()->withCount('scans')->whereNull('batch_id');

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('url', 'like', $like)
                    ->orWhere('ssid', 'like', $like);
            });
        }

        if (in_array($type, ['url', 'wifi', 'phone', 'vcard', 'email'], true)) {
            $query->where('type', $type);
        }

        if ($tracking === 'on') {
            $query->where('tracking_enabled', true);
        } elseif ($tracking === 'off') {
            $query->where('tracking_enabled', false);
        }

        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'name':
                $query->orderBy('name');
                break;
            case 'scans':
                $query->orderByDesc('scans_count');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $qrCodes = $query->paginate(12)->withQueryString();

        $batches = $request->user()->qrCodeBatches()
            ->withCount('qrCodes')
            ->latest()
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'type' => $b->type,
                'qr_codes_count' => (int) $b->qr_codes_count,
                'created_at' => optional($b->created_at)->toISOString(),
            ]);

        return Inertia::render('Dashboard', [
            'qrCodes' => $qrCodes,
            'batches' => $batches,
            'appUrl' => config('app.url'),
            'filters' => [
                'search' => $search,
                'type' => $type,
                'tracking' => $tracking,
                'sort' => $sort,
            ],
        ]);
    }
}
