<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',
        'hospital_id',
        'purpose',
        'vaccine_id',
        'date',
        'time',
        'status',
    ];

    /**
     * Get the vaccine for this appointment (for vaccination appointments).
     */
    public function vaccine()
    {
        return $this->belongsTo(Vaccine::class);
    }


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Get the patient that owns the appointment.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the hospital that owns the appointment.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Get the covid test for this appointment.
     */
    public function covidTest()
    {
        return $this->hasOne(CovidTest::class);
    }

    /**
     * Get the vaccination for this appointment.
     */
    public function vaccination()
    {
        return $this->hasOne(Vaccination::class);
    }

    /**
     * Check if appointment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    /**
     * Check if appointment is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'Approved';
    }
}
