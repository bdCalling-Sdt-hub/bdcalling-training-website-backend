<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'module_title',
        "module_no",
        'module_class'
      ];

      public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function mentor()
    {
        return $this->belongsTo(User::class);
    }

}
