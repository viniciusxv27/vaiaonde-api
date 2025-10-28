<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoulettePrizesTable extends Migration
{
    public function up()
    {
        Schema::create('roulette_prizes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('type'); // voucher, points, discount, free_item, cashback
            $table->string('prize_value')->nullable(); // Valor do prêmio
            $table->unsignedBigInteger('voucher_id')->nullable(); // Se for voucher
            $table->integer('points_value')->nullable(); // Se for pontos
            $table->decimal('discount_value', 10, 2)->nullable(); // Se for desconto
            $table->string('image_url')->nullable();
            $table->string('color')->default('#FFD700'); // Cor no círculo da roleta
            $table->integer('probability')->default(10); // Probabilidade em % (1-100)
            $table->integer('quantity')->nullable(); // Quantidade disponível (null = ilimitado)
            $table->integer('quantity_used')->default(0);
            $table->boolean('active')->default(true);
            $table->boolean('club_exclusive')->default(false); // Exclusivo para membros
            $table->timestamps();

            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('roulette_prizes');
    }
}
