<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'courseName',
        'language',
        'courseDetails',
        'startDate',
        'courseTimeLength',
        'price',
        'mentorId',
        'maxStudentLength',
        'skillLevel',
        'address',
        'courseThumbnail',
        'status'
      ];

    //   public function classes()
    //   {
    //       return $this->hasMany(Course::class, 'course_id');
    //   }
}
