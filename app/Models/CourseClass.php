<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'batch',
        'module_title',
        "module_no",
        'module_class'
      ];

      public function course()
    {
        return $this->belongsTo(CourseClass::class, 'course_id');
    }
}
