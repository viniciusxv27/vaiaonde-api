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
        Schema::table('place', function (Blueprint $table) {
            // Alterar todos os campos de URL/link para TEXT (sem limite de tamanho)
            $table->text('instagram_url')->nullable()->change();
            $table->text('location_url')->nullable()->change();
            $table->text('uber_url')->nullable()->change();
            $table->text('card_image')->nullable()->change();
            $table->text('logo')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('place', function (Blueprint $table) {
            // Reverter para VARCHAR(255)
            $table->string('instagram_url', 255)->nullable()->change();
            $table->string('location_url', 255)->nullable()->change();
            $table->string('uber_url', 500)->nullable()->change();
            $table->string('card_image', 255)->nullable()->change();
            $table->string('logo', 255)->nullable()->change();
        });
    }
};
