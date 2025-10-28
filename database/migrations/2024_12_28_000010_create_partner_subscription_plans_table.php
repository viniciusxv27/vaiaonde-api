<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Local, Regional, Destaque
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->json('features'); // Array de recursos
            $table->boolean('can_launch_promotions')->default(false);
            $table->boolean('appears_in_top')->default(false);
            $table->integer('professional_videos_per_month')->default(0);
            $table->boolean('has_analytics')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_subscription_plans');
    }
};
