<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livestock_tags', function (Blueprint $table) {
            $table->string('slug', 80)->nullable()->unique();
        });

        $usedSlugs = [];

        DB::table('livestock_tags')->orderBy('id')->get(['id', 'code'])->each(function ($tag) use (&$usedSlugs) {
            $base = preg_replace('/^qr-/i', '', $tag->code);
            $slug = Str::slug($base ?: 'tag-'.$tag->id);
            $candidate = $slug;
            $suffix = 2;

            while (isset($usedSlugs[$candidate])) {
                $candidate = $slug.'-'.$suffix;
                $suffix++;
            }

            $usedSlugs[$candidate] = true;
            DB::table('livestock_tags')->where('id', $tag->id)->update(['slug' => $candidate]);
        });
    }

    public function down(): void
    {
        Schema::table('livestock_tags', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
