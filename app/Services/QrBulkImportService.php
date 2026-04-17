<?php

namespace App\Services;

use App\Models\QrCode;
use App\Models\QrCodeBatch;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class QrBulkImportService
{
    /**
     * Column definitions for each QR type. Each entry is:
     *   column => [label, required, example]
     *
     * @var array<string, array<string, array{0:string,1:bool,2:string}>>
     */
    public const SCHEMAS = [
        'url' => [
            'name'             => ['Name', true,  'Marketing landing'],
            'url'              => ['URL', true,  'https://example.com/landing'],
            'tracking_enabled' => ['Tracking (yes/no)', false, 'yes'],
        ],
        'wifi' => [
            'name'           => ['Name', true,  'Office WiFi'],
            'ssid'           => ['SSID', true,  'OfficeNet'],
            'password'       => ['Password', false, 'supersecret'],
            'encryption'     => ['Encryption (WPA/WPA2/WPA3/WEP/nopass)', false, 'WPA2'],
            'hidden_network' => ['Hidden network (yes/no)', false, 'no'],
        ],
        'phone' => [
            'name'  => ['Name', true, 'Sales line'],
            'phone' => ['Phone number', true, '+31201234567'],
        ],
        'vcard' => [
            'name'       => ['Name', true, 'Jordan Hayes'],
            'first_name' => ['First name', false, 'Jordan'],
            'last_name'  => ['Last name', false, 'Hayes'],
            'company'    => ['Company', false, 'Northwind Labs'],
            'job_title'  => ['Job title', false, 'Product Manager'],
            'email'      => ['Email', false, 'jordan@example.com'],
            'mobile'     => ['Mobile', false, '+14155551234'],
            'phone'      => ['Phone', false, '+14155550100'],
            'website'    => ['Website', false, 'https://example.com'],
        ],
        'email' => [
            'name'    => ['Name', true, 'Support inbox'],
            'email'   => ['Email', true, 'support@example.com'],
            'subject' => ['Subject', false, 'Help needed'],
            'body'    => ['Body', false, 'Hi team, '],
        ],
    ];

    public function hasType(string $type): bool
    {
        return array_key_exists($type, self::SCHEMAS);
    }

    /**
     * Generate a CSV template string for the given type.
     */
    public function template(string $type): string
    {
        $schema = self::SCHEMAS[$type];
        $headers = array_keys($schema);
        $example = array_map(fn ($c) => $schema[$c][2], $headers);

        $fh = fopen('php://temp', 'r+');
        fputcsv($fh, $headers);
        fputcsv($fh, $example);
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv;
    }

    /**
     * Parse an uploaded file and return rows + validation errors.
     *
     * @return array{rows: array<int, array<string,mixed>>, errors: array<int, array<int,string>>, headers: array<int,string>, total: int}
     */
    public function parse(string $type, UploadedFile $file): array
    {
        $schema = self::SCHEMAS[$type];
        $expected = array_keys($schema);

        $fh = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($fh);
        if ($headers === false) {
            return ['rows' => [], 'errors' => [['General' => 'The uploaded file is empty.']], 'headers' => [], 'total' => 0];
        }
        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $headers);

        $rows = [];
        $errors = [];
        $lineNumber = 1;
        while (($data = fgetcsv($fh)) !== false) {
            $lineNumber++;
            if (count(array_filter($data, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue; // skip empty rows
            }

            $row = [];
            foreach ($headers as $i => $h) {
                $row[$h] = $data[$i] ?? null;
            }

            $rowErrors = [];
            foreach ($schema as $col => [$label, $required]) {
                $value = $row[$col] ?? null;
                if ($required && ($value === null || trim((string) $value) === '')) {
                    $rowErrors[] = "{$label} is required";
                }
            }

            // Type-specific checks
            if ($type === 'url' && !empty($row['url'])) {
                $url = trim((string) $row['url']);
                if (!preg_match('~^https?://~i', $url)) $url = 'https://' . $url;
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    $rowErrors[] = 'URL is not a valid URL';
                } else {
                    $row['url'] = $url;
                }
            }
            if ($type === 'email' && !empty($row['email']) && !filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = 'Email is not a valid email';
            }
            if ($type === 'phone' && !empty($row['phone']) && !preg_match('/^[+\d\s\-().]+$/', (string) $row['phone'])) {
                $rowErrors[] = 'Phone number contains invalid characters';
            }

            $rows[] = $row;
            if ($rowErrors) $errors[$lineNumber] = $rowErrors;
        }
        fclose($fh);

        // Reject if required headers are missing
        $missing = [];
        foreach ($schema as $col => [$label, $required]) {
            if ($required && !in_array($col, $headers, true)) $missing[] = $col;
        }
        if ($missing) {
            $errors[0] = ['Missing required column(s): ' . implode(', ', $missing)];
        }

        return [
            'rows' => $rows,
            'errors' => $errors,
            'headers' => $headers,
            'total' => count($rows),
        ];
    }

    /**
     * Import rows into a new batch. Assumes rows were previously validated.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function import(User $user, string $type, string $batchName, array $rows): QrCodeBatch
    {
        $batch = QrCodeBatch::create([
            'user_id' => $user->id,
            'name' => $batchName ?: ucfirst($type) . ' batch',
            'type' => $type,
        ]);

        foreach ($rows as $row) {
            $this->createQrFromRow($user, $batch, $type, $row);
        }

        return $batch;
    }

    private function createQrFromRow(User $user, QrCodeBatch $batch, string $type, array $row): QrCode
    {
        $base = [
            'user_id' => $user->id,
            'batch_id' => $batch->id,
            'type' => $type,
            'name' => (string) ($row['name'] ?? ''),
            'foreground_color' => '#000000',
            'background_color' => '#FFFFFF',
            'dot_style' => 'square',
            'corner_square_style' => 'square',
            'corner_dot_style' => 'square',
            'error_correction' => 'M',
            'size' => 300,
            'margin' => 10,
        ];

        switch ($type) {
            case 'url':
                $base['url'] = (string) $row['url'];
                $base['tracking_enabled'] = $this->toBool($row['tracking_enabled'] ?? false);
                if ($base['tracking_enabled']) $base['short_code'] = Str::random(8);
                break;
            case 'phone':
                $base['url'] = (string) $row['phone'];
                break;
            case 'wifi':
                $base['ssid'] = (string) $row['ssid'];
                $base['password'] = $row['password'] ?? null;
                $base['encryption'] = $this->normalizeEncryption($row['encryption'] ?? 'WPA2');
                $base['hidden_network'] = $this->toBool($row['hidden_network'] ?? false);
                break;
            case 'vcard':
                $base['vcard_data'] = array_filter([
                    'first_name' => $row['first_name'] ?? null,
                    'last_name'  => $row['last_name'] ?? null,
                    'company'    => $row['company'] ?? null,
                    'job_title'  => $row['job_title'] ?? null,
                    'email'      => $row['email'] ?? null,
                    'mobile'     => $row['mobile'] ?? null,
                    'phone'      => $row['phone'] ?? null,
                    'website'    => $row['website'] ?? null,
                ], fn ($v) => $v !== null && $v !== '');
                break;
            case 'email':
                $base['email_data'] = array_filter([
                    'email'   => $row['email'] ?? null,
                    'subject' => $row['subject'] ?? null,
                    'body'    => $row['body'] ?? null,
                ], fn ($v) => $v !== null && $v !== '');
                break;
        }

        return QrCode::create($base);
    }

    private function toBool($v): bool
    {
        if (is_bool($v)) return $v;
        $v = strtolower(trim((string) $v));
        return in_array($v, ['1', 'true', 'yes', 'y', 'on'], true);
    }

    private function normalizeEncryption($v): string
    {
        $v = strtoupper(trim((string) $v));
        return in_array($v, ['WPA', 'WPA2', 'WPA3', 'WEP', 'NOPASS'], true) ? $v : 'WPA2';
    }
}
