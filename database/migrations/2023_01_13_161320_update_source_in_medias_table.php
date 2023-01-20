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
        Schema::table('medias', function (Blueprint $table) {
            $table->string('source_uuid')->nullable()->after('media_type');
            $table->renameColumn('media_type', 'source_type');
        });

        \DB::statement("ALTER TABLE medias MODIFY COLUMN source_type ENUM('user_profile', 'user_banner', 'user_post', 'user_event') DEFAULT 'user_profile';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('medias', function (Blueprint $table) {
            //
        });
    }
};
