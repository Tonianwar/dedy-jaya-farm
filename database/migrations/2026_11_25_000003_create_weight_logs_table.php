<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weight_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cow_id')->constrained()->cascadeOnDelete();
            $table->date('weighed_at');
            $table->decimal('weight_kg', 8, 2);
            $table->text('note')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->index(['cow_id', 'weighed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weight_logs');
    }
};
