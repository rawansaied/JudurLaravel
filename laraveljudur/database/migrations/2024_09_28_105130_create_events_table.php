<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('land_id');
            $table->text('description');
            $table->date('date');
            $table->time('time');
            $table->integer('expected_organizer_number');
            $table->unsignedBigInteger('event_status');
            $table->string('image')->nullable(); 
            $table->string('location')->nullable(); 
            $table->timestamps();
            $table->string('duration')->nullable();
            $table->foreign('land_id')->references('id')->on('lands');

            $table->foreign('event_status')->references('id')->on('event_statuses')->onDelete('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('events');
    }
}
;
