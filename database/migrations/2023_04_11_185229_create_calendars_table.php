<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->dateTime('time');
            $table->unsignedBigInteger('OwnerID');
            $table->unsignedBigInteger('DefenseID');
            $table->timestamps();

            $table->foreign('OwnerID')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');



            $table->foreign('DefenseID')
                ->references('id')
                ->on('defenses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendars');
    }
};