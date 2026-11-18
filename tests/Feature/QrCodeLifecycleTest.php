<?php

namespace Tests\Feature;

use App\Models\QrCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// End-to-end guard for the QR rendering stack (endroid/qr-code) across dependency and PHP upgrades.
class QrCodeLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.two_factor_enabled' => false]);
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->create());
    }

    public function test_a_wifi_qr_code_can_be_created_and_exported(): void
    {
        $this->post('/qr', [
            'type' => 'wifi',
            'name' => 'Lifecycle test',
            'ssid' => 'Office',
            'password' => 'secret;pass',
            'encryption' => 'WPA2',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $qr = QrCode::sole();

        $png = $this->get("/qr/{$qr->id}/export/png")->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertStringStartsWith("\x89PNG", $png->getContent());

        $svg = $this->get("/qr/{$qr->id}/export/svg")->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $svg->getContent());

        $eps = $this->get("/qr/{$qr->id}/export/eps")->assertOk()->assertHeader('Content-Type', 'application/postscript');
        $this->assertStringStartsWith('%!PS', $eps->getContent());

        $this->get("/qr/{$qr->id}/edit")->assertOk();
    }

    public function test_a_tracked_url_qr_code_redirects_and_records_the_scan(): void
    {
        $this->post('/qr', [
            'type' => 'url',
            'name' => 'Tracked link',
            'url' => 'https://example.com/landing',
            'tracking_enabled' => true,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $qr = QrCode::sole();
        $this->assertNotNull($qr->short_code);

        $this->get("/r/{$qr->short_code}")->assertRedirect('https://example.com/landing');
        $this->assertSame(1, $qr->scans()->count());
        $this->get("/qr/{$qr->id}/analytics")->assertOk();
    }
}
