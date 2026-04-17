<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Models\QrCodeScan;
use App\Services\QrScanAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QrCodeController extends Controller
{
    public function create()
    {
        return Inertia::render('QrCode/Create', [
            'appUrl' => config('app.url'),
        ]);
    }

    public function store(Request $request)
    {
        $type = $request->input('type', 'wifi');
        $this->normalizeUrlFields($request, $type);

        $rules = [
            'type' => ['required', 'in:wifi,url,phone,vcard,email'],
            'name' => ['required', 'string', 'max:255'],
            'foreground_color' => ['string', 'max:7'],
            'background_color' => ['string', 'max:7'],
            'dot_style' => ['string', 'max:30'],
            'corner_square_style' => ['string', 'max:30'],
            'corner_dot_style' => ['string', 'max:30'],
            'error_correction' => ['string', 'in:L,M,Q,H'],
            'size' => ['integer', 'min:100', 'max:2000'],
            'margin' => ['integer', 'min:0', 'max:100'],
            'header_text' => ['nullable', 'string', 'max:255'],
            'header_font_size' => ['nullable', 'string', 'max:5'],
            'header_color' => ['string', 'max:7'],
            'header_alignment' => ['string', 'in:left,center,right'],
            'header_margin' => ['integer', 'min:0', 'max:40'],
            'header_font_family' => ['nullable', 'string', 'max:50'],
            'header_bold' => ['boolean'],
            'header_italic' => ['boolean'],
            'header_underline' => ['boolean'],
            'header_margin_custom' => ['boolean'],
            'header_margin_top' => ['integer', 'min:0', 'max:80'],
            'header_margin_right' => ['integer', 'min:0', 'max:80'],
            'header_margin_bottom' => ['integer', 'min:0', 'max:80'],
            'header_margin_left' => ['integer', 'min:0', 'max:80'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'footer_font_size' => ['nullable', 'string', 'max:5'],
            'footer_color' => ['string', 'max:7'],
            'footer_alignment' => ['string', 'in:left,center,right'],
            'footer_margin' => ['integer', 'min:0', 'max:40'],
            'footer_font_family' => ['nullable', 'string', 'max:50'],
            'footer_bold' => ['boolean'],
            'footer_italic' => ['boolean'],
            'footer_underline' => ['boolean'],
            'footer_margin_custom' => ['boolean'],
            'footer_margin_top' => ['integer', 'min:0', 'max:80'],
            'footer_margin_right' => ['integer', 'min:0', 'max:80'],
            'footer_margin_bottom' => ['integer', 'min:0', 'max:80'],
            'footer_margin_left' => ['integer', 'min:0', 'max:80'],
            'logo_size' => ['nullable', 'integer', 'min:10', 'max:40'],
            'tracking_enabled' => ['boolean'],
        ];

        if ($type === 'wifi') {
            $rules = array_merge($rules, [
                'ssid' => ['required', 'string', 'max:255'],
                'password' => ['nullable', 'string', 'max:255'],
                'encryption' => ['required', 'in:WPA,WPA2,WPA3,WEP,nopass'],
                'hidden_network' => ['boolean'],
                'show_wifi_details' => ['boolean'],
                'wifi_details_font_family' => ['nullable', 'string', 'max:50'],
                'wifi_details_font_size' => ['nullable', 'string', 'max:5'],
                'wifi_details_color' => ['nullable', 'string', 'max:7'],
                'wifi_details_alignment' => ['nullable', 'string', 'in:left,center,right'],
                'wifi_details_bold' => ['boolean'],
                'wifi_details_italic' => ['boolean'],
                'wifi_details_underline' => ['boolean'],
                'wifi_details_password_font' => ['nullable', 'string', 'max:50'],
                'wifi_details_show_password' => ['boolean'],
                'wifi_details_margin_top' => ['integer', 'min:0', 'max:60'],
                'wifi_details_margin' => ['integer', 'min:0', 'max:60'],
                'wifi_details_margin_custom' => ['boolean'],
                'wifi_details_margin_right' => ['integer', 'min:0', 'max:60'],
                'wifi_details_margin_bottom' => ['integer', 'min:0', 'max:60'],
                'wifi_details_margin_left' => ['integer', 'min:0', 'max:60'],
                'wifi_details_password_separate_style' => ['boolean'],
                'wifi_details_password_font_size' => ['nullable', 'string', 'max:5'],
                'wifi_details_password_bold' => ['boolean'],
                'wifi_details_password_italic' => ['boolean'],
                'wifi_details_password_underline' => ['boolean'],
                'wifi_details_password_color' => ['nullable', 'string', 'max:7'],
            ]);
        } elseif ($type === 'url') {
            $rules['url'] = ['required', 'url', 'max:2048'];
        } elseif ($type === 'phone') {
            $rules['url'] = ['required', 'string', 'max:50', 'regex:/^[+\d\s\-().]+$/'];
        } elseif ($type === 'vcard') {
            $rules['vcard_data'] = ['required', 'array'];
            $rules['vcard_data.first_name'] = ['nullable', 'string', 'max:100'];
            $rules['vcard_data.last_name'] = ['nullable', 'string', 'max:100'];
            $rules['vcard_data.mobile'] = ['nullable', 'string', 'max:50'];
            $rules['vcard_data.phone'] = ['nullable', 'string', 'max:50'];
            $rules['vcard_data.fax'] = ['nullable', 'string', 'max:50'];
            $rules['vcard_data.email'] = ['nullable', 'email', 'max:255'];
            $rules['vcard_data.company'] = ['nullable', 'string', 'max:100'];
            $rules['vcard_data.job_title'] = ['nullable', 'string', 'max:100'];
            $rules['vcard_data.street'] = ['nullable', 'string', 'max:255'];
            $rules['vcard_data.city'] = ['nullable', 'string', 'max:100'];
            $rules['vcard_data.postal_code'] = ['nullable', 'string', 'max:20'];
            $rules['vcard_data.province'] = ['nullable', 'string', 'max:100'];
            $rules['vcard_data.country'] = ['nullable', 'string', 'max:100'];
            $rules['vcard_data.website'] = ['nullable', 'url', 'max:2048'];
        } else {
            $rules['email_data'] = ['required', 'array'];
            $rules['email_data.to'] = ['required', 'email', 'max:255'];
            $rules['email_data.subject'] = ['nullable', 'string', 'max:255'];
            $rules['email_data.body'] = ['nullable', 'string', 'max:2000'];
        }

        $data = $request->validate($rules);

        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('qr-logos/' . $request->user()->id, 'private');
        }

        if (!empty($data['tracking_enabled'])) {
            $data['short_code'] = $this->generateShortCode();
        }

        $qrCode = QrCode::create($data);

        return redirect()->route('qr.edit', $qrCode)->with('success', 'QR code created.');
    }

    public function edit(QrCode $qrCode, Request $request)
    {
        if ($qrCode->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        return Inertia::render('QrCode/Edit', [
            'qrCode' => $qrCode,
            'appUrl' => config('app.url'),
        ]);
    }

    public function update(Request $request, QrCode $qrCode)
    {
        if ($qrCode->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        $type = $request->input('type', $qrCode->type ?? 'wifi');
        $this->normalizeUrlFields($request, $type);

        $rules = [
            'type' => ['required', 'in:wifi,url,phone,vcard,email'],
            'name' => ['required', 'string', 'max:255'],
            'foreground_color' => ['string', 'max:7'],
            'background_color' => ['string', 'max:7'],
            'dot_style' => ['string', 'max:30'],
            'corner_square_style' => ['string', 'max:30'],
            'corner_dot_style' => ['string', 'max:30'],
            'error_correction' => ['string', 'in:L,M,Q,H'],
            'size' => ['integer', 'min:100', 'max:2000'],
            'margin' => ['integer', 'min:0', 'max:100'],
            'header_text' => ['nullable', 'string', 'max:255'],
            'header_font_size' => ['nullable', 'string', 'max:5'],
            'header_color' => ['string', 'max:7'],
            'header_alignment' => ['string', 'in:left,center,right'],
            'header_margin' => ['integer', 'min:0', 'max:40'],
            'header_font_family' => ['nullable', 'string', 'max:50'],
            'header_bold' => ['boolean'],
            'header_italic' => ['boolean'],
            'header_underline' => ['boolean'],
            'header_margin_custom' => ['boolean'],
            'header_margin_top' => ['integer', 'min:0', 'max:80'],
            'header_margin_right' => ['integer', 'min:0', 'max:80'],
            'header_margin_bottom' => ['integer', 'min:0', 'max:80'],
            'header_margin_left' => ['integer', 'min:0', 'max:80'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'footer_font_size' => ['nullable', 'string', 'max:5'],
            'footer_color' => ['string', 'max:7'],
            'footer_alignment' => ['string', 'in:left,center,right'],
            'footer_margin' => ['integer', 'min:0', 'max:40'],
            'footer_font_family' => ['nullable', 'string', 'max:50'],
            'footer_bold' => ['boolean'],
            'footer_italic' => ['boolean'],
            'footer_underline' => ['boolean'],
            'footer_margin_custom' => ['boolean'],
            'footer_margin_top' => ['integer', 'min:0', 'max:80'],
            'footer_margin_right' => ['integer', 'min:0', 'max:80'],
            'footer_margin_bottom' => ['integer', 'min:0', 'max:80'],
            'footer_margin_left' => ['integer', 'min:0', 'max:80'],
            'logo_size' => ['nullable', 'integer', 'min:10', 'max:40'],
            'tracking_enabled' => ['boolean'],
        ];

        if ($type === 'wifi') {
            $rules = array_merge($rules, [
                'ssid' => ['required', 'string', 'max:255'],
                'password' => ['nullable', 'string', 'max:255'],
                'encryption' => ['required', 'in:WPA,WPA2,WPA3,WEP,nopass'],
                'hidden_network' => ['boolean'],
                'show_wifi_details' => ['boolean'],
                'wifi_details_font_family' => ['nullable', 'string', 'max:50'],
                'wifi_details_font_size' => ['nullable', 'string', 'max:5'],
                'wifi_details_color' => ['nullable', 'string', 'max:7'],
                'wifi_details_alignment' => ['nullable', 'string', 'in:left,center,right'],
                'wifi_details_bold' => ['boolean'],
                'wifi_details_italic' => ['boolean'],
                'wifi_details_underline' => ['boolean'],
                'wifi_details_password_font' => ['nullable', 'string', 'max:50'],
                'wifi_details_show_password' => ['boolean'],
                'wifi_details_margin_top' => ['integer', 'min:0', 'max:60'],
                'wifi_details_margin' => ['integer', 'min:0', 'max:60'],
                'wifi_details_margin_custom' => ['boolean'],
                'wifi_details_margin_right' => ['integer', 'min:0', 'max:60'],
                'wifi_details_margin_bottom' => ['integer', 'min:0', 'max:60'],
                'wifi_details_margin_left' => ['integer', 'min:0', 'max:60'],
                'wifi_details_password_separate_style' => ['boolean'],
                'wifi_details_password_font_size' => ['nullable', 'string', 'max:5'],
                'wifi_details_password_bold' => ['boolean'],
                'wifi_details_password_italic' => ['boolean'],
                'wifi_details_password_underline' => ['boolean'],
                'wifi_details_password_color' => ['nullable', 'string', 'max:7'],
            ]);
        } elseif ($type === 'url') {
            $rules['url'] = ['required', 'url', 'max:2048'];
        } elseif ($type === 'phone') {
            $rules['url'] = ['required', 'string', 'max:50', 'regex:/^[+\d\s\-().]+$/'];
        } elseif ($type === 'vcard') {
            $rules['vcard_data'] = ['required', 'array'];
            $rules['vcard_data.first_name'] = ['nullable', 'string', 'max:100'];
            $rules['vcard_data.last_name'] = ['nullable', 'string', 'max:100'];
            $rules['vcard_data.mobile'] = ['nullable', 'string', 'max:50'];
            $rules['vcard_data.phone'] = ['nullable', 'string', 'max:50'];
            $rules['vcard_data.fax'] = ['nullable', 'string', 'max:50'];
            $rules['vcard_data.email'] = ['nullable', 'email', 'max:255'];
            $rules['vcard_data.company'] = ['nullable', 'string', 'max:100'];
            $rules['vcard_data.job_title'] = ['nullable', 'string', 'max:100'];
            $rules['vcard_data.street'] = ['nullable', 'string', 'max:255'];
            $rules['vcard_data.city'] = ['nullable', 'string', 'max:100'];
            $rules['vcard_data.postal_code'] = ['nullable', 'string', 'max:20'];
            $rules['vcard_data.province'] = ['nullable', 'string', 'max:100'];
            $rules['vcard_data.country'] = ['nullable', 'string', 'max:100'];
            $rules['vcard_data.website'] = ['nullable', 'url', 'max:2048'];
        } else {
            $rules['email_data'] = ['required', 'array'];
            $rules['email_data.to'] = ['required', 'email', 'max:255'];
            $rules['email_data.subject'] = ['nullable', 'string', 'max:255'];
            $rules['email_data.body'] = ['nullable', 'string', 'max:2000'];
        }

        $data = $request->validate($rules);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('qr-logos/' . $request->user()->id, 'private');
        }

        if (!empty($data['tracking_enabled']) && !$qrCode->short_code) {
            $data['short_code'] = $this->generateShortCode();
        }

        $qrCode->update($data);

        return back()->with('success', 'QR code updated.');
    }

    public function destroy(QrCode $qrCode, Request $request)
    {
        if ($qrCode->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        $qrCode->delete();

        return redirect()->route('dashboard')->with('success', 'QR code deleted.');
    }

    public function analytics(QrCode $qrCode, Request $request, QrScanAnalyticsService $analytics)
    {
        if ($qrCode->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        if (!$qrCode->tracking_enabled) {
            return redirect()->route('qr.edit', $qrCode)
                ->with('error', 'Scan tracking is disabled for this QR code. Enable it to see analytics.');
        }

        return Inertia::render('QrCode/Analytics', [
            'qrCode' => [
                'id' => $qrCode->id,
                'name' => $qrCode->name,
                'type' => $qrCode->type,
                'short_code' => $qrCode->short_code,
                'tracking_enabled' => $qrCode->tracking_enabled,
                'created_at' => $qrCode->created_at?->toISOString(),
            ],
            'analytics' => $analytics->forQrCode($qrCode),
            'appUrl' => config('app.url'),
        ]);
    }

    public function track(string $shortCode)
    {
        $qrCode = QrCode::where('short_code', $shortCode)->firstOrFail();

        if ($qrCode->type !== 'url' || !$qrCode->url) {
            abort(404);
        }

        QrCodeScan::create([
            'qr_code_id' => $qrCode->id,
            'scanned_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referer' => request()->header('referer'),
        ]);

        return redirect($qrCode->url);
    }

    /**
     * If the user entered a URL without a scheme (e.g. `example.com`), prepend
     * `https://` so Laravel's `url` validator accepts it and we save a working
     * link. Applied to the main `url` field on URL-type QRs and the
     * `vcard_data.website` field on vCard QRs.
     */
    private function normalizeUrlFields(Request $request, string $type): void
    {
        if ($type === 'url') {
            $url = trim((string) $request->input('url', ''));
            if ($url !== '' && !preg_match('~^[a-z][a-z0-9+.\-]*://~i', $url)) {
                $request->merge(['url' => 'https://' . ltrim($url, '/')]);
            }
        }

        if ($type === 'vcard') {
            $website = trim((string) $request->input('vcard_data.website', ''));
            if ($website !== '' && !preg_match('~^[a-z][a-z0-9+.\-]*://~i', $website)) {
                $vcard = $request->input('vcard_data', []);
                $vcard['website'] = 'https://' . ltrim($website, '/');
                $request->merge(['vcard_data' => $vcard]);
            }
        }
    }

    private function generateShortCode(): string
    {
        do {
            $code = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        } while (QrCode::where('short_code', $code)->exists());

        return $code;
    }
}
