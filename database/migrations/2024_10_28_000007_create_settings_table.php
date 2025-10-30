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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            ['key' => 'app_name', 'value' => 'Vai Aonde', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'contact_email', 'value' => 'contato@vaiaonde.com', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'featured_price', 'value' => '39.90', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'min_withdrawal', 'value' => '20.00', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'maintenance_mode', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
