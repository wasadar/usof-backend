<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('type', ['+', '-']);
            $table->foreignID('author')
                    ->on('users')
                    ->onUpdate('cascade')
                    ->nullable();
            /*$table->enum('message_type', ['post', 'comment']);*/
            $table->foreignID('post_id')
                    ->on('users')
                    ->onUpdate('posts')
                    ->nullable();
            $table->foreignID('comment_id')
                    ->on('categories')
                    ->onUpdate('comments')
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
        Schema::dropIfExists('likes');
    }
}
