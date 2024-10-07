<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageColumnToItemDonationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_donations', function (Blueprint $table) {
            $table->string('image')->nullable(); // Adding the image column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_donations', function (Blueprint $table) {
            $table->dropColumn('image'); // Dropping the image column
        });
    }
}
