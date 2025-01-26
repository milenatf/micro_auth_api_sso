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
        Schema::create('providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Cria a chave estrangeira e a relaciona com a tabela `users`
            $table->string('provider_id')->nullable();
            $table->string('provider_name')->nullable();
            $table->string('provider_nickname')->nullable();
            $table->string('provider_avatar')->nullable();
            $table->text('id_token')->nullable();
            $table->text('provider_token')->nullable();
            $table->text('provider_refresh_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
