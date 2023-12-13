<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'courseThumbnail'
      ];
}
