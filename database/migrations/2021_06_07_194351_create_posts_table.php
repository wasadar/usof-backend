<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title')->unique();
            $table->string('content')->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('rating')->default(0);
            $table->foreignID('author')
                    ->on('users')
                    ->onUpdate('cascade')
                    ->nullable();
            $table->foreignID('category')
                    ->on('categories')
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
        Schema::dropIfExists('posts');
    }
}
