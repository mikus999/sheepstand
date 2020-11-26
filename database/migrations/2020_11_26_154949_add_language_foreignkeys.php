<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLanguageForeignkeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('languages', function (Blueprint $table) {
        $table->foreign('id')
        ->references('language')
        ->on('translator_languages');
      });

      Schema::table('translator_languages', function (Blueprint $table) {
        $table->foreign('language')
        ->references('id')
        ->on('languages');
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
