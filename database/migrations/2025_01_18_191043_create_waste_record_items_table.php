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
        Schema::create('waste_record_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_record_id')->constrained('waste_records')->onDelete('cascade'); // Привязка к WasteRecord
            $table->foreignId('waste_id')->constrained('wastes')->onDelete('cascade'); // Привязка к Waste
            $table->decimal('amount', 10, 2); // Количество отходов
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
        Schema::dropIfExists('waste_record_items');
    }
};
