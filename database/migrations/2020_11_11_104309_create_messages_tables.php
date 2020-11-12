<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('alerts', function (Blueprint $table) {
        $table->dropForeign(['team_id']);
      });

      Schema::table('alert_user', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
        $table->dropForeign(['alert_id']);
      });

      Schema::dropIfExists('alerts');
      Schema::dropIfExists('alert_user');


      Schema::create('messages', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('team_id')->nullable()->unsigned(); // 'teams.id' (optional)
        $table->string('for_roles', 200)->nullable();
        $table->boolean('system_message')->default(false);
        $table->string('message_text', 200)->nullable();
        $table->string('message_i18n_string', 200)->nullable();
        $table->string('link_text', 50)->nullable();
        $table->string('link_i18n_string', 50)->nullable();
        $table->string('named_route', 200)->nullable();
        $table->string('color')->nullable()->default('primary');
        $table->string('type')->nullable()->default('info');
        $table->string('icon', 50)->nullable();
        $table->boolean('dismissable')->default(true);
        $table->boolean('outlined')->default(false);
        $table->boolean('show_banner')->default(false);
        $table->datetime('display_until')->nullable();
        $table->timestamps();

        $table->foreign('team_id')
        ->references('id')
        ->on('teams')
        ->onDelete('cascade');
      });



      // Create a table linking users to alerts
      Schema::create('message_user', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('user_id')->unsigned(); // 'users.id'
        $table->bigInteger('message_id')->unsigned(); // 'teams.id'
        $table->boolean('dismissed')->default(false);
        $table->timestamps();

        $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');

        $table->foreign('message_id')
          ->references('id')
          ->on('messages')
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
      Schema::dropIfExists('messages');
      Schema::dropIfExists('message_user');
    }
}