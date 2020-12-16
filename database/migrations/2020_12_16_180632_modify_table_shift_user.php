<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTableShiftUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('shift_user', function (Blueprint $table) {
        $table->bigInteger('trade_user_id')->nullable()->unsigned();
        $table->bigInteger('trade_shift_id')->nullable()->unsigned();
      });   
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('shift_user', function (Blueprint $table) {
        $table->dropColumn(['trade_user_id', 'trade_shift_id']);
      });   
    }
}
