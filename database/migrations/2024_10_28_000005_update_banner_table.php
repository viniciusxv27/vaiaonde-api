<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banner', function (Blueprint $table) {
            // Adicionar novas colunas
            $table->string('title')->after('id');
            $table->string('image_url')->nullable()->after('image');
            $table->string('link')->nullable()->after('image_url');
            $table->boolean('is_active')->default(true)->after('link');
            
            // Tornar a coluna image nullable
            $table->string('image')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('banner', function (Blueprint $table) {
            $table->dropColumn(['title', 'image_url', 'link', 'is_active']);
        });
    }
};
