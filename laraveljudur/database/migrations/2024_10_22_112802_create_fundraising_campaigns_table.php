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
        Schema::create('fundraising_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organizer_id');
            $table->string('title');
            $table->text('description');
            $table->decimal('target_amount', 8, 2);
            $table->decimal('raised_amount', 8, 2)->default(0);
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key to the organizer (user or entity)
            $table->foreign('organizer_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('fundraising_campaigns');
    }
};
