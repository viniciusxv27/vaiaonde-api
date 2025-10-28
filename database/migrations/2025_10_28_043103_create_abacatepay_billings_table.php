<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('abacatepay_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('billing_id')->unique(); // AbacatePay billing ID
            $table->string('type')->default('deposit'); // deposit, subscription, withdrawal
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, paid, failed, cancelled
            $table->text('pix_qr_code')->nullable();
            $table->string('pix_qr_code_url')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abacatepay_billings');
    }
};
