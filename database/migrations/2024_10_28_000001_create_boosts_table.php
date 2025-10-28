<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // quem pagou
            $table->decimal('amount', 10, 2); // valor total
            $table->integer('days'); // quantidade de dias
            $table->decimal('daily_budget', 10, 2); // valor por dia (amount / days)
            $table->integer('clicks')->default(0); // cliques recebidos
            $table->integer('impressions')->default(0); // visualizações/impressões
            $table->decimal('cpc', 10, 2)->default(0); // custo por clique (amount / clicks)
            $table->decimal('ctr', 10, 2)->default(0); // taxa de clique (clicks / impressions * 100)
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boosts');
    }
};
