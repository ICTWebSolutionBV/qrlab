<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QrCode extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'url',
        'ssid',
        'password',
        'encryption',
        'hidden_network',
        'foreground_color',
        'background_color',
        'dot_style',
        'corner_square_style',
        'corner_dot_style',
        'error_correction',
        'size',
        'margin',
        'header_text',
        'header_font_size',
        'header_color',
        'header_alignment',
        'header_margin',
        'header_font_family',
        'header_bold',
        'header_italic',
        'header_underline',
        'header_margin_custom',
        'header_margin_top',
        'header_margin_right',
        'header_margin_bottom',
        'header_margin_left',
        'footer_text',
        'footer_font_size',
        'footer_color',
        'footer_alignment',
        'footer_margin',
        'footer_font_family',
        'footer_bold',
        'footer_italic',
        'footer_underline',
        'footer_margin_custom',
        'footer_margin_top',
        'footer_margin_right',
        'footer_margin_bottom',
        'footer_margin_left',
        'show_wifi_details',
        'wifi_details_font_family',
        'wifi_details_font_size',
        'wifi_details_color',
        'wifi_details_alignment',
        'wifi_details_bold',
        'wifi_details_italic',
        'wifi_details_underline',
        'wifi_details_password_font',
        'wifi_details_show_password',
        'wifi_details_margin_top',
        'wifi_details_margin',
        'wifi_details_margin_custom',
        'wifi_details_margin_right',
        'wifi_details_margin_bottom',
        'wifi_details_margin_left',
        'wifi_details_password_separate_style',
        'wifi_details_password_font_size',
        'wifi_details_password_bold',
        'wifi_details_password_italic',
        'wifi_details_password_underline',
        'wifi_details_password_color',
        'logo_path',
        'logo_size',
        'frame_style',
        'frame_color',
        'frame_text',
        'vcard_data',
        'email_data',
        'tracking_enabled',
        'short_code',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'hidden_network' => 'boolean',
            'tracking_enabled' => 'boolean',
            'header_bold' => 'boolean',
            'header_italic' => 'boolean',
            'header_underline' => 'boolean',
            'header_margin_custom' => 'boolean',
            'header_margin_top' => 'integer',
            'header_margin_right' => 'integer',
            'header_margin_bottom' => 'integer',
            'header_margin_left' => 'integer',
            'footer_bold' => 'boolean',
            'footer_italic' => 'boolean',
            'footer_underline' => 'boolean',
            'footer_margin_custom' => 'boolean',
            'footer_margin_top' => 'integer',
            'footer_margin_right' => 'integer',
            'footer_margin_bottom' => 'integer',
            'footer_margin_left' => 'integer',
            'show_wifi_details' => 'boolean',
            'wifi_details_bold' => 'boolean',
            'wifi_details_italic' => 'boolean',
            'wifi_details_underline' => 'boolean',
            'wifi_details_show_password' => 'boolean',
            'wifi_details_margin_top' => 'integer',
            'wifi_details_margin' => 'integer',
            'wifi_details_margin_custom' => 'boolean',
            'wifi_details_margin_right' => 'integer',
            'wifi_details_margin_bottom' => 'integer',
            'wifi_details_margin_left' => 'integer',
            'wifi_details_password_separate_style' => 'boolean',
            'wifi_details_password_bold' => 'boolean',
            'wifi_details_password_italic' => 'boolean',
            'wifi_details_password_underline' => 'boolean',
            'size' => 'integer',
            'margin' => 'integer',
            'logo_size' => 'integer',
            'vcard_data' => 'array',
            'email_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scans(): HasMany
    {
        return $this->hasMany(QrCodeScan::class);
    }

    public function toUrlString(): string
    {
        return $this->url ?? '';
    }

    public function toWifiString(): string
    {
        $encryption = match ($this->encryption) {
            'WPA', 'WPA2', 'WPA3' => 'WPA',
            'WEP' => 'WEP',
            default => 'nopass',
        };

        $ssid = $this->escapeWifiField($this->ssid);
        $password = $this->password ? $this->escapeWifiField($this->password) : '';
        $hidden = $this->hidden_network ? 'true' : 'false';

        return "WIFI:T:{$encryption};S:{$ssid};P:{$password};H:{$hidden};;";
    }

    private function escapeWifiField(string $value): string
    {
        return str_replace(
            ['\\', ';', ',', '"', ':'],
            ['\\\\', '\\;', '\\,', '\\"', '\\:'],
            $value
        );
    }
}
