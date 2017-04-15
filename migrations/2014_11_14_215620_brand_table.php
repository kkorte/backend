<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BrandTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Creates the users table
        Schema::create(config('hideyo.db_prefix').'brand', function ($table) {
            $table->increments('id');
            $table->boolean('active')->default(false);
            $table->string('reference_code')->nullable();
            $table->string('title');
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->integer('rank')->default(0);
            $table->integer('shop_id')->unsigned();
            $table->foreign('shop_id')->references('id')->on(config('hideyo.db_prefix').'shop')->onDelete('cascade');
            $table->string('slug');
            $table->integer('modified_by_user_id')->unsigned()->nullable();
            $table->foreign('modified_by_user_id')->references('id')->on(config('hideyo.db_prefix').'user')->onDelete('set null');
            $table->unique(array('title','shop_id'), 'unique_brand_title');
            $table->timestamps();
        });

        Schema::create(config('hideyo.db_prefix').'brand_image', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('brand_id')->unsigned();
            $table->foreign('brand_id')->references('id')->on(config('hideyo.db_prefix').'brand')->onDelete('cascade');
            ;
            $table->string('file')->nullable();
            $table->string('path')->nullable();
            $table->integer('size')->nullable();
            $table->string('extension')->nullable();
            $table->integer('rank')->default(0);
            $table->string('tag')->nullable();
            $table->integer('modified_by_user_id')->unsigned()->nullable();
            $table->foreign('modified_by_user_id')->references('id')->on(config('hideyo.db_prefix').'user')->onDelete('set null');
            $table->timestamps();
        });

        Schema::table(config('hideyo.db_prefix').'product', function (Blueprint $table) {
            $table->integer('brand_id')->unsigned()->nullable();
            $table->foreign('brand_id')->references('id')->on(config('hideyo.db_prefix').'brand')->onDelete('set null');
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
