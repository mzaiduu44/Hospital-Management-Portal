<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaccination extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'appointment_id',
        'patient_id',
        'hospital_id',
        'vaccine_id',
        'dose_number',
        'status',
        'vaccination_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'vaccination_date' => 'date',
            'dose_number' => 'integer',
        ];
    }

    /**
     * Get the appointment that owns this vaccination.
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the patient that received this vaccination.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the hospital that administered this vaccination.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Get the vaccine used.
     */
    public function vaccine()
    {
        return $this->belongsTo(Vaccine::class);
    }
}
