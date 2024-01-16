<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    // public function student()
    // {
    //     return $this->hasOne(Student::class, 'register_id');
    // }

    // public function mentor()
    // {
    //     return $this->hasOne(Mentor::class, 'register_id');
    // }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    public function getJWTCustomClaims()
    {
        return [];
    }


    protected $fillable = [
        'fullName',
        "userName",
        'email',
        'password',
        "userType",
        'verified_email',
        'verified_code',
        'mobileNumber',


        'batchNo',
        'category_id',
        'dob',
        'registrationDate',
        'image',
        'bloodGroup',
        'address',
        'designation',
        'expert',
        'approve',

    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
