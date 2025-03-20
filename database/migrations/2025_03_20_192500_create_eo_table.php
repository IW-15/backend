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
        Schema::create('eos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("id_user")->unique();
            $table->string("name");
            $table->string("nib");
            $table->string("pic");
            $table->string("picPhone");
            $table->string("email");
            $table->string("address");
            $table->string("document");

            $table->timestamps();

            $table->foreign("id_user")->references("id")->on("users")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eos');
    }
};
