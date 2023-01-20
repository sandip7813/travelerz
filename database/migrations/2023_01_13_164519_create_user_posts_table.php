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
        Schema::create('user_posts', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('user_id')->constrained();
            $table->text('content')->nullable();
            $table->string('location')->nullable();
            $table->double('latitude', 11, 8)->nullable();
            $table->double('longitude', 11, 8)->nullable();
            $table->enum('status', [0, 1, 2])->nullable()->default(0)->comment('0 = drafted, 1 = active, 2 = blocked');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_posts');
    }
};
