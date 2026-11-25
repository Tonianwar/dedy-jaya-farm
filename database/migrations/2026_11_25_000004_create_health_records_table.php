<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cow_id')->constrained()->cascadeOnDelete();
            $table->date('recorded_at');
            $table->string('category', 30);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('youtube_url')->nullable();
            $table->timestamps();

            $table->index(['cow_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
