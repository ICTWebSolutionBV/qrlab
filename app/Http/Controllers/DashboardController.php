<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $qrCodes = $request->user()->qrCodes()
            ->withCount('scans')
            ->latest()
            ->paginate(12);

        return Inertia::render('Dashboard', [
            'qrCodes' => $qrCodes,
            'appUrl' => config('app.url'),
        ]);
    }
}
