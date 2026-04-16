<?php

namespace App\Services;

use App\Models\QrCode as QrCodeModel;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\EpsWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Illuminate\Support\Facades\Storage;

class QrGeneratorService
{
    public function generate(QrCodeModel $qrCode, string $format = 'png'): ResultInterface
    {
        $writer = match ($format) {
            'svg' => new SvgWriter(),
            'eps' => new EpsWriter(),
            default => new PngWriter(),
        };

        $errorLevel = match ($qrCode->error_correction) {
            'L' => ErrorCorrectionLevel::Low,
            'Q' => ErrorCorrectionLevel::Quartile,
            'H' => ErrorCorrectionLevel::High,
            default => ErrorCorrectionLevel::Medium,
        };

        [$fgR, $fgG, $fgB] = $this->hexToRgb($qrCode->foreground_color ?? '#000000');
        [$bgR, $bgG, $bgB] = $this->hexToRgb($qrCode->background_color ?? '#FFFFFF');

        $logoPath = null;
        if ($qrCode->logo_path && Storage::disk('private')->exists($qrCode->logo_path)) {
            $logoPath = Storage::disk('private')->path($qrCode->logo_path);
        }

        $logoResizeWidth = null;
        if ($logoPath && $qrCode->logo_size) {
            $logoResizeWidth = (int) round($qrCode->size * ($qrCode->logo_size / 100));
        }

        $builder = new Builder(
            writer: $writer,
            data: $qrCode->toWifiString(),
            errorCorrectionLevel: $errorLevel,
            size: $qrCode->size,
            margin: $qrCode->margin,
            foregroundColor: new Color($fgR, $fgG, $fgB),
            backgroundColor: new Color($bgR, $bgG, $bgB),
            labelText: $qrCode->footer_text ?? '',
            logoPath: $logoPath ?? '',
            logoResizeToWidth: $logoResizeWidth,
            logoPunchoutBackground: true,
        );

        return $builder->build();
    }

    public function generateFromConfig(array $config, string $format = 'png'): ResultInterface
    {
        $writer = match ($format) {
            'svg' => new SvgWriter(),
            'eps' => new EpsWriter(),
            default => new PngWriter(),
        };

        $errorLevel = match ($config['error_correction'] ?? 'M') {
            'L' => ErrorCorrectionLevel::Low,
            'Q' => ErrorCorrectionLevel::Quartile,
            'H' => ErrorCorrectionLevel::High,
            default => ErrorCorrectionLevel::Medium,
        };

        [$fgR, $fgG, $fgB] = $this->hexToRgb($config['foreground_color'] ?? '#000000');
        [$bgR, $bgG, $bgB] = $this->hexToRgb($config['background_color'] ?? '#FFFFFF');

        $enc = match ($config['encryption'] ?? 'WPA2') {
            'WPA', 'WPA2', 'WPA3' => 'WPA',
            'WEP' => 'WEP',
            default => 'nopass',
        };
        $ssid = str_replace(['\\', ';', ',', '"', ':'], ['\\\\', '\\;', '\\,', '\\"', '\\:'], $config['ssid'] ?? '');
        $pass = str_replace(['\\', ';', ',', '"', ':'], ['\\\\', '\\;', '\\,', '\\"', '\\:'], $config['password'] ?? '');
        $hidden = !empty($config['hidden_network']) ? 'true' : 'false';
        $data = "WIFI:T:{$enc};S:{$ssid};P:{$pass};H:{$hidden};;";

        $builder = new Builder(
            writer: $writer,
            data: $data,
            errorCorrectionLevel: $errorLevel,
            size: (int) ($config['size'] ?? 300),
            margin: (int) ($config['margin'] ?? 10),
            foregroundColor: new Color($fgR, $fgG, $fgB),
            backgroundColor: new Color($bgR, $bgG, $bgB),
        );

        return $builder->build();
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }
}
