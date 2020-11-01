<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTelegramColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('notification_settings', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('team_id')->unsigned(); // 'teams.id'
        $table->string('telegram_channel_id');
        $table->string('telegram_access_hash');
        $table->boolean('setting_notify_trade_requests')->default(false);
        $table->boolean('setting_notify_trade_filled')->default(false);
        $table->boolean('setting_notify_schedule_open')->default(false);
        $table->boolean('setting_notify_schedule_closed')->default(false);
        $table->timestamps();

        $table->foreign('team_id')
        ->references('id')
        ->on('teams')
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
      Schema::dropIfExists('notification_settings');
    }
}
