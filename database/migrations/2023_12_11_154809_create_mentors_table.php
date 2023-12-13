<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mentors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('register_id');
            $table->foreign('register_id')->references('id')->on('users');
            $table->text('mentor_image');
            $table->string('email');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('designation');
            $table->string('course_name');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('mentors');
    }
};
