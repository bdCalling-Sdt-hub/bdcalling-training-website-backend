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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('register_id');
            $table->foreign('register_id')->references('id')->on('users');
            $table->text('student_image');
            $table->string('full_name');
            $table->string('mobile_number');
            $table->integer('batch_no');
            $table->date('registration_date');
            $table->date('dob');
            $table->string('department_name');
            $table->string('blood_group');
            $table->string('address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
