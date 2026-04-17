<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_code_batches', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type'); // wifi, url, phone, vcard, email
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'created_at']);
        });

        Schema::table('qr_codes', function (Blueprint $table) {
            $table->foreignUlid('batch_id')->nullable()->after('user_id')
                ->constrained('qr_code_batches')->nullOnDelete();
            $table->index(['user_id', 'batch_id']);
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropIndex(['user_id', 'batch_id']);
            $table->dropColumn('batch_id');
        });

        Schema::dropIfExists('qr_code_batches');
    }
};
