<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained('folders')->nullOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('disk', 50)->default('local');
            $table->string('path');
            $table->string('mime_type', 150)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('hash', 64)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'folder_id']);
            $table->index(['user_id', 'hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
