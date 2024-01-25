<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    public function course()
    {
        return $this->belongsTo(Course::class,"course_id");
    }

    public function student()
    {
        return $this->belongsTo(User::class);
    }


    protected $fillable = [

        "gateway_name",
        "course_id",
        "course_name",
        "amount",
        "transaction_id",
        "student_id",
        "status",
        "currency"

       ];

}
