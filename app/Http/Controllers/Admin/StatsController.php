<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\QrCodeScan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $now = CarbonImmutable::now();
        $since30 = $now->startOfDay()->subDays(29);

        $totals = [
            'users' => User::count(),
            'admins' => User::whereIn('role', ['admin', 'super_admin'])->count(),
            'qr_codes' => QrCode::count(),
            'qr_codes_tracking' => QrCode::where('tracking_enabled', true)->count(),
            'scans_all_time' => QrCodeScan::count(),
            'scans_today' => QrCodeScan::where('scanned_at', '>=', $now->startOfDay())->count(),
            'scans_7d' => QrCodeScan::where('scanned_at', '>=', $now->startOfDay()->subDays(6))->count(),
            'scans_30d' => QrCodeScan::where('scanned_at', '>=', $since30)->count(),
        ];

        // Scans per day (last 30 days) for the global trend chart
        $dailyRaw = QrCodeScan::where('scanned_at', '>=', $since30)
            ->select(DB::raw('DATE(scanned_at) as day'), DB::raw('COUNT(*) as count'))
            ->groupBy('day')
            ->pluck('count', 'day')
            ->all();

        $daily = [];
        for ($i = 0; $i < 30; $i++) {
            $day = $since30->addDays($i)->toDateString();
            $daily[] = [
                'day' => $day,
                'count' => (int) ($dailyRaw[$day] ?? 0),
            ];
        }

        $sort = (string) $request->string('sort', 'scans');
        $direction = $request->string('dir', 'desc')->toString() === 'asc' ? 'asc' : 'desc';
        $search = trim((string) $request->string('search'));

        $usersQuery = User::query()
            ->leftJoin('qr_codes', 'qr_codes.user_id', '=', 'users.id')
            ->leftJoin('qr_code_scans', 'qr_code_scans.qr_code_id', '=', 'qr_codes.id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.role',
                'users.created_at',
                DB::raw('COUNT(DISTINCT qr_codes.id) as qr_codes_count'),
                DB::raw('COUNT(qr_code_scans.id) as scans_count'),
                DB::raw('MAX(qr_code_scans.scanned_at) as last_scan_at'),
            ])
            ->groupBy('users.id', 'users.name', 'users.email', 'users.role', 'users.created_at');

        if ($search !== '') {
            $like = '%' . $search . '%';
            $usersQuery->where(function ($q) use ($like) {
                $q->where('users.name', 'like', $like)
                    ->orWhere('users.email', 'like', $like);
            });
        }

        $orderColumn = match ($sort) {
            'name' => 'users.name',
            'email' => 'users.email',
            'created' => 'users.created_at',
            'qr_codes' => 'qr_codes_count',
            'last_scan' => 'last_scan_at',
            default => 'scans_count',
        };

        $usersQuery->orderBy($orderColumn, $direction);

        $users = $usersQuery->paginate(20)->withQueryString()
            ->through(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'created_at' => optional($u->created_at)->toISOString(),
                'qr_codes_count' => (int) $u->qr_codes_count,
                'scans_count' => (int) $u->scans_count,
                'last_scan_at' => $u->last_scan_at
                    ? CarbonImmutable::parse($u->last_scan_at)->toISOString()
                    : null,
            ]);

        // Top QR codes globally
        $topQrCodes = QrCode::query()
            ->leftJoin('qr_code_scans', 'qr_code_scans.qr_code_id', '=', 'qr_codes.id')
            ->leftJoin('users', 'users.id', '=', 'qr_codes.user_id')
            ->select([
                'qr_codes.id',
                'qr_codes.name',
                'qr_codes.type',
                'users.name as owner_name',
                'users.email as owner_email',
                DB::raw('COUNT(qr_code_scans.id) as scans_count'),
            ])
            ->where('qr_codes.tracking_enabled', true)
            ->groupBy('qr_codes.id', 'qr_codes.name', 'qr_codes.type', 'users.name', 'users.email')
            ->orderByDesc('scans_count')
            ->limit(10)
            ->get()
            ->map(fn ($q) => [
                'id' => $q->id,
                'name' => $q->name,
                'type' => $q->type,
                'owner' => $q->owner_name,
                'owner_email' => $q->owner_email,
                'scans_count' => (int) $q->scans_count,
            ])
            ->all();

        return Inertia::render('Admin/Stats', [
            'totals' => $totals,
            'daily' => $daily,
            'users' => $users,
            'topQrCodes' => $topQrCodes,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'dir' => $direction,
            ],
        ]);
    }
}
