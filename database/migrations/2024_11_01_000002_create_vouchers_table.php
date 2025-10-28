<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('place_id');
            $table->string('title');
            $table->text('description');
            $table->string('discount_type'); // percentage, fixed, free_item
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->string('code')->unique();
            $table->integer('max_uses')->nullable(); // null = ilimitado
            $table->integer('uses_count')->default(0);
            $table->date('valid_from');
            $table->date('valid_until');
            $table->boolean('active')->default(true);
            $table->boolean('club_exclusive')->default(false); // Exclusivo para membros do clube
            $table->timestamps();

            $table->foreign('place_id')->references('id')->on('place')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
}
