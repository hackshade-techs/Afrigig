<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->increments('id');
            $table->increments('id');
            $table->string('translation_lang', 10)->nullable()->index('translation_lang');
            $table->integer('translation_of')->unsigned()->nullable()->index('translation_of');
            $table->integer('parent_id')->unsigned()->nullable()->default(0)->index('parent_id');
            $table->string('name', 150)->default('');
            $table->string('slug', 150)->nullable();
            $table->string('description')->nullable();
            $table->string('picture', 100)->nullable();
            $table->string('css_class', 100)->nullable();
            $table->integer('lft')->unsigned()->nullable();
            $table->integer('rgt')->unsigned()->nullable();
            $table->integer('depth')->unsigned()->nullable();
            $table->boolean('active')->nullable()->default(1);
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
        Schema::dropIfExists('skills');
    }
}
