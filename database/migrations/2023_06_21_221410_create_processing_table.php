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
        Schema::create('processing', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('moderator_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company')->onDelete('set null');
            $table->foreign('moderator_id')->references('id')->on('moderator')->onDelete('set null');
            $table->unsignedBigInteger('tbo_total');
            $table->unsignedBigInteger('tbo_food');
            $table->unsignedBigInteger('tbo_plastic');
            $table->unsignedBigInteger('tbo_bumaga');
            $table->unsignedBigInteger('tbo_derevo');
            $table->unsignedBigInteger('tbo_meshki');
            $table->unsignedBigInteger('tbo_metal');
            $table->unsignedBigInteger('tbo_neutil');
            $table->unsignedBigInteger('bsv');
            $table->unsignedBigInteger('tpo_total');
            $table->unsignedBigInteger('tpo_cement');
            $table->unsignedBigInteger('tpo_drevesn');
            $table->unsignedBigInteger('tpo_metall_m');
            $table->unsignedBigInteger('tpo_krishki');
            $table->unsignedBigInteger('tpo_meshki');
            $table->unsignedBigInteger('tpo_plastic');
            $table->unsignedBigInteger('tpo_shini');
            $table->unsignedBigInteger('tpo_vetosh_fi');
            $table->unsignedBigInteger('tpo_makul');
            $table->unsignedBigInteger('tpo_akkum');
            $table->unsignedBigInteger('tpo_tara_met');
            $table->unsignedBigInteger('tpo_tara_pol');
            $table->unsignedBigInteger('po_total');
            $table->unsignedBigInteger('po_neftesh');
            $table->unsignedBigInteger('po_zam_gr');
            $table->unsignedBigInteger('po_bur_shl');
            $table->unsignedBigInteger('po_obr');
            $table->unsignedBigInteger('po_him_reag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('processing');
    }
};

