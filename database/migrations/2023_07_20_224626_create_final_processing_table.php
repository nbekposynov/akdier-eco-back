<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('final_processing', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table-> string('kod_othoda');
            $table-> string('name_othod');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company')->onDelete('set null');
            $table->double('value')->nullable();
            $table->string('type_operaton')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('final_processing');
    }
};
