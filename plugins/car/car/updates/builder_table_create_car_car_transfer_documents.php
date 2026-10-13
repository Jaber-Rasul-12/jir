<?php namespace Car\Car\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateCarCarTransferDocuments extends Migration
{
    public function up()
    {
        Schema::create('car_car_transfer_documents', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('document_number')->unique();
            $table->integer('car_id')->unsigned();

            // بيانات المالك القديم (المتنازل)
            $table->string('old_owner_name');
            $table->string('old_owner_id_number');
            $table->string('old_owner_id_issuer')->nullable();
            $table->string('old_owner_address')->nullable();
            $table->string('old_owner_phone')->nullable();

            // بيانات المالك الجديد (المتنازل إليه)
            $table->string('new_owner_name')->nullable();
            $table->string('new_owner_id_number')->nullable();
            $table->string('new_owner_id_issuer')->nullable();
            $table->string('new_owner_address')->nullable();
            $table->string('new_owner_phone')->nullable();

            // بيانات السيارة (منسوخة للتوثيق التاريخي)
            $table->string('car_license_plate')->nullable();
            $table->string('car_chassis_number')->nullable();
            $table->string('car_engine_number')->nullable();
            $table->string('car_brand')->nullable();
            $table->string('car_model')->nullable();
            $table->string('car_color')->nullable();

            // بيانات التنازل
            $table->date('transfer_date');
            $table->string('transfer_place')->nullable();
            $table->boolean('is_full_transfer')->default(true);
            $table->decimal('transfer_percentage', 5, 2)->nullable();

            // الوثائق المطلوبة
            $table->boolean('has_identity_copy')->default(false);
            $table->boolean('has_traffic_clearance')->default(false);
            $table->boolean('has_insurance')->default(false);
            $table->boolean('has_inspection')->default(false);
            $table->boolean('has_old_registration')->default(false);

            // الحالة
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('car_id')
                    ->references('id')
                    ->on('car_car_cars')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('car_car_transfer_documents');
    }
}