<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('featured_places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained('place')->onDelete('cascade');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['place_id', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_places');
    }
};
