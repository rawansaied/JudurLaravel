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
        Schema::table('lands', function (Blueprint $table) {
            $table->date('availability_time')->nullable();  // Allow the column to be nullable
        });
    }
    
    public function down()
    {
        Schema::table('lands', function (Blueprint $table) {
            $table->dropColumn('availability_time'); // Rollback the column if the migration is reversed
        });
    }
};
