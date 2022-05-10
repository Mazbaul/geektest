<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->biginteger('sender_user_id');
            $table->string('sender_currency', 3);
            $table->double('sending_amount');
            $table->biginteger('receiver_user_id');
            $table->string('receiver_currency', 3);
            $table->double('receiving_amount');
            $table->timestamp('transaction_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
