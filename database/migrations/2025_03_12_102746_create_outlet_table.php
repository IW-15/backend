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
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("id_revenue");
            $table->unsignedBigInteger("id_merchant");
            $table->unsignedBigInteger("id_user");
            $table->string("name");
            $table->string("type");
            $table->string("phone");
            $table->string("email");
            $table->string("rekening");
            $table->string("address");

            $table->timestamps();

            $table->foreign("id_revenue")->references("id")->on("outlet_revenues")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("id_merchant")->references("id")->on("merchants")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("id_user")->references("id")->on("users")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};
