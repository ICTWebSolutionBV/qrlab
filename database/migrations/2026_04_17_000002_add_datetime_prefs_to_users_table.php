<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('timezone', 64)->default('Europe/Amsterdam')->after('theme_preference');
            $table->string('date_format', 32)->default('DD-MM-YYYY')->after('timezone');
            $table->string('time_format', 32)->default('HH:mm:ss')->after('date_format');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['timezone', 'date_format', 'time_format']);
        });
    }
};
