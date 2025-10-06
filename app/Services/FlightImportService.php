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

                // ✅ VALIDASI UPDATE: Hapus validasi untuk eta
                if (empty($data['flight_number']) || empty($data['airline_code']) || 
                    empty($data['departure']) || empty($data['arrival']) || 
                    empty($data['std'])) { // HAPUS empty($data['eta'])
                    throw new \Exception('Missing required flight data');
                }

                // ✅ CREATE FLIGHT UPDATE: Hanya gunakan STD
                $flight = Flight::create([
                    'flight_number' => $data['flight_number'],
                    'airline_code' => $data['airline_code'],
                    'departure_airport' => $data['departure'],
                    'arrival_airport' => $data['arrival'],
                    'scheduled_departure' => Carbon::parse($data['std']),
                    // ✅ HAPUS scheduled_arrival dari sini
                    'status' => 'Scheduled',
                    'created_by' => Auth::id(),
                ]);

                Log::info('Flight created', ['flight_id' => $flight->id]);

                // ✅ CALCULATE FUELING TIME (1 hour before departure) - Tetap gunakan STD
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

    // ✅ METHOD BARU untuk handle manual flight creation (optional)
    public function createManualFlight(array $flightData)
    {
        DB::beginTransaction();

        try {
            Log::info('Creating manual flight', $flightData);

            // Validasi untuk manual input
            if (empty($flightData['flight_number']) || empty($flightData['airline_code']) || 
                empty($flightData['departure_airport']) || empty($flightData['arrival_airport']) || 
                empty($flightData['scheduled_departure'])) {
                throw new \Exception('Missing required flight data for manual creation');
            }

            // Create flight hanya dengan STD
            $flight = Flight::create([
                'flight_number' => $flightData['flight_number'],
                'airline_code' => $flightData['airline_code'],
                'departure_airport' => $flightData['departure_airport'],
                'arrival_airport' => $flightData['arrival_airport'],
                'scheduled_departure' => Carbon::parse($flightData['scheduled_departure']),
                'status' => 'Scheduled',
                'created_by' => Auth::id(),
            ]);

            // Calculate fueling time
            $fuelingTime = Carbon::parse($flightData['scheduled_departure'])->subHour();

            // Create fuel schedule
            $fuelScheduleData = [
                'flight_id' => $flight->id,
                'scheduled_fueling_time' => $fuelingTime,
                'status' => 'Pending',
            ];

            // Tambahkan CRO dan Shift jika ada
            if (!empty($flightData['cro_id'])) {
                $fuelScheduleData['cro_id'] = $flightData['cro_id'];
            }

            if (!empty($flightData['shift_id'])) {
                $fuelScheduleData['shift_id'] = $flightData['shift_id'];
            }

            $fuelSchedule = FuelSchedule::create($fuelScheduleData);

            DB::commit();
            
            Log::info('Manual flight created successfully', [
                'flight_id' => $flight->id,
                'fuel_schedule_id' => $fuelSchedule->id
            ]);

            return $flight;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Manual flight creation failed: ' . $e->getMessage());
            throw $e;
        }
    }
}