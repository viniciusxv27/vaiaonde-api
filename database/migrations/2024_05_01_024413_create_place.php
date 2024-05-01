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
        Schema::create('place', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('card_image');
            $table->string('review');
            $table->string('categories_ids');
            $table->tinyInteger('city_id');
            $table->string('logo');
            $table->string('instagram_url');
            $table->string('phone');
            $table->string('location_url');
            $table->string('location');
            $table->string('uber_url', 500);
            $table->boolean('ticket');
            $table->float('ticket_value');
            $table->integer('ticket_count');
            $table->boolean('hidden');
            $table->tinyInteger('tipe_id');
            $table->boolean('top');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place');
    }
};
