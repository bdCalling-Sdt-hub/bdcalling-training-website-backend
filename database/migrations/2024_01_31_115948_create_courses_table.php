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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->string("courseName");
            $table->string("language");
            $table->text("courseDetails");
            $table->date("startDate");
            $table->string("courseTimeLength");
            $table->string("price");
            $table->text("mentorId");
            $table->integer("maxStudentLength");
            $table->string("skillLevel");
            $table->string("address");
            $table->string("courseThumbnail");
            $table->string("status");
            $table->string("batch");
            $table->string("discount_price")->nullable();
            $table->string("coupon_code")->nullable();
            $table->string("coupon_code_price")->nullable();
            $table->string("seat_left");
            $table->date("end_date");
            $table->text("careeropportunities");
            $table->text("carriculum");
            $table->text("job_position");
            $table->text("software");
            $table->boolean("popular")->default(false);
            $table->boolean("publish")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
