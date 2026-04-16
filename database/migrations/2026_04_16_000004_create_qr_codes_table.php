<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');

            // WiFi config
            $table->string('ssid');
            $table->string('password')->nullable();
            $table->string('encryption', 20)->default('WPA2');
            $table->boolean('hidden_network')->default(false);

            // Styling
            $table->string('foreground_color', 7)->default('#000000');
            $table->string('background_color', 7)->default('#FFFFFF');
            $table->string('dot_style', 30)->default('square');
            $table->string('corner_square_style', 30)->default('square');
            $table->string('corner_dot_style', 30)->default('square');
            $table->string('error_correction', 1)->default('M');
            $table->integer('size')->default(300);
            $table->integer('margin')->default(10);

            // Text/Labels
            $table->string('header_text')->nullable();
            $table->string('footer_text')->nullable();

            // Logo
            $table->string('logo_path', 500)->nullable();
            $table->integer('logo_size')->nullable();

            // Frame
            $table->string('frame_style', 30)->nullable();
            $table->string('frame_color', 7)->nullable();
            $table->string('frame_text', 100)->nullable();

            // Tracking
            $table->boolean('tracking_enabled')->default(false);
            $table->string('short_code', 12)->unique()->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
