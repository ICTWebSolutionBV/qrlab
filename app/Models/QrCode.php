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
        'name',
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
        'footer_text',
        'footer_font_size',
        'footer_color',
        'footer_alignment',
        'footer_margin',
        'logo_path',
        'logo_size',
        'frame_style',
        'frame_color',
        'frame_text',
        'tracking_enabled',
        'short_code',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'hidden_network' => 'boolean',
            'tracking_enabled' => 'boolean',
            'size' => 'integer',
            'margin' => 'integer',
            'logo_size' => 'integer',
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
