<?php

namespace App\Services;

use App\Models\Flight;
use App\Models\FuelSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FlightImportService
{
    public function importFromData(array $flightData)
    {
        $importedFlights = [];

        DB::beginTransaction();

        try {
            foreach ($flightData as $data) {
                Log::info('Processing flight data', $data);

                // Validate required fields
                if (empty($data['flight_number']) || empty($data['airline_code']) || 
                    empty($data['departure']) || empty($data['arrival']) || 
                    empty($data['std']) || empty($data['eta'])) {
                    throw new \Exception('Missing required flight data');
                }

                // Create flight dengan created_by
                $flight = Flight::create([
                    'flight_number' => $data['flight_number'],
                    'airline_code' => $data['airline_code'],
                    'departure_airport' => $data['departure'],
                    'arrival_airport' => $data['arrival'],
                    'scheduled_departure' => Carbon::parse($data['std']),
                    'scheduled_arrival' => Carbon::parse($data['eta']),
                    'status' => 'Scheduled',
                    'created_by' => Auth::id(), // TAMBAH INI
                ]);

                Log::info('Flight created', ['flight_id' => $flight->id]);

                // Calculate fueling time (1 hour before departure)
                $fuelingTime = Carbon::parse($data['std'])->subHour();

                // Prepare fuel schedule data
                $fuelScheduleData = [
                    'flight_id' => $flight->id,
                    'scheduled_fueling_time' => $fuelingTime,
                    'status' => 'Pending',
                ];

                // Add CRO and Shift if provided
                if (!empty($data['cro_id'])) {
                    $fuelScheduleData['cro_id'] = $data['cro_id'];
                    Log::info('CRO assigned', ['cro_id' => $data['cro_id']]);
                }

                if (!empty($data['shift_id'])) {
                    $fuelScheduleData['shift_id'] = $data['shift_id'];
                    Log::info('Shift assigned', ['shift_id' => $data['shift_id']]);
                }

                // Create fuel schedule
                $fuelSchedule = FuelSchedule::create($fuelScheduleData);
                Log::info('Fuel schedule created', ['fuel_schedule_id' => $fuelSchedule->id]);

                $importedFlights[] = $flight;
            }

            DB::commit();
            Log::info('Flight import completed successfully', ['count' => count($importedFlights)]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Flight import failed: ' . $e->getMessage());
            throw $e;
        }

        return $importedFlights;
    }
}