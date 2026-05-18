<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('title');
            $table->string('file_path');
            $table->string('file_type', 20);     // pdf, doc, image, certificate
            $table->unsignedInteger('file_size'); // bytes
            $table->text('description')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'file_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_documents');
    }
};