<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHighestBidColumnsToAuctionsTable extends Migration
{
    public function up()
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->decimal('current_highest_bid', 8, 2)->nullable()->after('starting_price');
            $table->unsignedBigInteger('highest_bidder_id')->nullable()->after('current_highest_bid');
            $table->foreign('highest_bidder_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropForeign(['highest_bidder_id']);
            $table->dropColumn('current_highest_bid');
            $table->dropColumn('highest_bidder_id');
        });
    }
}
