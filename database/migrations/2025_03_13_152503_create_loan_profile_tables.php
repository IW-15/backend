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
        Schema::create('loan_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("id_merchant")->unique();
            $table->unsignedBigInteger("id_user")->unique();
            $table->decimal("maxAmount", 15, 2);
            $table->decimal("minAmount", 15, 2);
            $table->decimal("limit", 15, 2);

            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade')->onUpdate("cascade");
            $table->foreign('id_merchant')->references('id')->on('merchants')->onDelete('cascade')->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_profiles');
    }
};
