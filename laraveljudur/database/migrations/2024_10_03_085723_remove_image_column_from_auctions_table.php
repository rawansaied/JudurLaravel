<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveImageColumnFromAuctionsTable extends Migration
{
    /**
     * Run the migrations to remove the image column.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropColumn('image');  // Remove the image column
        });
    }

    /**
     * Reverse the migrations to add the image column back.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->string('image')->nullable();  // Add the image column back in case of rollback
        });
    }
}
