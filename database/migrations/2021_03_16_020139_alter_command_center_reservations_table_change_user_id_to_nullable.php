<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCommandCenterReservationsTableChangeUserIdToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('command_center_reservations', function (Blueprint $table) {
            $table->uuid('user_id_reservation')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('command_center_reservations', function (Blueprint $table) {
            $table->uuid('user_id_reservation')->change();
        });
    }
}
