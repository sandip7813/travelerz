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
        Schema::create('invitee_move', function (Blueprint $table) {
            $table->unsignedInteger('move_id')->constrained();
            $table->unsignedInteger('invitee_id')->constrained();
            $table->tinyInteger('invite_status')->default(0)->comment('0 = invited, 1 = interested, 2 = going');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invitee_move');
    }
};
