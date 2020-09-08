<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTeamOptions2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->renameColumn('option_shift_request_autoapproval', 'setting_shift_request_autoapproval');
            $table->renameColumn('option_shift_assignment_autoaccept', 'setting_shift_assignment_autoaccept');
            $table->boolean('setting_shift_trade_autoapproval')->default(false);
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
}
