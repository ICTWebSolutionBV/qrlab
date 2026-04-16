<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->boolean('header_margin_custom')->default(false)->after('header_underline');
            $table->integer('header_margin_top')->default(8)->after('header_margin_custom');
            $table->integer('header_margin_right')->default(0)->after('header_margin_top');
            $table->integer('header_margin_bottom')->default(8)->after('header_margin_right');
            $table->integer('header_margin_left')->default(0)->after('header_margin_bottom');

            $table->boolean('footer_margin_custom')->default(false)->after('footer_underline');
            $table->integer('footer_margin_top')->default(8)->after('footer_margin_custom');
            $table->integer('footer_margin_right')->default(0)->after('footer_margin_top');
            $table->integer('footer_margin_bottom')->default(8)->after('footer_margin_right');
            $table->integer('footer_margin_left')->default(0)->after('footer_margin_bottom');
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropColumn([
                'header_margin_custom', 'header_margin_top', 'header_margin_right',
                'header_margin_bottom', 'header_margin_left',
                'footer_margin_custom', 'footer_margin_top', 'footer_margin_right',
                'footer_margin_bottom', 'footer_margin_left',
            ]);
        });
    }
};
