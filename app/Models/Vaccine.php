<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaccine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'manufacturer',
        'doses_required',
        'available',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'available' => 'boolean',
            'doses_required' => 'integer',
        ];
    }

    /**
     * Get all vaccinations using this vaccine.
     */
    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class);
    }
}
