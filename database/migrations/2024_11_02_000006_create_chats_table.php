<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsTable extends Migration
{
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('influencer_id');
            $table->unsignedBigInteger('place_id');
            $table->unsignedBigInteger('proposal_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('influencer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('place_id')->references('id')->on('place')->onDelete('cascade');
            $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('set null');
            
            $table->unique(['influencer_id', 'place_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('chats');
    }
}
