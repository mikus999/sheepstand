<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTeamOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('option_shift_request_autoapproval')->default(false);
            $table->boolean('option_shift_assignment_autoaccept')->default(false);
            $table->bigInteger('default_participants_min')->default(2);
            $table->bigInteger('default_participants_max')->default(3);
            $table->bigInteger('default_shift_minutes')->default(120);
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
