<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancialsTable extends Migration
{
    public function up()
    {
        Schema::create('financials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('donor_id');
            $table->decimal('amount', 8, 2);
            $table->string('currency');
            $table->string('payment_method');
            $table->timestamps();

            $table->foreign('donor_id')->references('id')->on('donors');
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->foreign('campaign_id')->references('id')->on('fundraising_campaigns');
        });
    }

    public function down()
    {
        Schema::dropIfExists('financials');
    }
};
