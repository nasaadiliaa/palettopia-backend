<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'role',
        'phone',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Helper: check role
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }
    public function products() {
    return $this->hasMany(Product::class);
    }
    public function analysisHistories() {
    return $this->hasMany(AnalysisHistory::class);
    }
    public function educationalContents() {
    return $this->hasMany(EducationalContent::class);
    }
}
