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
            $table->foreignId('waste_record_id')->constrained('waste_records')->onDelete('cascade'); // Привязка к WasteRecord
            $table->foreignId('company_id')->constrained('users')->onDelete('cascade'); // Привязка к компании
            $table->string('name_othod'); // Название отхода
            $table->decimal('value', 10, 2); // Значение отходов
            $table->string('type_operation'); // Тип операции
            $table->timestamps(); // created_at и updated_at
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
