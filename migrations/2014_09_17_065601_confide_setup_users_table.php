<?php

use Illuminate\Database\Migrations\Migration;

class ConfideSetupUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::dropIfExists(config('hideyo.db_prefix').'groups');
        Schema::dropIfExists(config('hideyo.db_prefix').'users_groups');
        Schema::dropIfExists(config('hideyo.db_prefix').'users');




        // Creates the users table
        Schema::create(config('hideyo.db_prefix').'user', function ($table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('confirmation_code');
            $table->string('remember_token')->nullable();
            $table->string('api_token')->unique()->nullable();
            $table->boolean('confirmed')->default(false);
            $table->integer('language_id')->unsigned()->nullable();
            $table->integer('selected_shop_id')->unsigned()->nullable();
            $table->foreign('selected_shop_id')->references('id')->on(config('hideyo.db_prefix').'shop')->onDelete('set null');           
            $table->timestamps();
        });

        // Creates password reminders table
        Schema::create(config('hideyo.db_prefix').'password_reminders', function ($table) {
            $table->string('email');
            $table->string('token');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('hideyo.db_prefix').'password_reminders');
        Schema::drop(config('hideyo.db_prefix').'users');
    }
}
