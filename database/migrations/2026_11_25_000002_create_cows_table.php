<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cows', function (Blueprint $table) {
            $table->id();
            $table->string('tag_number')->unique();
            $table->string('slug')->unique();
            $table->string('name')->nullable();
            $table->string('breed');
            $table->string('gender', 20);
            $table->date('birth_date')->nullable();
            $table->date('arrived_at');
            $table->unsignedBigInteger('price')->nullable();
            $table->decimal('initial_weight_kg', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status', 20)->default('available');
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'arrived_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cows');
    }
};
