<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChargeTable extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charge_types', function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
            $table->unsignedBigInteger('company_id')->index();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->timestamps();
            $table->softDeletes();

        });

        Schema::create('charges', function (Blueprint $table) {
            $table->id('id');
            $table->double('amount');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('type_id')->index();
            $table->foreign('type_id')->references('id')->on('charge_types');
            $table->unsignedBigInteger('company_id')->index();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();

        });
    }


 /**
     * Reverse the migrations.
     *
     * @return void
     */
 public function down()
    {
        Schema::dropIfExists('charges');
        Schema::dropIfExists('charge_types');
    }
}