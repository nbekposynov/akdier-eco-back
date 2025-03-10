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
        Schema::create('waste_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->onDelete('cascade'); // Привязка к компании
            $table->foreignId('moderator_id')->constrained('users')->onDelete('cascade'); // Привязка к модератору
            $table->string('car_num')->nullable(); // Номер машины
            $table->string('driv_name')->nullable(); // Имя водителя
            $table->date('record_date'); // Дата записи
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
        Schema::dropIfExists('waste_records');
    }
};
