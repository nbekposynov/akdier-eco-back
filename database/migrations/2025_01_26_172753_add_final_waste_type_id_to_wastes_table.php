<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinalWasteTypeIdToWastesTable extends Migration
{
    public function up()
    {
        Schema::table('wastes', function (Blueprint $table) {
            $table->foreignId('final_waste_type_id')->nullable()->constrained('final_waste_types')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('wastes', function (Blueprint $table) {
            $table->dropForeign(['final_waste_type_id']);
            $table->dropColumn('final_waste_type_id');
        });
    }
}
