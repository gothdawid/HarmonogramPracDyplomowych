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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Departament-ID');
            $table->string('Teacher-Name');
            $table->string('Jednostka');
            $table->string('Jednostka-en');
            $table->unsignedBigInteger('Plan-ID');
            $table->integer('DAY');
            $table->integer('OD_GODZ');
            $table->integer('DO_GODZ');
            $table->string('G_OD');
            $table->string('G_DO');
            $table->string('NAME');
            $table->string('NAME_EN');
            $table->unsignedBigInteger('ID_KALENDARZ');
            $table->string('TERMIN_K');
            $table->timestamps();

            $table->foreign('Departament-ID')
                ->references('Departament-ID')
                ->on('departments')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};