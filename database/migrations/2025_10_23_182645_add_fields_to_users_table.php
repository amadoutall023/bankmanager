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
            $table->string('cni')->unique()->nullable()->after('email');
            $table->string('phone')->nullable()->after('cni');
            $table->text('address')->nullable()->after('phone');
            $table->enum('role', ['admin', 'client'])->default('client')->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cni', 'phone', 'address', 'role']);
        });
    }
};
