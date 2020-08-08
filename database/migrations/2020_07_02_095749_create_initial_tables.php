<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInitialTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create a table linking users to teams
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('code', 50);
            $table->bigInteger('user_id')->unsigned(); // 'users.id', Team creator
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });


        // Create a table linking users to teams
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned(); // 'users.id'
            $table->bigInteger('team_id')->unsigned(); // 'teams.id'
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        // Table used for storing locations/routes, team specific
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('team_id')->unsigned(); // 'teams.id'
            $table->string('name',50); // Location/route description
            $table->string('color_code')->nullable(); // Color used when displaying shift (optional)
            $table->string('map',100)->nullable(); // Link to route/location map (optional)
            $table->timestamps();

            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onDelete('cascade');
        });

        // Table for storing weekly schedules, team specific
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('team_id')->unsigned(); // 'teams.id'
            $table->bigInteger('user_id')->unsigned()->nullable(); // 'users.id' (optional)
            $table->char('status')->default(0); // Schedule status (create, open, closed)
            $table->date('date_start')->nullable(); // Default: Monday. make user selectable
            $table->boolean('availableday_mon')->default(1); // Whether to display Monday
            $table->boolean('availableday_tues')->default(1); // Whether to display Tuesday
            $table->boolean('availableday_wed')->default(1); // Whether to display Wednesday
            $table->boolean('availableday_thur')->default(1); // Whether to display Thursday
            $table->boolean('availableday_fri')->default(1); // Whether to display Friday
            $table->boolean('availableday_sat')->default(1); // Whether to display Saturday
            $table->boolean('availableday_sun')->default(1); // Whether to display Sunday
            $table->bigInteger('schedule_template_id')->unsigned()->nullable(); // 'schedule_templates.id'
            $table->timestamps();

            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

        });

        // Table used for storing shifts, schedule specific
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('schedule_id')->unsigned(); // 'schedules.id'
            $table->bigInteger('location_id')->unsigned(); // Cart location/route, 'locations.id'
            $table->time('time_start');
            $table->time('time_end');
            $table->smallInteger('min_participants')->unsigned();
            $table->smallInteger('max_participants')->unsigned();
            $table->timestamps();

            $table->foreign('schedule_id')
                ->references('id')
                ->on('schedules')
                ->onDelete('cascade');

            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onDelete('cascade');
        });

        Schema::create('shift_user', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('shift_id')->unsigned(); // 'shifts.id'
            $table->bigInteger('user_id')->unsigned(); // 'users.id'
            $table->boolean('pending')->default(1); // Pending approval
            $table->timestamps();

            $table->foreign('shift_id')
                ->references('id')
                ->on('shifts')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::create('schedule_templates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('team_id')->unsigned(); // 'teams.id'
            $table->string('name',50);
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
        Schema::drop('teams');
        Schema::drop('team_user');
        Schema::drop('locations');
        Schema::drop('schedules');
        Schema::drop('shifts');
        Schema::drop('shift_user');
        Schema::drop('schedule_templates');

    }
}
