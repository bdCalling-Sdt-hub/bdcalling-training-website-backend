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
            $table->text('mentor_image')->default("test");
            $table->string('first_name')->default("test");
            $table->string('last_name')->default("test");
            $table->string('designation')->default("test");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentors');
    }
};
