<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->string('wifi_details_font_family', 50)->default('Inter')->after('wifi_details_margin_top');
            $table->boolean('wifi_details_bold')->default(false)->after('wifi_details_font_family');
            $table->boolean('wifi_details_italic')->default(false)->after('wifi_details_bold');
            $table->boolean('wifi_details_underline')->default(false)->after('wifi_details_italic');
            $table->integer('wifi_details_margin')->default(8)->after('wifi_details_underline');
            $table->boolean('wifi_details_margin_custom')->default(false)->after('wifi_details_margin');
            $table->integer('wifi_details_margin_right')->default(0)->after('wifi_details_margin_custom');
            $table->integer('wifi_details_margin_bottom')->default(8)->after('wifi_details_margin_right');
            $table->integer('wifi_details_margin_left')->default(0)->after('wifi_details_margin_bottom');
            $table->boolean('wifi_details_password_separate_style')->default(false)->after('wifi_details_margin_left');
            $table->string('wifi_details_password_font_size', 5)->default('14')->after('wifi_details_password_separate_style');
            $table->boolean('wifi_details_password_bold')->default(false)->after('wifi_details_password_font_size');
            $table->boolean('wifi_details_password_italic')->default(false)->after('wifi_details_password_bold');
            $table->boolean('wifi_details_password_underline')->default(false)->after('wifi_details_password_italic');
            $table->string('wifi_details_password_color', 7)->default('#000000')->after('wifi_details_password_underline');
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropColumn([
                'wifi_details_font_family',
                'wifi_details_bold', 'wifi_details_italic', 'wifi_details_underline',
                'wifi_details_margin', 'wifi_details_margin_custom',
                'wifi_details_margin_right', 'wifi_details_margin_bottom', 'wifi_details_margin_left',
                'wifi_details_password_separate_style',
                'wifi_details_password_font_size',
                'wifi_details_password_bold', 'wifi_details_password_italic',
                'wifi_details_password_underline', 'wifi_details_password_color',
            ]);
        });
    }
};
