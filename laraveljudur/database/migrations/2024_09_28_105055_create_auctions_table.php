<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuctionsTable extends Migration
{
    public function up()
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('auction_status_id')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('starting_price', 8, 2);
            $table->string('title');
            $table->text('description');
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('item_donations');
            $table->foreign('auction_status_id')->references('id')->on('auction_statuses');
        });
    }

    public function down()
    {
        Schema::dropIfExists('auctions');
    }
}
;
