<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('qr_code_scans', function (Blueprint $table) {
            $table->string('country', 100)->nullable()->after('referer');
            $table->string('country_code', 2)->nullable()->after('country');
            $table->string('region', 100)->nullable()->after('country_code');
            $table->string('city', 100)->nullable()->after('region');
            $table->index('country_code');
        });
    }

    public function down(): void
    {
        Schema::table('qr_code_scans', function (Blueprint $table) {
            $table->dropIndex(['country_code']);
            $table->dropColumn(['country', 'country_code', 'region', 'city']);
        });
    }
};
