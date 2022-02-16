<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_types', function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();

        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
            $table->unsignedBigInteger('type_id')->index();
            $table->foreign('type_id')->references('id')->on('company_types');
            $table->timestamp('expire_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->index();
            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_company_id_foreign');
            $table->dropColumn('company_id');
        });
        Schema::dropIfExists('companies');
        Schema::dropIfExists('company_types');
    }
}
