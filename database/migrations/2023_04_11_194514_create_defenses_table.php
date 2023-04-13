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
        Schema::create('defenses', function (Blueprint $table) {
            $table->id();

            $table->string('egzaminer_name');
            $table->unsignedBigInteger('examinerID')->nullable();

            $table->string('egzaminer2_name');
            $table->unsignedBigInteger('examiner2ID')->nullable();

            $table->string('promoter_name');
            $table->unsignedBigInteger('promoterID')->nullable();

            $table->unsignedBigInteger('CalendarID');
            $table->dateTime('EgzamDate')->nullable();
            $table->string('student');

            $table->timestamps();

            $table->foreign('examinerID')
                ->references('Teacher-ID')
                ->on('teachers')
                ->onDelete('cascade');

            $table->foreign('examiner2ID')
                ->references('Teacher-ID')
                ->on('teachers')
                ->onDelete('cascade');

            $table->foreign('promoterID')
                ->references('Teacher-ID')
                ->on('teachers')
                ->onDelete('cascade');

            $table->foreign('CalendarID')
                ->references('id')
                ->on('calendars')
                ->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defenses');
    }
};