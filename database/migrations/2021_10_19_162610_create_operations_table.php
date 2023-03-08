<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operations', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('customer_id')->index()->nullable();
            $table->foreign('site_id')->references('id')->on('sites');
            $table->unsignedBigInteger('site_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->double('total');
            $table->double('cost');
            $table->double('payment');
            $table->double('discount');
            $table->double('rest');
            $table->timestamps();

        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('operation_id')->index()->nullable();
            $table->foreign('operation_id')->references('id')->on('operations');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_operation_id_foreign');
            $table->dropColumn('operation_id');
        });
        
        Schema::dropIfExists('operations');
    }
}
