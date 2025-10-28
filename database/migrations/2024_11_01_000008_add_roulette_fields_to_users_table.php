<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRouletteFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('roulette_spins_available')->default(0)->after('ticket_count');
            $table->timestamp('last_daily_spin')->nullable()->after('roulette_spins_available');
            $table->unsignedBigInteger('subscription_plan_id')->nullable()->after('subscription');
            
            $table->foreign('subscription_plan_id')->references('id')->on('subscription_plans')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['subscription_plan_id']);
            $table->dropColumn(['roulette_spins_available', 'last_daily_spin', 'subscription_plan_id']);
        });
    }
}
