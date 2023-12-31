<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mentor extends Model
{
    use HasFactory;

    protected $fillable = [
        'register_id',
        'mentor_image',
        'first_name',
        'last_name',
        'designation',

      ];

    public function user(){
        return $this->belongsTo(User::class,'register_id');
    }

}
