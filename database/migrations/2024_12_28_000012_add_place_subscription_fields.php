<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('place', function (Blueprint $table) {
            $table->enum('type', ['lugar', 'restaurante', 'evento'])->default('lugar')->after('name');
            $table->foreignId('subscription_id')->nullable()->after('owner_id')->constrained('partner_subscriptions')->onDelete('set null');
            $table->boolean('is_active')->default(true)->after('subscription_id');
            $table->text('deactivation_reason')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('place', function (Blueprint $table) {
            $table->dropColumn(['type', 'subscription_id', 'is_active', 'deactivation_reason']);
        });
    }
};
