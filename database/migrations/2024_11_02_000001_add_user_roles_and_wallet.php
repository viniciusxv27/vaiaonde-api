<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserRolesAndWallet extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['comum', 'assinante', 'proprietario', 'influenciador'])->default('comum')->after('is_admin');
            $table->decimal('wallet_balance', 10, 2)->default(0)->after('economy');
            $table->string('pix_key')->nullable()->after('wallet_balance');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'wallet_balance', 'pix_key']);
        });
    }
}
