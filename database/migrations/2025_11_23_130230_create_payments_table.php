<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_payments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('hitpay_payment_id')->nullable(); // payment-request id from HitPay
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('PHP');
            $table->string('email')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->json('meta')->nullable(); // store whole hitpay response if needed
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
