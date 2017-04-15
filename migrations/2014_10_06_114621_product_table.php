<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProductTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Creates the users table
        Schema::create(config('hideyo.db_prefix').'product', function ($table) {
            $table->increments('id');
            $table->boolean('active')->default(false);
            $table->enum('type', array('single', 'group', 'variation'));
            $table->string('reference_code')->nullable();
            $table->string('ean_code')->nullable();
            $table->string('mpn_code')->nullable();
            
            $table->string('title');
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->text('ingredients')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->decimal('price', 12, 4)->nullable();
            $table->decimal('cost_price', 12, 4)->nullable();
            $table->decimal('commercial_price', 12, 4)->nullable();
            $table->boolean('discount_promotion')->default(true);

            $table->enum('discount_type', array('amount', 'percent'));
            $table->decimal('discount_value', 12, 4)->nullable();
            $table->date('discount_start_date')->nullable();
            $table->date('discount_end_date')->nullable();
            $table->bigInteger('amount')->default(0);
            $table->bigInteger('weight')->default(0);
            $table->string('weight_title');
            $table->integer('rank')->default(0);
            $table->string('slug');

            $table->integer('product_category_id')->unsigned()->nullable();            ;
            $table->foreign('product_category_id')->references('id')->on(config('hideyo.db_prefix').'product_category')->onDelete('set null');
            $table->integer('tax_rate_id')->unsigned()->nullable();
            $table->foreign('tax_rate_id')->references('id')->on(config('hideyo.db_prefix').'tax_rate')->onDelete('set null');
            $table->unsignedInteger('product_parent_id')->nullable();
            $table->foreign('product_parent_id')->references('id')->on(config('hideyo.db_prefix').'product')->onDelete('cascade');
            
            $table->integer('shop_id')->unsigned();
            $table->foreign('shop_id')->references('id')->on(config('hideyo.db_prefix').'shop')->onDelete('cascade');
            
            $table->integer('modified_by_user_id')->unsigned()->nullable();
            $table->foreign('modified_by_user_id')->references('id')->on(config('hideyo.db_prefix').'user')->onDelete('set null');
            $table->unique(array('title','shop_id'), 'unique_product_title');
            $table->timestamps();
        });


        Schema::create(config('hideyo.db_prefix').'product_image', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on(config('hideyo.db_prefix').'product')->onDelete('cascade');
            $table->integer('product_variation_id')->unsigned()->nullable();
            $table->foreign('product_variation_id')->references('id')->on(config('hideyo.db_prefix').'product')->onDelete('set null');
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


        Schema::create(config('hideyo.db_prefix').'product_image_attribute', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('attribute_id')->unsigned();
            $table->foreign('attribute_id')->references('id')->on(config('hideyo.db_prefix').'attribute')->onDelete('cascade');
            $table->integer('product_image_id')->unsigned();
            $table->foreign('product_image_id')->references('id')->on(config('hideyo.db_prefix').'product_image')->onDelete('cascade');
            $table->integer('modified_by_user_id')->unsigned()->nullable();
            $table->foreign('modified_by_user_id')->references('id')->on(config('hideyo.db_prefix').'user')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create(config('hideyo.db_prefix').'product_amount_option', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('default_on')->default(false);
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on(config('hideyo.db_prefix').'product')->onDelete('cascade');
            $table->bigInteger('amount')->nullable();
            $table->enum('discount_type', array('amount', 'percent'));
            $table->decimal('discount_value', 12, 4)->nullable();
            $table->date('discount_start_date')->nullable();
            $table->date('discount_end_date')->nullable();
            $table->integer('modified_by_user_id')->unsigned()->nullable();
            $table->foreign('modified_by_user_id')->references('id')->on(config('hideyo.db_prefix').'user')->onDelete('set null');
            $table->timestamps();
        });


        Schema::create(config('hideyo.db_prefix').'product_amount_series', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('active')->default(false);
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on(config('hideyo.db_prefix').'product')->onDelete('cascade');
            $table->bigInteger('series_value');
            $table->bigInteger('series_start');
            $table->bigInteger('series_max');
            $table->integer('modified_by_user_id')->unsigned()->nullable();
            $table->foreign('modified_by_user_id')->references('id')->on(config('hideyo.db_prefix').'user')->onDelete('set null');
            $table->timestamps();
        });


        Schema::create(config('hideyo.db_prefix').'product_waiting_list', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on(config('hideyo.db_prefix').'product')->onDelete('cascade');
            $table->integer('product_attribute_id')->unsigned()->nullable();
            $table->string('email')->nullable();
            $table->unique(array('product_id', 'product_attribute_id', 'email'), 'unique_product_waitinglist');
            $table->timestamps();
        });


        Schema::create(config('hideyo.db_prefix').'product_related_product', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on(config('hideyo.db_prefix').'product')->onDelete('cascade');
            $table->integer('related_product_id')->unsigned();
            $table->foreign('related_product_id')->references('id')->on(config('hideyo.db_prefix').'product')->onDelete('cascade');
            $table->timestamps();
        });


        Schema::create(config('hideyo.db_prefix').'product_sub_product_category', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->foreign('product_id', 'pspc_product_id_fk')->references('id')->on(config('hideyo.db_prefix').'product')->onDelete('cascade');
            $table->unsignedInteger('product_category_id');
            $table->foreign('product_category_id', 'pspc_product_category_id_fk')->references('id')->on(config('hideyo.db_prefix').'product_category')->onDelete('cascade');
            $table->unique(array('product_id','product_category_id'), 'unique_product_category');
            $table->timestamps();
        });

        Schema::create(config('hideyo.db_prefix').'product_extra_field_value', function (Blueprint $table) {
            $table->increments('id');
            $table->string('value')->nullable();
            
            $table->integer('product_id')->unsigned()->nullable();            ;
            $table->foreign('product_id', 'pefv_product_id_fk')->references('id')->on(config('hideyo.db_prefix').'product')->onDelete('cascade');
            
            $table->integer('extra_field_id')->unsigned()->nullable();
            $table->foreign('extra_field_id', 'pefv_extra_field_id_fk')->references('id')->on(config('hideyo.db_prefix').'extra_field')->onDelete('cascade');
            
            $table->integer('extra_field_default_value_id')->unsigned()->nullable();            ;
            $table->foreign('extra_field_default_value_id', 'pefv_extra_field_default_value_id_fk')->references('id')->on(config('hideyo.db_prefix').'extra_field_default_value')->onDelete('set null');
            
            $table->integer('shop_id')->unsigned();
            $table->foreign('shop_id', 'pefv_shop_id_fk')->references('id')->on(config('hideyo.db_prefix').'shop')->onDelete('cascade');
            
            $table->integer('modified_by_user_id')->unsigned()->nullable();
            $table->foreign('modified_by_user_id', 'pefv_modified_by_user_id_fk')->references('id')->on(config('hideyo.db_prefix').'user')->onDelete('set null');
            
            $table->timestamps();
            $table->unique(array('product_id','extra_field_id'), 'unique_product_extra_field_value');
        });   

        Schema::create(config('hideyo.db_prefix').'product_tag_group', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('active')->default(false);
            $table->string('tag')->nullable();
            $table->text('description')->nullable();
            $table->integer('shop_id')->unsigned();
            $table->foreign('shop_id')->references('id')->on(config('hideyo.db_prefix').'shop')->onDelete('cascade');
            $table->integer('modified_by_user_id')->unsigned()->nullable();
            $table->foreign('modified_by_user_id')->references('id')->on(config('hideyo.db_prefix').'user')->onDelete('set null');
            $table->unique(array('tag','shop_id'), 'unique_product_tag_group_tag');
            $table->timestamps();
        });


        Schema::create(config('hideyo.db_prefix').'product_tag_group_related_product', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_tag_group_id')->unsigned();
            $table->foreign('product_tag_group_id', 'ptgrp_product_tag_group_id_fk')->references('id')->on(config('hideyo.db_prefix').'product_tag_group')->onDelete('cascade');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on(config('hideyo.db_prefix').'product')->onDelete('cascade');
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
     //
    }
}
