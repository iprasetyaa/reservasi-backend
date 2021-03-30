<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCommandCenterShiftTableRenameFixNamingField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('command_center_shifts', function (Blueprint $table) {
            $table->renameColumn('code', 'time');
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
            $table->renameColumn('time', 'code');
        });
    }
}
