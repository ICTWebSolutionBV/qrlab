<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Models\ShortLinkClick;
use App\Services\IpGeolocationService;
use App\Services\ShortLinkAnalyticsService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ShortLinkController extends Controller
{
    private const RESERVED = [
        'login', 'logout', 'register', 'password', 'forgot-password', 'reset-password',
        'dashboard', 'admin', 'profile', 'qr', 'links', 'invite', 'two-factor',
        'passkeys', 'changelog', 'feedback', 'api', 'r',
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $sort   = (string) $request->string('sort', 'latest');
        $status = (string) $request->string('status', '');

        $query = ShortLink::where('user_id', $request->user()->id);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('alias', 'like', $like)
                  ->orWhere('original_url', 'like', $like)
                  ->orWhere('title', 'like', $like);
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true)->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
        } elseif ($status === 'inactive') {
            $query->where(function ($q) {
                $q->where('is_active', false)->orWhere('expires_at', '<=', now());
            });
        }

        $query->orderBy(match ($sort) {
            'clicks'  => 'clicks_count',
            'oldest'  => 'created_at',
            default   => 'created_at',
        }, $sort === 'oldest' ? 'asc' : 'desc');

        $links = $query->paginate(20)->withQueryString()->through(fn ($l) => [
            'id'           => $l->id,
            'alias'        => $l->alias,
            'original_url' => $l->original_url,
            'title'        => $l->title,
            'is_active'    => $l->is_active,
            'expires_at'   => $l->expires_at?->toISOString(),
            'clicks_count' => $l->clicks_count,
            'created_at'   => $l->created_at->toISOString(),
            'is_expired'   => $l->isExpired(),
        ]);

        return Inertia::render('ShortLink/Index', [
            'links'   => $links,
            'appUrl'  => config('app.url'),
            'filters' => compact('search', 'sort', 'status'),
        ]);
    }

    public function create()
    {
        return Inertia::render('ShortLink/Create', [
            'appUrl' => config('app.url'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'original_url' => ['required', 'url', 'max:2048'],
            'alias'        => [
                'nullable', 'string', 'min:5', 'max:100',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::notIn(self::RESERVED),
                Rule::unique('short_links', 'alias')->whereNull('deleted_at'),
            ],
            'title'      => ['nullable', 'string', 'max:255'],
            'is_active'  => ['boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $alias = $data['alias'] ?? $this->generateAlias();

        ShortLink::create([
            'user_id'      => $request->user()->id,
            'alias'        => $alias,
            'original_url' => $data['original_url'],
            'title'        => $data['title'] ?? null,
            'is_active'    => $data['is_active'] ?? true,
            'expires_at'   => $data['expires_at'] ?? null,
        ]);

        return redirect()->route('links.index')
            ->with('success', 'Short link created successfully.');
    }

    public function edit(Request $request, ShortLink $link)
    {
        if ($link->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        return Inertia::render('ShortLink/Edit', [
            'link'   => $link,
            'appUrl' => config('app.url'),
        ]);
    }

    public function update(Request $request, ShortLink $link)
    {
        if ($link->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'original_url' => ['required', 'url', 'max:2048'],
            'alias'        => [
                'required', 'string', 'min:5', 'max:100',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::notIn(self::RESERVED),
                Rule::unique('short_links', 'alias')->ignore($link->id)->whereNull('deleted_at'),
            ],
            'title'      => ['nullable', 'string', 'max:255'],
            'is_active'  => ['boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $link->update($data);

        return redirect()->route('links.index')
            ->with('success', 'Short link updated.');
    }

    public function destroy(Request $request, ShortLink $link)
    {
        if ($link->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        $link->delete();

        return back()->with('success', 'Short link deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['string']])['ids'];

        ShortLink::whereIn('id', $ids)
            ->where('user_id', $request->user()->id)
            ->delete();

        return back()->with('success', count($ids) . ' link(s) deleted.');
    }

    public function analytics(Request $request, ShortLink $link, ShortLinkAnalyticsService $analytics)
    {
        if ($link->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        return Inertia::render('ShortLink/Analytics', [
            'link'      => $link,
            'analytics' => $analytics->forLink($link),
            'appUrl'    => config('app.url'),
        ]);
    }

    public function qrCode(Request $request, ShortLink $link)
    {
        if ($link->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        $shortUrl = config('app.url') . '/' . $link->alias;

        $result = (new Builder(
            writer: new PngWriter(),
            data: $shortUrl,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 400,
            margin: 10,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255),
        ))->build();

        return response($result->getString())
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $link->alias . '-qr.png"');
    }

    public function redirect(Request $request, string $alias, IpGeolocationService $geo)
    {
        $link = ShortLink::where('alias', $alias)->first();

        if (!$link || !$link->isAvailable()) {
            abort(404);
        }

        $ip = $request->ip();
        $geo = $geo->lookup($ip);

        ShortLinkClick::create([
            'short_link_id' => $link->id,
            'clicked_at'    => now(),
            'ip_address'    => $ip,
            'user_agent'    => $request->userAgent(),
            'referer'       => $request->header('referer'),
            'country'       => $geo['country'],
            'country_code'  => $geo['country_code'],
            'region'        => $geo['region'],
            'city'          => $geo['city'],
        ]);

        $link->increment('clicks_count');

        return redirect()->away($link->original_url, 301);
    }

    private function generateAlias(): string
    {
        do {
            $alias = Str::lower(Str::random(6));
        } while (ShortLink::where('alias', $alias)->exists());

        return $alias;
    }
}
