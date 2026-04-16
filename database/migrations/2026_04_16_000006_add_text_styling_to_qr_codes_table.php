<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->string('header_font_size', 10)->default('16')->after('header_text');
            $table->string('header_color', 7)->default('#000000')->after('header_font_size');
            $table->string('header_alignment', 10)->default('center')->after('header_color');
            $table->integer('header_margin')->default(8)->after('header_alignment');

            $table->string('footer_font_size', 10)->default('14')->after('footer_text');
            $table->string('footer_color', 7)->default('#000000')->after('footer_font_size');
            $table->string('footer_alignment', 10)->default('center')->after('footer_color');
            $table->integer('footer_margin')->default(8)->after('footer_alignment');
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropColumn([
                'header_font_size', 'header_color', 'header_alignment', 'header_margin',
                'footer_font_size', 'footer_color', 'footer_alignment', 'footer_margin',
            ]);
        });
    }
};
