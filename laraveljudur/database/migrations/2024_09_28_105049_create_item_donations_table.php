<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemDonationsTable extends Migration
{
    public function up()
    {
        Schema::create('item_donations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('donor_id');
            $table->string('item_name');
            $table->decimal('value', 8, 2);
            $table->boolean('is_valuable');
            $table->string('condition');
            $table->unsignedBigInteger('status_id');
            $table->timestamps();

            $table->foreign('donor_id')->references('id')->on('donors');
            $table->foreign('status_id')->references('id')->on('item_statuses');
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_donations');
    }
}

;
