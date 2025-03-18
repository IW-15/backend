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
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("id_user")->unique();
            $table->string("name");
            $table->string("address");
            $table->string("phone");
            $table->string("email");
            $table->string("applyForm")->nullable();
            $table->string("ktp")->nullable();
            $table->string("photo")->nullable();
            $table->string("license")->nullable();
            $table->string("npwp")->nullable();

            $table->timestamps();

            $table->foreign("id_user")->references("id")->on("users")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
