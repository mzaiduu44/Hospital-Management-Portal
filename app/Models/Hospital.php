<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Hospital extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'website',
        'description',
        'profile_photo_path',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Check if hospital is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'Approved';
    }

    /**
     * Check if hospital is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    /**
     * Get all appointments for the hospital.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get all covid tests for the hospital.
     */
    public function covidTests()
    {
        return $this->hasMany(CovidTest::class);
    }

    /**
     * Get all vaccinations for the hospital.
     */
    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class);
    }

    /**
     * Get all vaccine inventory for the hospital.
     */
    public function vaccineInventory()
    {
        return $this->hasMany(VaccineInventory::class);
    }
}
