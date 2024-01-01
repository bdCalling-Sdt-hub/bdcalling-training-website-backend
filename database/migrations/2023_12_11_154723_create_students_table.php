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
            $table->string('fullName');
            $table->string("userName");
            $table->string('email')->unique();
            $table->string('password');
            $table->text('studentImage')->nullable();
            $table->string('mobileNumber')->nullable();
            $table->integer('batchNo');
            $table->date('registrationDate');
            $table->date('dob')->nullable();
            $table->string('departmentName');
            $table->string('bloodGroup')->nullable();
            $table->string('address')->nullable();
            $table->text("verified_code");
            $table->string('verified_email')->default(false);
            $table->boolean("approve")->default(false);
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
