<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
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
        'time',
        'date',
        'category_id',
        'batch',
        'mentor_id',
        'course_id'
      ];
}
