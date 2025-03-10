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
        Schema::table('final_waste_types', function (Blueprint $table) {
            $table->decimal('factor', 10, 2)->nullable(); // Коэфициент
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('final_waste_types', function (Blueprint $table) {
            $table->dropColumn('factor');
        });
    }
};
