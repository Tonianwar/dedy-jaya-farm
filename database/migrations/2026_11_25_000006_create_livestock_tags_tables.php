<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livestock_tags', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('token', 40)->unique();
            $table->foreignId('cow_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('livestock_tag_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livestock_tag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cow_id')->constrained()->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['livestock_tag_id', 'released_at']);
            $table->index(['cow_id', 'released_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livestock_tag_assignments');
        Schema::dropIfExists('livestock_tags');
    }
};
