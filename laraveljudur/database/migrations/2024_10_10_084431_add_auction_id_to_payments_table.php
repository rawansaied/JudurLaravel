<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('auction_id')->after('id');
            
            // Optionally, add a foreign key constraint if you want
            $table->foreign('auction_id')->references('id')->on('auctions')->onDelete('cascade');
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['auction_id']);
            $table->dropColumn('auction_id');
        });
    }
    
};
