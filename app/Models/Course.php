<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function mentor()
    {
        return $this->belongsTo(User::class);
    }



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
        'status',
        'batch',
        'discount_price',
        'coupon_code_price',
        'coupon_code',
        'end_date',
        'seat_left',

        'reviews',
        'careeropportunities',
        'carriculum',
        'job_position',
        'software',
        'popular'
      ];

    //   public function classes()
    //   {
    //       return $this->hasMany(Course::class, 'course_id');
    //   }
}
