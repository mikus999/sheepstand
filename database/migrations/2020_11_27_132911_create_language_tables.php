<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguageTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::dropIfExists('translator_languages');


      Schema::create('languages', function (Blueprint $table) {
        $table->id();
        $table->string('code');
        $table->string('name');
        $table->string('native_name');
        $table->boolean('site_language')->default(false);
        $table->timestamps();
      });


      Schema::create('language_user', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('user_id')->nullable()->unsigned(); // 'users.id' (optional)
        $table->bigInteger('language_id')->nullable()->unsigned(); // 'languages.id' (optional)
        $table->timestamps();

        $table->foreign('user_id')
        ->references('id')
        ->on('users')
        ->onDelete('cascade');

        $table->foreign('language_id')
        ->references('id')
        ->on('languages')
        ->onDelete('cascade');
      });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('language_user', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
        $table->dropForeign(['language_id']);
      });

      Schema::dropIfExists('languages');
      Schema::dropIfExists('language_user');
    }
}
