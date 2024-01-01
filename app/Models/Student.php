<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Student extends Authenticatable implements JWTSubject
{
    use  HasFactory, Notifiable;
    protected $fillable = [
            'userName',
            'fullName',
            'mobileNumber',
            'email',
            'batchNo',
            'password',
            'studentImage',
            'registrationDate',
            'dob',
            'departmentName',
            'verified_email',
            'verified_code',
            'bloodGroup',
            'address'
    ];

    public function user(){
        return $this->belongsTo(User::class,'register_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    public function getJWTCustomClaims()
    {
        return [];
    }
}
