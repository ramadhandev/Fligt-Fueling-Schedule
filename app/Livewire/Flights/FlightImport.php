<?php

namespace App\Livewire\Flights;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\FlightImportService;
use App\Models\Flight;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Support\Facades\Log;

class FlightImport extends Component
{
    use WithFileUploads;

    public $csvFile;
    public $importedCount = 0;
    public $isImporting = false;
    public $showManualForm = false;

    // ✅ MANUAL INPUT FIELDS - HAPUS scheduled_arrival
    public $flight_number = '';
    public $airline_code = '';
    public $departure_airport = '';
    public $arrival_airport = '';
    public $scheduled_departure = ''; // HANYA STD
    public $cro_id = '';
    public $shift_id = '';

    protected $rules = [
        'csvFile' => 'required|file|mimes:csv,txt|max:1024',
    ];

    protected $manualRules = [
        'flight_number' => 'required|string|max:10',
        'airline_code' => 'required|string|max:3',
        'departure_airport' => 'required|string|max:3',
        'arrival_airport' => 'required|string|max:3',
        'scheduled_departure' => 'required|date', // ✅ HANYA STD
        'cro_id' => 'nullable|exists:users,id',
        'shift_id' => 'nullable|exists:shifts,id',
    ];

    public function importCSV()
    {
        $this->validateOnly('csvFile');
        $this->isImporting = true;

        try {
            $filePath = $this->csvFile->getRealPath();
            $flightData = $this->parseCSV($filePath);
            
            $importService = new FlightImportService();
            $importedFlights = $importService->importFromData($flightData);
            
            $this->importedCount = count($importedFlights);
            
            session()->flash('success', "Berhasil mengimport {$this->importedCount} flight dari CSV!");
            
        } catch (\Exception $e) {
            Log::error('CSV Import Error: ' . $e->getMessage());
            session()->flash('error', 'Error importing CSV: ' . $e->getMessage());
        }

        $this->isImporting = false;
        $this->csvFile = null;
    }

    public function addManualFlight()
    {
        $this->validate($this->manualRules);

        try {
            Log::info('Manual flight form data:', [
                'flight_number' => $this->flight_number,
                'airline_code' => $this->airline_code,
                'departure_airport' => $this->departure_airport,
                'arrival_airport' => $this->arrival_airport,
                'scheduled_departure' => $this->scheduled_departure, // ✅ HANYA STD
                'cro_id' => $this->cro_id,
                'shift_id' => $this->shift_id,
            ]);

            $importService = new FlightImportService();
            $flightData = [[
                'flight_number' => $this->flight_number,
                'airline_code' => $this->airline_code,
                'departure' => $this->departure_airport,
                'arrival' => $this->arrival_airport,
                'std' => $this->scheduled_departure, // ✅ HANYA STD
                // ❌ HAPUS ETA dari sini
                'cro_id' => $this->cro_id ?: null,
                'shift_id' => $this->shift_id ?: null,
            ]];
            
            $importedFlights = $importService->importFromData($flightData);
            
            session()->flash('success', "Berhasil menambahkan flight {$this->flight_number}!");
            
            // Reset form
            $this->resetManualForm();
            
        } catch (\Exception $e) {
            Log::error('Error in addManualFlight: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            session()->flash('error', 'Error adding flight: ' . $e->getMessage());
        }
    }

    private function parseCSV($filePath)
    {
        $data = [];
        
        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            // Skip header jika ada
            $headers = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== FALSE) {
                if (count($row) >= 5) { // ✅ UBAH dari 6 ke 5 kolom
                    $data[] = [
                        'flight_number' => $row[0] ?? '',
                        'airline_code' => $row[1] ?? '',
                        'departure' => $row[2] ?? '',
                        'arrival' => $row[3] ?? '',
                        'std' => $row[4] ?? '', // ✅ HANYA STD, HAPUS ETA
                    ];
                }
            }
            
            fclose($handle);
        }
        
        return $data;
    }

    private function resetManualForm()
    {
        $this->reset([
            'flight_number',
            'airline_code', 
            'departure_airport',
            'arrival_airport',
            'scheduled_departure', // ✅ HANYA STD
            // ❌ HAPUS scheduled_arrival dari sini
            'cro_id',
            'shift_id',
        ]);
    }

    public function toggleManualForm()
    {
        $this->showManualForm = !$this->showManualForm;
        $this->resetManualForm();
    }

    public function render()
    {
        $recentFlights = Flight::with(['fuelSchedule.cro', 'fuelSchedule.shift'])
            ->latest()
            ->take(5)
            ->get();

        $cros = User::where('role', 'CRO')->get(['id', 'name']);
        $shifts = Shift::all();

        return view('livewire.flights.flight-import', compact('recentFlights', 'cros', 'shifts'));
    }
}