<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinalWasteTypesTable extends Migration
{
    public function up()
    {
        Schema::create('final_waste_types', function (Blueprint $table) {
            $table->id();
            $table->string('final_name'); // Финальное имя отхода
            $table->string('type_operation'); // Тип операции
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('final_waste_types');
    }
}
