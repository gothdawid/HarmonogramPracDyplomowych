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
        Schema::create('defenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examiner');
            $table->unsignedBigInteger('examiner2');
            $table->unsignedBigInteger('promoter');
            $table->string('student');
            $table->unsignedBigInteger('OwnerID');

            $table->timestamps();

            $table->foreign('OwnerID')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('examiner') 
                ->references('Teacher-ID')
                ->on('teachers')
                ->onDelete('cascade');

            $table->foreign('examiner2')
                ->references('Teacher-ID')
                ->on('teachers')
                ->onDelete('cascade');

            $table->foreign('promoter')
                ->references('Teacher-ID')
                ->on('teachers')
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
