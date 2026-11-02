<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineInventory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vaccine_inventory';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hospital_id',
        'vaccine_id',
        'quantity',
        'min_threshold',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'min_threshold' => 'integer',
        ];
    }

    /**
     * Get the hospital that owns the inventory.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Get the vaccine for this inventory.
     */
    public function vaccine()
    {
        return $this->belongsTo(Vaccine::class);
    }

    /**
     * Check if the vaccine is available in stock.
     */
    public function isAvailable(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Check if stock is below minimum threshold.
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_threshold;
    }

    /**
     * Decrement inventory quantity by specified amount.
     */
    public function decrementStock(int $amount = 1): bool
    {
        if ($this->quantity >= $amount) {
            $this->decrement('quantity', $amount);
            return true;
        }
        return false;
    }

    /**
     * Increment inventory quantity by specified amount.
     */
    public function incrementStock(int $amount): void
    {
        $this->increment('quantity', $amount);
    }
}
