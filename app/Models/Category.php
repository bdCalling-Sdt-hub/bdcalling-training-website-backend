<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public function students()
    {
        return $this->hasMany(Student::class, 'category_id');
    }

    protected $fillable = [
        'category_name',
        'department_id'
      ];
}
