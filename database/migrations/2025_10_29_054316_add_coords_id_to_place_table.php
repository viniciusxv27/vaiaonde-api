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
            $table->unsignedBigInteger('coords_id')->nullable()->after('tipe_id');
            $table->foreign('coords_id')->references('id')->on('coords')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('place', function (Blueprint $table) {
            $table->dropForeign(['coords_id']);
            $table->dropColumn('coords_id');
        });
    }
};
