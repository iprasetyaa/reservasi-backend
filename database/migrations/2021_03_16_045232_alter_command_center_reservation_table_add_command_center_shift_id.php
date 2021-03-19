<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCommandCenterReservationTableAddCommandCenterShiftId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('command_center_reservations', function (Blueprint $table) {
            $table->dropColumn('shift');
            $table->foreignId('command_center_shift_id')
                ->after('reservation_code')
                ->nullable()
                ->constrained('command_center_shifts')
                ->onUpdate('no action')
                ->onDelete('set null');
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
            $table->dropForeign('command_center_shift_id');
        });
    }
}
