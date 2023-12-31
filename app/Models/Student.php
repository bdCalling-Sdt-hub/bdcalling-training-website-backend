<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $fillable = [
            'register_id',
            'full_name',
            'mobile_number',
            'batch_no',
            'registration_date',
            'dob',
            'department_name',
            'blood_group',
            'address'
    ];

    public function user(){
        return $this->belongsTo(User::class,'register_id');
    }
}
