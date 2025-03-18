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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("id_loan_profile");
            $table->decimal("amount", 15, 2);
            $table->integer("tenor");
            $table->decimal("monthlyBill", 15, 2);
            $table->date("applicationDate");
            $table->string("status");

            $table->timestamps();

            $table->foreign("id_loan_profile")->references("id")->on("loan_profiles")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
