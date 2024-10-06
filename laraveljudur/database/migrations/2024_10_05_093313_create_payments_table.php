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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_payment_id')->unique(); 
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->integer('amount'); 
            $table->string('currency'); 
            $table->string('status'); 
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
