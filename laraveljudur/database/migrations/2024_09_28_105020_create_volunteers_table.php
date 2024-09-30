<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVolunteersTable extends Migration
{
    public function up()
    {
        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('skills');
            $table->string('availability');
            $table->unsignedBigInteger('volunteer_status')->nullable();

            $table->string('aim');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('volunteer_status')->references('id')->on('volunteer_statuses');

        });
    }

    public function down()
    {
        Schema::dropIfExists('volunteers');
    }
};
