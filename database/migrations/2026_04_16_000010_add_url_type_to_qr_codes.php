<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->string('type', 20)->default('wifi')->after('name');
            $table->string('url')->nullable()->after('type');
            $table->string('ssid')->nullable()->change();
            $table->string('encryption', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropColumn(['type', 'url']);
            $table->string('ssid')->nullable(false)->change();
            $table->string('encryption', 20)->nullable(false)->change();
        });
    }
};
