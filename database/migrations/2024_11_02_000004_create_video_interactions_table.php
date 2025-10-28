<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoInteractionsTable extends Migration
{
    public function up()
    {
        Schema::create('video_interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('video_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['view', 'like', 'share']); 
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('video_id')->references('id')->on('videos')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['video_id', 'user_id', 'type']);
            $table->index(['video_id', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('video_interactions');
    }
}
