<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QrCodeController extends Controller
{
    public function create()
    {
        return Inertia::render('QrCode/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ssid' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['required', 'in:WPA,WPA2,WPA3,WEP,nopass'],
            'hidden_network' => ['boolean'],
            'foreground_color' => ['string', 'max:7'],
            'background_color' => ['string', 'max:7'],
            'dot_style' => ['string', 'max:30'],
            'corner_square_style' => ['string', 'max:30'],
            'corner_dot_style' => ['string', 'max:30'],
            'error_correction' => ['string', 'in:L,M,Q,H'],
            'size' => ['integer', 'min:100', 'max:2000'],
            'margin' => ['integer', 'min:0', 'max:100'],
            'header_text' => ['nullable', 'string', 'max:255'],
            'header_font_size' => ['string', 'in:12,14,16,18,20,24,28,32'],
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
            'footer_font_size' => ['string', 'in:12,14,16,18,20,24,28,32'],
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
            'show_wifi_details' => ['boolean'],
            'wifi_details_font_size' => ['nullable', 'string', 'in:12,14,16,18,20,24,28,32'],
            'wifi_details_color' => ['nullable', 'string', 'max:7'],
            'wifi_details_alignment' => ['nullable', 'string', 'in:left,center,right'],
            'wifi_details_password_font' => ['nullable', 'string', 'max:50'],
            'wifi_details_show_password' => ['boolean'],
            'wifi_details_margin_top' => ['integer', 'min:0', 'max:60'],
            'logo_size' => ['nullable', 'integer', 'min:10', 'max:40'],
            'frame_style' => ['nullable', 'string', 'max:30'],
            'frame_color' => ['nullable', 'string', 'max:7'],
            'frame_text' => ['nullable', 'string', 'max:100'],
            'tracking_enabled' => ['boolean'],
        ]);

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
        ]);
    }

    public function update(Request $request, QrCode $qrCode)
    {
        if ($qrCode->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ssid' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['required', 'in:WPA,WPA2,WPA3,WEP,nopass'],
            'hidden_network' => ['boolean'],
            'foreground_color' => ['string', 'max:7'],
            'background_color' => ['string', 'max:7'],
            'dot_style' => ['string', 'max:30'],
            'corner_square_style' => ['string', 'max:30'],
            'corner_dot_style' => ['string', 'max:30'],
            'error_correction' => ['string', 'in:L,M,Q,H'],
            'size' => ['integer', 'min:100', 'max:2000'],
            'margin' => ['integer', 'min:0', 'max:100'],
            'header_text' => ['nullable', 'string', 'max:255'],
            'header_font_size' => ['string', 'in:12,14,16,18,20,24,28,32'],
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
            'footer_font_size' => ['string', 'in:12,14,16,18,20,24,28,32'],
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
            'show_wifi_details' => ['boolean'],
            'wifi_details_font_size' => ['nullable', 'string', 'in:12,14,16,18,20,24,28,32'],
            'wifi_details_color' => ['nullable', 'string', 'max:7'],
            'wifi_details_alignment' => ['nullable', 'string', 'in:left,center,right'],
            'wifi_details_password_font' => ['nullable', 'string', 'max:50'],
            'wifi_details_show_password' => ['boolean'],
            'wifi_details_margin_top' => ['integer', 'min:0', 'max:60'],
            'logo_size' => ['nullable', 'integer', 'min:10', 'max:40'],
            'frame_style' => ['nullable', 'string', 'max:30'],
            'frame_color' => ['nullable', 'string', 'max:7'],
            'frame_text' => ['nullable', 'string', 'max:100'],
            'tracking_enabled' => ['boolean'],
        ]);

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

    private function generateShortCode(): string
    {
        do {
            $code = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        } while (QrCode::where('short_code', $code)->exists());

        return $code;
    }
}
