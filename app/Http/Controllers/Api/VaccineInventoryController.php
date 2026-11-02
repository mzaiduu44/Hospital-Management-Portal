<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VaccineInventory;
use App\Models\Hospital;
use App\Models\Vaccine;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VaccineInventoryController extends Controller
{
    /**
     * Get all inventory for a hospital.
     */
    public function index(Request $request, string $hospitalId)
    {
        try {
            $hospital = Hospital::find($hospitalId);

            if (!$hospital) {
                return apiNotFound('Hospital not found');
            }

            // Get all vaccines with their inventory status for this hospital
            $vaccines = Vaccine::all();
            $inventory = VaccineInventory::where('hospital_id', $hospitalId)->get()->keyBy('vaccine_id');

            $result = $vaccines->map(function ($vaccine) use ($inventory, $hospitalId) {
                $inv = $inventory->get($vaccine->id);
                return [
                    'id' => $inv?->id,
                    'hospital_id' => (int) $hospitalId,
                    'vaccine_id' => $vaccine->id,
                    'vaccine_name' => $vaccine->name,
                    'vaccine_manufacturer' => $vaccine->manufacturer,
                    'doses_required' => $vaccine->doses_required,
                    'quantity' => $inv?->quantity ?? 0,
                    'min_threshold' => $inv?->min_threshold ?? 10,
                    'is_available' => ($inv?->quantity ?? 0) > 0,
                    'is_low_stock' => ($inv?->quantity ?? 0) <= ($inv?->min_threshold ?? 10),
                ];
            });

            return apiSuccess('Inventory retrieved successfully', $result);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve inventory: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update/refill stock quantity for a specific vaccine at a hospital.
     */
    public function update(Request $request, string $hospitalId, string $vaccineId)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:0',
                'min_threshold' => 'sometimes|integer|min:0',
            ]);

            $hospital = Hospital::find($hospitalId);
            if (!$hospital) {
                return apiNotFound('Hospital not found');
            }

            $vaccine = Vaccine::find($vaccineId);
            if (!$vaccine) {
                return apiNotFound('Vaccine not found');
            }

            $inventory = VaccineInventory::updateOrCreate(
                ['hospital_id' => $hospitalId, 'vaccine_id' => $vaccineId],
                [
                    'quantity' => $validated['quantity'],
                    'min_threshold' => $validated['min_threshold'] ?? 10,
                ]
            );

            return apiSuccess('Inventory updated successfully', [
                'id' => $inventory->id,
                'hospital_id' => (int) $hospitalId,
                'vaccine_id' => (int) $vaccineId,
                'vaccine_name' => $vaccine->name,
                'quantity' => $inventory->quantity,
                'min_threshold' => $inventory->min_threshold,
                'is_available' => $inventory->isAvailable(),
                'is_low_stock' => $inventory->isLowStock(),
            ]);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to update inventory: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Refill stock by adding to existing quantity.
     */
    public function refill(Request $request, string $hospitalId, string $vaccineId)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|integer|min:1',
            ]);

            $hospital = Hospital::find($hospitalId);
            if (!$hospital) {
                return apiNotFound('Hospital not found');
            }

            $vaccine = Vaccine::find($vaccineId);
            if (!$vaccine) {
                return apiNotFound('Vaccine not found');
            }

            $inventory = VaccineInventory::firstOrCreate(
                ['hospital_id' => $hospitalId, 'vaccine_id' => $vaccineId],
                ['quantity' => 0, 'min_threshold' => 10]
            );

            $inventory->incrementStock($validated['amount']);

            return apiSuccess('Inventory refilled successfully', [
                'id' => $inventory->id,
                'hospital_id' => (int) $hospitalId,
                'vaccine_id' => (int) $vaccineId,
                'vaccine_name' => $vaccine->name,
                'quantity' => $inventory->quantity,
                'min_threshold' => $inventory->min_threshold,
                'is_available' => $inventory->isAvailable(),
                'is_low_stock' => $inventory->isLowStock(),
            ]);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to refill inventory: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Public endpoint to check vaccine availability at a hospital.
     */
    public function checkAvailability(string $hospitalId, string $vaccineId)
    {
        try {
            $hospital = Hospital::find($hospitalId);
            if (!$hospital) {
                return apiNotFound('Hospital not found');
            }

            $vaccine = Vaccine::find($vaccineId);
            if (!$vaccine) {
                return apiNotFound('Vaccine not found');
            }

            $inventory = VaccineInventory::where('hospital_id', $hospitalId)
                ->where('vaccine_id', $vaccineId)
                ->first();

            $quantity = $inventory?->quantity ?? 0;
            $isAvailable = $quantity > 0;

            return apiSuccess('Availability checked successfully', [
                'hospital_id' => (int) $hospitalId,
                'hospital_name' => $hospital->name,
                'vaccine_id' => (int) $vaccineId,
                'vaccine_name' => $vaccine->name,
                'quantity' => $quantity,
                'is_available' => $isAvailable,
                'message' => $isAvailable
                    ? "This vaccine is available at {$hospital->name}."
                    : "Sorry, {$vaccine->name} is currently out of stock at {$hospital->name}.",
            ]);
        } catch (\Exception $e) {
            return apiError('Failed to check availability: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get available vaccines for a hospital (for patient booking).
     */
    public function getAvailableVaccines(string $hospitalId)
    {
        try {
            $hospital = Hospital::find($hospitalId);
            if (!$hospital) {
                return apiNotFound('Hospital not found');
            }

            // Get all vaccines with their availability status at this hospital
            $vaccines = Vaccine::where('available', true)->get();
            $inventory = VaccineInventory::where('hospital_id', $hospitalId)->get()->keyBy('vaccine_id');

            $result = $vaccines->map(function ($vaccine) use ($inventory, $hospital) {
                $inv = $inventory->get($vaccine->id);
                $quantity = $inv?->quantity ?? 0;
                return [
                    'vaccine_id' => $vaccine->id,
                    'vaccine_name' => $vaccine->name,
                    'manufacturer' => $vaccine->manufacturer,
                    'doses_required' => $vaccine->doses_required,
                    'quantity' => $quantity,
                    'is_available' => $quantity > 0,
                    'message' => $quantity > 0
                        ? "Available"
                        : "Out of stock at {$hospital->name}",
                ];
            });

            return apiSuccess('Available vaccines retrieved successfully', $result);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve available vaccines: ' . $e->getMessage(), null, 500);
        }
    }
}
