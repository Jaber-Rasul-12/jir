<?php namespace Car\Car\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateCarCarModels extends Migration
{
    public function up()
    {
        Schema::create('car_car_models', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->integer('brand_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('brand_id')
                    ->references('id')
                    ->on('car_car_brands')
                    ->onDelete('cascade')->onUpdate('cascade');

        });
    }
    
    public function down()
    {
        Schema::dropIfExists('car_car_models');
    }
}
