<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandsTable extends Migration
{
    public function up()
    {
        Schema::create('lands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('donor_id');
            $table->text('description');
            $table->decimal('land_size', 8, 2);
            $table->string('address');
            $table->string('proof_of_ownership');
            $table->unsignedBigInteger('status_id');
            $table->timestamps();

            $table->foreign('donor_id')->references('id')->on('donors');
            $table->foreign('status_id')->references('id')->on('land_statuses');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lands');
    }
}

;
