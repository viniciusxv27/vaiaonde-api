<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPlansTable extends Migration
{
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // VáClub Mensal, VáClub Anual
            $table->string('slug')->unique(); // monthly, annual, quarterly
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable(); // Preço original (para mostrar desconto)
            $table->string('period'); // month, year, quarter
            $table->integer('period_count')->default(1); // 1 mês, 12 meses, etc
            $table->string('stripe_price_id')->nullable();
            $table->json('features')->nullable(); // Array de benefícios
            $table->integer('roulette_spins_per_month')->default(1); // Giros de roleta por mês
            $table->integer('priority_support')->default(0); // Nível de suporte
            $table->boolean('active')->default(true);
            $table->boolean('is_popular')->default(false); // Marcar como mais popular
            $table->integer('order')->default(0); // Ordem de exibição
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_plans');
    }
}
