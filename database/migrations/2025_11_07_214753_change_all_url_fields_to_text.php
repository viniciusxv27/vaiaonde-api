<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabela videos - alterar video_url e thumbnail_url
        Schema::table('videos', function (Blueprint $table) {
            $table->text('video_url')->change();
            $table->text('thumbnail_url')->nullable()->change();
        });

        // Tabela roulette_prizes - alterar image_url
        Schema::table('roulette_prizes', function (Blueprint $table) {
            $table->text('image_url')->nullable()->change();
        });

        // Tabela banner - alterar image_url e link
        if (Schema::hasColumn('banner', 'image_url')) {
            Schema::table('banner', function (Blueprint $table) {
                $table->text('image_url')->nullable()->change();
            });
        }
        
        if (Schema::hasColumn('banner', 'link')) {
            Schema::table('banner', function (Blueprint $table) {
                $table->text('link')->nullable()->change();
            });
        }

        // Tabela place_images - alterar image
        if (Schema::hasColumn('place_images', 'image')) {
            Schema::table('place_images', function (Blueprint $table) {
                $table->text('image')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para VARCHAR(255)
        Schema::table('videos', function (Blueprint $table) {
            $table->string('video_url', 255)->change();
            $table->string('thumbnail_url', 255)->nullable()->change();
        });

        Schema::table('roulette_prizes', function (Blueprint $table) {
            $table->string('image_url', 255)->nullable()->change();
        });

        if (Schema::hasColumn('banner', 'image_url')) {
            Schema::table('banner', function (Blueprint $table) {
                $table->string('image_url', 255)->nullable()->change();
            });
        }
        
        if (Schema::hasColumn('banner', 'link')) {
            Schema::table('banner', function (Blueprint $table) {
                $table->string('link', 255)->nullable()->change();
            });
        }

        if (Schema::hasColumn('place_images', 'image')) {
            Schema::table('place_images', function (Blueprint $table) {
                $table->string('image', 255)->change();
            });
        }
    }
};
