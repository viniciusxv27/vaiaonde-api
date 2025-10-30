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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('pix_key');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('bio');
            }
            if (!Schema::hasColumn('users', 'instagram_url')) {
                $table->string('instagram_url')->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'youtube_url')) {
                $table->string('youtube_url')->nullable()->after('instagram_url');
            }
            if (!Schema::hasColumn('users', 'tiktok_url')) {
                $table->string('tiktok_url')->nullable()->after('youtube_url');
            }
            if (!Schema::hasColumn('users', 'twitter_url')) {
                $table->string('twitter_url')->nullable()->after('tiktok_url');
            }
            if (!Schema::hasColumn('users', 'balance')) {
                $table->decimal('balance', 10, 2)->default(0)->after('twitter_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['bio', 'avatar', 'instagram_url', 'youtube_url', 'tiktok_url', 'twitter_url', 'balance'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
