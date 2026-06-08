<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShortLink;
use App\Models\ShortLinkClick;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ShortLinkStatsController extends Controller
{
    public function index(Request $request)
    {
        $now    = CarbonImmutable::now();
        $since30 = $now->startOfDay()->subDays(29);

        $totals = [
            'total_links'    => ShortLink::count(),
            'active_links'   => ShortLink::where('is_active', true)->count(),
            'clicks_all_time'=> ShortLinkClick::count(),
            'clicks_today'   => ShortLinkClick::where('clicked_at', '>=', $now->startOfDay())->count(),
            'clicks_7d'      => ShortLinkClick::where('clicked_at', '>=', $now->startOfDay()->subDays(6))->count(),
            'clicks_30d'     => ShortLinkClick::where('clicked_at', '>=', $since30)->count(),
        ];

        $dailyRaw = ShortLinkClick::where('clicked_at', '>=', $since30)
            ->select(DB::raw('DATE(clicked_at) as day'), DB::raw('COUNT(*) as count'))
            ->groupBy('day')
            ->pluck('count', 'day')
            ->all();

        $daily = [];
        for ($i = 0; $i < 30; $i++) {
            $day = $since30->addDays($i)->toDateString();
            $daily[] = ['day' => $day, 'count' => (int) ($dailyRaw[$day] ?? 0)];
        }

        $sort      = (string) $request->string('sort', 'clicks');
        $direction = $request->string('dir', 'desc')->toString() === 'asc' ? 'asc' : 'desc';
        $search    = trim((string) $request->string('search'));

        $topLinks = ShortLink::query()
            ->leftJoin('users', 'users.id', '=', 'short_links.user_id')
            ->select([
                'short_links.id',
                'short_links.alias',
                'short_links.original_url',
                'short_links.title',
                'short_links.clicks_count',
                'short_links.is_active',
                'short_links.created_at',
                'users.name as owner_name',
                'users.email as owner_email',
            ])
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where('short_links.alias', 'like', $like)
                  ->orWhere('short_links.original_url', 'like', $like)
                  ->orWhere('users.name', 'like', $like)
                  ->orWhere('users.email', 'like', $like);
            }))
            ->orderBy(match ($sort) {
                'alias'   => 'short_links.alias',
                'created' => 'short_links.created_at',
                'owner'   => 'users.name',
                default   => 'short_links.clicks_count',
            }, $direction)
            ->paginate(25)
            ->withQueryString()
            ->through(fn ($l) => [
                'id'           => $l->id,
                'alias'        => $l->alias,
                'original_url' => $l->original_url,
                'title'        => $l->title,
                'clicks_count' => (int) $l->clicks_count,
                'is_active'    => (bool) $l->is_active,
                'created_at'   => optional($l->created_at)->toISOString(),
                'owner'        => $l->owner_name,
                'owner_email'  => $l->owner_email,
            ]);

        return Inertia::render('Admin/ShortLinkStats', [
            'totals'  => $totals,
            'daily'   => $daily,
            'links'   => $topLinks,
            'filters' => compact('search', 'sort', 'direction'),
        ]);
    }
}
