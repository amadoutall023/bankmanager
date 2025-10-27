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
        Schema::table('accounts', function (Blueprint $table) {
            $table->timestamp('blocked_at')->nullable()->after('status');
            $table->timestamp('blocking_expires_at')->nullable()->after('blocked_at');
            $table->text('blocking_reason')->nullable()->after('blocking_expires_at');
            $table->boolean('is_archived')->default(false)->after('blocking_reason');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['blocked_at', 'blocking_expires_at', 'blocking_reason', 'is_archived', 'archived_at']);
        });
    }
};
