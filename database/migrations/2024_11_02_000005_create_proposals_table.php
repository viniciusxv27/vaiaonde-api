<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposalsTable extends Migration
{
    public function up()
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('influencer_id');
            $table->unsignedBigInteger('place_id');
            $table->string('title');
            $table->text('description');
            $table->decimal('amount', 10, 2);
            $table->integer('deadline_days'); // Prazo em dias
            $table->enum('status', ['pending', 'accepted', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('influencer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('place_id')->references('id')->on('place')->onDelete('cascade');
            
            $table->index(['influencer_id', 'status']);
            $table->index(['place_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('proposals');
    }
}
