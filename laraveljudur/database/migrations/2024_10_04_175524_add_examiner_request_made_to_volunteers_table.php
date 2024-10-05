<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExaminerRequestMadeToVolunteersTable extends Migration
{
    public function up()
    {
        Schema::table('volunteers', function (Blueprint $table) {
            $table->boolean('examiner_request_made')->default(false); // Tracks whether the request was made
        });
    }

    public function down()
    {
        Schema::table('volunteers', function (Blueprint $table) {
            $table->dropColumn('examiner_request_made');
        });
    }
}