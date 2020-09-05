<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslatorLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translator_languages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned(); // 'users.id'
            $table->string('language',2); // ISO Language code (2 digits)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translator_languages');
    }
}
