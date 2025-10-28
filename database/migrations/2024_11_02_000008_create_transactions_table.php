<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['deposit', 'withdrawal', 'transfer_in', 'transfer_out', 'proposal_payment', 'proposal_refund', 'highlight_purchase']);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->string('description');
            $table->unsignedBigInteger('related_user_id')->nullable(); // Para transferências
            $table->unsignedBigInteger('proposal_id')->nullable();
            $table->string('payment_method')->nullable(); // card, pix, wallet
            $table->string('stripe_charge_id')->nullable(); // ID do Stripe
            $table->string('payment_id')->nullable(); // ID externo genérico
            $table->string('pix_key')->nullable();
            $table->text('error_message')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('related_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('set null');
            
            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
