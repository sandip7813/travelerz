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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('transaction_id')->nullable()->after('provider_id');
            $table->string('membership_uuid')->nullable()->after('provider_id');
            $table->dateTime('membership_expiry')->nullable()->after('provider_id');
            $table->string('membership')->nullable()->after('provider_id');
            $table->string('stripe_customer_id')->nullable()->after('provider_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
