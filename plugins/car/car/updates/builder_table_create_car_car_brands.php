<?php namespace Car\Car\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateCarCarBrands extends Migration
{
    public function up()
    {
        Schema::create('car_car_brands', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('car_car_brands');
    }
}
