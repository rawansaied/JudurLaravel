<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandInspectionsTable extends Migration
{
    public function up()
    {
        Schema::create('land_inspections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('land_id');
            $table->date('date');
            $table->unsignedBigInteger('examiner_id');
            $table->string('hygiene');
            $table->integer('capacity');
            $table->boolean('electricity_supply');
            $table->string('general_condition');
            $table->string('photo_path')->nullable(); 
          
            $table->text('summary')->nullable(); // New column for summary
            $table->json('suggestions')->nullable();
            $table->timestamps();

            $table->foreign('land_id')->references('id')->on('lands');
            $table->foreign('examiner_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('land_inspections');
    }
}
;
