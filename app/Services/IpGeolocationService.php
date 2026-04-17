<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Lightweight IP → country/region/city resolver using ip-api.com (no key, 45
 * req/min). Results cached for 24h per IP. All failures are swallowed so a
 * slow/blocked geolookup never breaks the redirect.
 */
class IpGeolocationService
{
    private const ENDPOINT = 'http://ip-api.com/json/';
    private const TIMEOUT_SECONDS = 2;
    private const CACHE_TTL_SECONDS = 86400; // 24h

    /**
     * @return array{country: ?string, country_code: ?string, region: ?string, city: ?string}
     */
    public function lookup(?string $ip): array
    {
        $blank = ['country' => null, 'country_code' => null, 'region' => null, 'city' => null];

        if (!$ip || $this->isPrivate($ip)) {
            return $blank;
        }

        $cacheKey = 'geoip:' . $ip;

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($ip, $blank) {
            try {
                $res = Http::timeout(self::TIMEOUT_SECONDS)
                    ->get(self::ENDPOINT . urlencode($ip), [
                        'fields' => 'status,country,countryCode,regionName,city',
                    ]);

                if (!$res->successful()) return $blank;

                $json = $res->json();
                if (($json['status'] ?? null) !== 'success') return $blank;

                return [
                    'country' => $json['country'] ?? null,
                    'country_code' => $json['countryCode'] ?? null,
                    'region' => $json['regionName'] ?? null,
                    'city' => $json['city'] ?? null,
                ];
            } catch (\Throwable $e) {
                Log::warning('IP geolocation failed', ['ip' => $ip, 'error' => $e->getMessage()]);
                return $blank;
            }
        });
    }

    private function isPrivate(string $ip): bool
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
