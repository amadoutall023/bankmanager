<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('account_number')->unique();
            $table->enum('type', ['epargne', 'cheque'])->default('cheque');
            $table->decimal('balance', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'closed'])->default('active');
            $table->timestamps();
            $table->softDeletes(); // pour ne pas supprimer définitivement
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
