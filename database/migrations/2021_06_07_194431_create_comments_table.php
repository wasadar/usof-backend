<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('content')->unique();
            $table->integer('rating')->default(0);
            $table->foreignID('author')
                    ->on('users')
                    ->onUpdate('cascade')
                    ->nullable();
            $table->foreignID('post')
                    ->on('posts')
                    ->onUpdate('cascade')
                    ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
