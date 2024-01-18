<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;



    public function student()
    {
        return $this->belongsTo(User::class,'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    protected $fillable = [
        'course_id',
        'review',
        'student_id'
    ];
}
