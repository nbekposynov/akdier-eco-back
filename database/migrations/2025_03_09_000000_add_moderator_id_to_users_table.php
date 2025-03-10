<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModeratorIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('moderator_id')->nullable()->after('description');

            // Добавляем внешний ключ, связывающий moderator_id с id из таблицы users
            $table->foreign('moderator_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null'); // Если модератор удален, связь становится null
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
            // Сначала удаляем внешний ключ
            $table->dropForeign(['moderator_id']);

            // Затем удаляем колонку
            $table->dropColumn('moderator_id');
        });
    }
}
