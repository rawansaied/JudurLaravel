<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToAuctionsTable extends Migration
{
    public function up()
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description'); 
        });
    }

    public function down()
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
}
