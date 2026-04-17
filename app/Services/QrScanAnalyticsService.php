<?php

namespace App\Services;

use App\Models\QrCode;
use App\Models\QrCodeScan;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class QrScanAnalyticsService
{
    /**
     * Full analytics payload for a single QR code.
     *
     * @return array{
     *   totals: array,
     *   daily: array,
     *   hourly: array,
     *   browsers: array,
     *   devices: array,
     *   os: array,
     *   recent: array,
     * }
     */
    public function forQrCode(QrCode $qrCode, int $days = 30): array
    {
        $now = CarbonImmutable::now();
        $since = $now->startOfDay()->subDays($days - 1);

        $base = QrCodeScan::where('qr_code_id', $qrCode->id);

        $todayStart = $now->startOfDay();
        $weekStart = $now->startOfDay()->subDays(6);
        $monthStart = $now->startOfDay()->subDays(29);

        $totals = [
            'all_time' => (clone $base)->count(),
            'today' => (clone $base)->where('scanned_at', '>=', $todayStart)->count(),
            'last_7_days' => (clone $base)->where('scanned_at', '>=', $weekStart)->count(),
            'last_30_days' => (clone $base)->where('scanned_at', '>=', $monthStart)->count(),
            'unique_visitors' => (clone $base)->distinct('ip_address')->count('ip_address'),
        ];

        // Daily bucketing over the last N days
        $dailyRaw = (clone $base)
            ->where('scanned_at', '>=', $since)
            ->select(DB::raw('DATE(scanned_at) as day'), DB::raw('COUNT(*) as count'))
            ->groupBy('day')
            ->pluck('count', 'day')
            ->all();

        $daily = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $since->addDays($i)->toDateString();
            $daily[] = [
                'day' => $day,
                'count' => (int) ($dailyRaw[$day] ?? 0),
            ];
        }

        // Hourly distribution (last 24h)
        $hourlySince = $now->subHours(23)->startOfHour();
        $hourlyRaw = (clone $base)
            ->where('scanned_at', '>=', $hourlySince)
            ->get(['scanned_at'])
            ->groupBy(fn ($s) => CarbonImmutable::parse($s->scanned_at)->format('Y-m-d H:00'))
            ->map->count();

        $hourly = [];
        for ($i = 0; $i < 24; $i++) {
            $bucket = $hourlySince->addHours($i)->format('Y-m-d H:00');
            $hourly[] = [
                'hour' => $hourlySince->addHours($i)->format('H:00'),
                'count' => (int) ($hourlyRaw[$bucket] ?? 0),
            ];
        }

        // Parse UAs for browser / device / os breakdowns
        $scanMeta = (clone $base)->get(['user_agent', 'country', 'country_code', 'city']);

        $browsers = [];
        $devices = [];
        $os = [];
        $countries = [];
        $cities = [];
        foreach ($scanMeta as $s) {
            $parsed = $this->parseUserAgent((string) $s->user_agent);
            $browsers[$parsed['browser']] = ($browsers[$parsed['browser']] ?? 0) + 1;
            $devices[$parsed['device']] = ($devices[$parsed['device']] ?? 0) + 1;
            $os[$parsed['os']] = ($os[$parsed['os']] ?? 0) + 1;

            $countryLabel = $s->country ?: 'Unknown';
            $countryKey = ($s->country_code ?: '--') . '|' . $countryLabel;
            if (!isset($countries[$countryKey])) {
                $countries[$countryKey] = [
                    'label' => $countryLabel,
                    'code' => $s->country_code,
                    'count' => 0,
                ];
            }
            $countries[$countryKey]['count']++;

            if ($s->city) {
                $cityLabel = $s->city . ($s->country ? ', ' . $s->country : '');
                $cities[$cityLabel] = ($cities[$cityLabel] ?? 0) + 1;
            }
        }

        arsort($browsers);
        arsort($devices);
        arsort($os);
        usort($countries, fn ($a, $b) => $b['count'] <=> $a['count']);
        arsort($cities);

        // Recent scans (last 20)
        $recent = (clone $base)
            ->latest('scanned_at')
            ->limit(20)
            ->get()
            ->map(function (QrCodeScan $s) {
                $parsed = $this->parseUserAgent((string) $s->user_agent);
                $location = $s->city && $s->country
                    ? $s->city . ', ' . $s->country
                    : ($s->country ?: 'Unknown');
                return [
                    'id' => $s->id,
                    'scanned_at' => $s->scanned_at?->toISOString(),
                    'ip_masked' => $this->maskIp((string) $s->ip_address),
                    'browser' => $parsed['browser'],
                    'device' => $parsed['device'],
                    'os' => $parsed['os'],
                    'location' => $location,
                    'country_code' => $s->country_code,
                    'referer' => $s->referer,
                ];
            })
            ->all();

        // Trim country list to top N + "Other", preserving the flag code
        $topCountries = array_slice($countries, 0, 6);
        $otherCount = 0;
        foreach (array_slice($countries, 6) as $c) $otherCount += $c['count'];
        if ($otherCount > 0) {
            $topCountries[] = ['label' => 'Other', 'code' => null, 'count' => $otherCount];
        }

        return [
            'totals' => $totals,
            'daily' => $daily,
            'hourly' => $hourly,
            'browsers' => $this->toBreakdown($browsers, 6),
            'devices' => $this->toBreakdown($devices, 6),
            'os' => $this->toBreakdown($os, 6),
            'countries' => $topCountries,
            'cities' => $this->toBreakdown($cities, 6),
            'recent' => $recent,
        ];
    }

    private function toBreakdown(array $counts, int $limit): array
    {
        $items = [];
        $i = 0;
        $other = 0;
        foreach ($counts as $label => $count) {
            if ($i < $limit) {
                $items[] = ['label' => $label, 'count' => $count];
            } else {
                $other += $count;
            }
            $i++;
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
            // IPv6 → keep first 3 groups
            $parts = explode(':', $ip);
            return implode(':', array_slice($parts, 0, 3)) . '::';
        }
        $parts = explode('.', $ip);
        if (count($parts) !== 4) return $ip;
        return $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.x';
    }

    public function parseUserAgent(string $ua): array
    {
        if ($ua === '') {
            return ['browser' => 'Unknown', 'device' => 'Unknown', 'os' => 'Unknown'];
        }

        // OS
        $os = match (true) {
            preg_match('/iPhone|iPad|iPod/i', $ua) === 1 => 'iOS',
            preg_match('/Android/i', $ua) === 1 => 'Android',
            preg_match('/Mac OS X|Macintosh/i', $ua) === 1 => 'macOS',
            preg_match('/Windows/i', $ua) === 1 => 'Windows',
            preg_match('/Linux/i', $ua) === 1 => 'Linux',
            preg_match('/CrOS/i', $ua) === 1 => 'ChromeOS',
            default => 'Other',
        };

        // Browser — order matters (Edge before Chrome, Chrome before Safari)
        $browser = match (true) {
            preg_match('/Edg\//i', $ua) === 1 => 'Edge',
            preg_match('/OPR\/|Opera/i', $ua) === 1 => 'Opera',
            preg_match('/Firefox|FxiOS/i', $ua) === 1 => 'Firefox',
            preg_match('/Chrome|CriOS/i', $ua) === 1 => 'Chrome',
            preg_match('/Safari/i', $ua) === 1 => 'Safari',
            preg_match('/bot|spider|crawl/i', $ua) === 1 => 'Bot',
            default => 'Other',
        };

        // Device
        $device = match (true) {
            preg_match('/iPad|Tablet/i', $ua) === 1 => 'Tablet',
            preg_match('/Mobile|iPhone|Android.*Mobile/i', $ua) === 1 => 'Mobile',
            preg_match('/Android/i', $ua) === 1 => 'Mobile',
            default => 'Desktop',
        };

        return compact('browser', 'device', 'os');
    }
}
