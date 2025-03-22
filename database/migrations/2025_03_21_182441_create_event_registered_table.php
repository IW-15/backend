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
        Schema::create('event_registered', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("id_eo");
            $table->unsignedBigInteger("id_event");
            $table->unsignedBigInteger("id_sme");
            $table->unsignedBigInteger("id_outlet");
            $table->enum("status", ["rejected", "received", "waiting", "accepted"])->default("received");
            $table->enum("score", ["high", "medium", "low"]);
            $table->date("date");

            $table->timestamps();

            $table->foreign("id_eo")->references("id")->on("eos")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("id_event")->references("id")->on("events")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("id_sme")->references("id")->on("merchants")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("id_outlet")->references("id")->on("outlets")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registered');
    }
};
