<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LanguageTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('hideyo.db_prefix').'language', function (Blueprint $table) {
            $table->increments('id');
            $table->text('language')->nullable();
            $table->integer('modified_by_user_id')->unsigned()->nullable();
            $table->foreign('modified_by_user_id')->references('id')->on(config('hideyo.db_prefix').'user')->onDelete('set null');
            $table->integer('shop_id')->unsigned();
            $table->foreign('shop_id')->references('id')->on(config('hideyo.db_prefix').'shop')->onDelete('cascade');
            $table->timestamps();
        });
    
        Schema::table(config('hideyo.db_prefix').'shop', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on(config('hideyo.db_prefix').'language')->onDelete('set null');
        });

        Schema::table(config('hideyo.db_prefix').'user', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on(config('hideyo.db_prefix').'language')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('language');
    }
}
