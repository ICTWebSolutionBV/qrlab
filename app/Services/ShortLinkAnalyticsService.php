<?php

namespace App\Services;

use App\Models\ShortLink;
use App\Models\ShortLinkClick;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ShortLinkAnalyticsService
{
    public function forLink(ShortLink $link, int $days = 30): array
    {
        $now = CarbonImmutable::now();
        $since = $now->startOfDay()->subDays($days - 1);

        $base = ShortLinkClick::where('short_link_id', $link->id);

        $totals = [
            'all_time'       => (clone $base)->count(),
            'today'          => (clone $base)->where('clicked_at', '>=', $now->startOfDay())->count(),
            'last_7_days'    => (clone $base)->where('clicked_at', '>=', $now->startOfDay()->subDays(6))->count(),
            'last_30_days'   => (clone $base)->where('clicked_at', '>=', $now->startOfDay()->subDays(29))->count(),
            'unique_visitors'=> (clone $base)->distinct('ip_address')->count('ip_address'),
        ];

        $dailyRaw = (clone $base)
            ->where('clicked_at', '>=', $since)
            ->select(DB::raw('DATE(clicked_at) as day'), DB::raw('COUNT(*) as count'))
            ->groupBy('day')
            ->pluck('count', 'day')
            ->all();

        $daily = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $since->addDays($i)->toDateString();
            $daily[] = ['day' => $day, 'count' => (int) ($dailyRaw[$day] ?? 0)];
        }

        $hourlySince = $now->subHours(23)->startOfHour();
        $hourlyRaw = (clone $base)
            ->where('clicked_at', '>=', $hourlySince)
            ->get(['clicked_at'])
            ->groupBy(fn ($c) => CarbonImmutable::parse($c->clicked_at)->format('Y-m-d H:00'))
            ->map->count();

        $hourly = [];
        for ($i = 0; $i < 24; $i++) {
            $bucket = $hourlySince->addHours($i)->format('Y-m-d H:00');
            $hourly[] = [
                'hour'  => $hourlySince->addHours($i)->format('H:00'),
                'count' => (int) ($hourlyRaw[$bucket] ?? 0),
            ];
        }

        $clickMeta = (clone $base)->get(['user_agent', 'country', 'country_code', 'city', 'referer']);

        $browsers = [];
        $devices = [];
        $os = [];
        $countries = [];
        $referers = [];

        $parser = app(QrScanAnalyticsService::class);

        foreach ($clickMeta as $c) {
            $parsed = $parser->parseUserAgent((string) $c->user_agent);
            $browsers[$parsed['browser']] = ($browsers[$parsed['browser']] ?? 0) + 1;
            $devices[$parsed['device']] = ($devices[$parsed['device']] ?? 0) + 1;
            $os[$parsed['os']] = ($os[$parsed['os']] ?? 0) + 1;

            $countryLabel = $c->country ?: 'Unknown';
            $countryKey = ($c->country_code ?: '--') . '|' . $countryLabel;
            if (!isset($countries[$countryKey])) {
                $countries[$countryKey] = ['label' => $countryLabel, 'code' => $c->country_code, 'count' => 0];
            }
            $countries[$countryKey]['count']++;

            if ($c->referer) {
                $host = parse_url($c->referer, PHP_URL_HOST) ?: $c->referer;
                $referers[$host] = ($referers[$host] ?? 0) + 1;
            }
        }

        arsort($browsers);
        arsort($devices);
        arsort($os);
        arsort($referers);
        usort($countries, fn ($a, $b) => $b['count'] <=> $a['count']);

        $recent = (clone $base)
            ->latest('clicked_at')
            ->limit(20)
            ->get()
            ->map(function (ShortLinkClick $c) use ($parser) {
                $parsed = $parser->parseUserAgent((string) $c->user_agent);
                $location = $c->city && $c->country
                    ? $c->city . ', ' . $c->country
                    : ($c->country ?: 'Unknown');
                return [
                    'id'           => $c->id,
                    'clicked_at'   => $c->clicked_at?->toISOString(),
                    'ip_masked'    => $this->maskIp((string) $c->ip_address),
                    'browser'      => $parsed['browser'],
                    'device'       => $parsed['device'],
                    'os'           => $parsed['os'],
                    'location'     => $location,
                    'country_code' => $c->country_code,
                    'referer'      => $c->referer,
                ];
            })
            ->all();

        $topCountries = array_slice($countries, 0, 6);
        $otherCount = 0;
        foreach (array_slice($countries, 6) as $c) $otherCount += $c['count'];
        if ($otherCount > 0) {
            $topCountries[] = ['label' => 'Other', 'code' => null, 'count' => $otherCount];
        }

        return [
            'totals'    => $totals,
            'daily'     => $daily,
            'hourly'    => $hourly,
            'browsers'  => $this->toBreakdown($browsers, 6),
            'devices'   => $this->toBreakdown($devices, 6),
            'os'        => $this->toBreakdown($os, 6),
            'countries' => $topCountries,
            'referers'  => $this->toBreakdown($referers, 8),
            'recent'    => $recent,
        ];
    }

    private function toBreakdown(array $counts, int $limit): array
    {
        $items = [];
        $i = 0;
        $other = 0;
        foreach ($counts as $label => $count) {
            if ($i++ < $limit) {
                $items[] = ['label' => $label, 'count' => $count];
            } else {
                $other += $count;
            }
        }
        if ($other > 0) {
            $items[] = ['label' => 'Other', 'count' => $other];
        }
        return $items;
    }

    private function maskIp(string $ip): string
    {
        if ($ip === '') return 'unknown';
        if (str_contains($ip, ':')) {
            $parts = explode(':', $ip);
            return implode(':', array_slice($parts, 0, 3)) . '::';
        }
        $parts = explode('.', $ip);
        if (count($parts) !== 4) return $ip;
        return $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.x';
    }
}
