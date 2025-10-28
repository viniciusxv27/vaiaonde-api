<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('email');
            $table->string('stripe_id')->nullable()->after('payment_id');
            $table->string('promocode')->default('1')->after('stripe_id');
            $table->integer('ticket_count')->default(0)->after('promocode');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'stripe_id', 'promocode', 'ticket_count']);
        });
    }
}
