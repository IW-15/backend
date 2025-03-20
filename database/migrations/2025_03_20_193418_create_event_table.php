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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("id_eo");
            $table->string("name");
            $table->date("date");
            $table->time("time");
            $table->enum("category", ["Bazaar", "Festival Makanan", "Konser", "Pameran"]);
            $table->string("location");
            $table->double("latitude");
            $table->double("longitude");
            $table->enum("venue", ["Indoor", "Outdoor"]);
            $table->integer("visitorNumber");
            $table->integer("tenantNumber");
            $table->decimal('tenantPrice', 15, 2);
            $table->string("description");
            $table->enum("status", ["draft", "published"])->default("draft");
            $table->string("pic");
            $table->string("banner");
            $table->string("picNumber");

            $table->timestamps();

            $table->foreign("id_eo")->references("id")->on("eos")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
