<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            // Text markup + font for header
            $table->string('header_font_family', 50)->default('Inter')->after('header_margin');
            $table->boolean('header_bold')->default(false)->after('header_font_family');
            $table->boolean('header_italic')->default(false)->after('header_bold');
            $table->boolean('header_underline')->default(false)->after('header_italic');

            // Text markup + font for footer
            $table->string('footer_font_family', 50)->default('Inter')->after('footer_margin');
            $table->boolean('footer_bold')->default(false)->after('footer_font_family');
            $table->boolean('footer_italic')->default(false)->after('footer_bold');
            $table->boolean('footer_underline')->default(false)->after('footer_italic');

            // WiFi info display block
            $table->boolean('show_wifi_details')->default(false)->after('footer_underline');
            $table->string('wifi_details_font_size', 5)->default('14')->after('show_wifi_details');
            $table->string('wifi_details_color', 7)->default('#000000')->after('wifi_details_font_size');
            $table->string('wifi_details_alignment', 10)->default('center')->after('wifi_details_color');
            $table->string('wifi_details_password_font', 50)->default('Roboto Mono')->after('wifi_details_alignment');
            $table->boolean('wifi_details_show_password')->default(true)->after('wifi_details_password_font');
            $table->integer('wifi_details_margin_top')->default(8)->after('wifi_details_show_password');
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropColumn([
                'header_font_family', 'header_bold', 'header_italic', 'header_underline',
                'footer_font_family', 'footer_bold', 'footer_italic', 'footer_underline',
                'show_wifi_details', 'wifi_details_font_size', 'wifi_details_color',
                'wifi_details_alignment', 'wifi_details_password_font',
                'wifi_details_show_password', 'wifi_details_margin_top',
            ]);
        });
    }
};
