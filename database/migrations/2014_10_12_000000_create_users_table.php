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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fullName');
            $table->string('userName')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('userType');
            $table->string('mobileNumber');
            $table->text("verified_code")->nullable();
            $table->string('verified_email')->default(false);

            $table->text('image')->nullable();
            $table->integer('batchNo')->nullable();
            $table->date('registrationDate')->nullable();
            $table->date('dob')->nullable();


            $table->string("category_id")->nullable();
            $table->string('bloodGroup')->nullable();
            $table->string('address')->nullable();
            $table->string('designation')->nullable();
            $table->string('expert')->nullable();
            $table->boolean("approve")->default(false);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
