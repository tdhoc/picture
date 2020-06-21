<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePictureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('picture', function (Blueprint $table) {
            $table->id();
            $table->string('link');
            $table->string('thumb')->nullable();
            $table->string('author')->nullable();
            $table->string('description')->nullable();
            $table->string('resolution')->nullable();
            $table->string('size')->nullable();
            $table->integer('view')->nullable();
            $table->integer('download')->nullable();
            $table->foreignId('users_id')->constrained("users")->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('subcategory_id')->constrained('subcategory')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('picture');
    }
}
