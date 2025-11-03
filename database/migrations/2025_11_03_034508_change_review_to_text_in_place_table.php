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
            // Mudar review de varchar para text (permite textos longos)
            $table->text('review')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('place', function (Blueprint $table) {
            // Reverter para varchar(255)
            $table->string('review', 255)->change();
        });
    }
};
