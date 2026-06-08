<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('short_link_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('short_link_id')->constrained('short_links')->cascadeOnDelete();
            $table->timestamp('clicked_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamps();

            $table->index(['short_link_id', 'clicked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_link_clicks');
    }
};
