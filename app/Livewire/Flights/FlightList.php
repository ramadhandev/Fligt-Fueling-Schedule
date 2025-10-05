<?php

namespace App\Livewire\Flights;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Flight;

class FlightList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $airline = '';
    public $date = '';
    public $perPage = 10;
    public $showDeleteModal = false;
    public $flightToDelete = null;
    public $flightToDeleteNumber = '';
    public $selectedFlights = [];
    public $selectAll = false;
    public $showBulkDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'airline' => ['except' => ''],
        'date' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingStatus()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingAirline()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingDate()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'airline', 'date']);
        $this->resetPage();
        $this->resetSelection();
    }

    public function resetSelection()
    {
        $this->selectedFlights = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedFlights = $this->flights->pluck('id')->toArray();
        } else {
            $this->selectedFlights = [];
        }
    }

    public function updatedSelectedFlights()
    {
        $this->selectAll = false;
    }

    public function confirmDelete($flightId)
    {
        $flight = Flight::find($flightId);
        if ($flight) {
            $this->flightToDelete = $flightId;
            $this->flightToDeleteNumber = $flight->flight_number;
            $this->showDeleteModal = true;
        }
    }

    public function deleteFlight()
    {
        try {
            $flight = Flight::findOrFail($this->flightToDelete);
            $flightNumber = $flight->flight_number;
            
            // Hapus fuel schedule terkait dulu
            $flight->fuelSchedule()->delete();
            
            // Hapus flight
            $flight->delete();
            
            // Reset modal state
            $this->resetDeleteModal();
            $this->resetSelection();
            
            session()->flash('success', "Flight {$flightNumber} berhasil dihapus!");
        } catch (\Exception $e) {
            $this->resetDeleteModal();
            session()->flash('error', 'Error deleting flight: ' . $e->getMessage());
        }
    }

    public function confirmBulkDelete()
    {
        if (count($this->selectedFlights) > 0) {
            $this->showBulkDeleteModal = true;
        }
    }

    public function bulkDeleteFlights()
    {
        try {
            $flightsToDelete = Flight::whereIn('id', $this->selectedFlights)->get();
            $deletedCount = 0;

            foreach ($flightsToDelete as $flight) {
                // Hapus fuel schedule terkait dulu
                $flight->fuelSchedule()->delete();
                
                // Hapus flight
                $flight->delete();
                $deletedCount++;
            }

            $this->resetBulkDeleteModal();
            $this->resetSelection();

            session()->flash('success', "{$deletedCount} flights berhasil dihapus!");
        } catch (\Exception $e) {
            $this->resetBulkDeleteModal();
            session()->flash('error', 'Error deleting flights: ' . $e->getMessage());
        }
    }

    public function resetDeleteModal()
    {
        $this->reset(['showDeleteModal', 'flightToDelete', 'flightToDeleteNumber']);
    }

    public function resetBulkDeleteModal()
    {
        $this->reset(['showBulkDeleteModal']);
    }

    public function getFlightsProperty()
    {
        return Flight::with(['createdBy', 'fuelSchedule.cro', 'fuelSchedule.shift'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('flight_number', 'like', '%' . $this->search . '%')
                      ->orWhere('airline_code', 'like', '%' . $this->search . '%')
                      ->orWhere('departure_airport', 'like', '%' . $this->search . '%')
                      ->orWhere('arrival_airport', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->airline, function ($query) {
                $query->where('airline_code', $this->airline);
            })
            ->when($this->date, function ($query) {
                $query->whereDate('scheduled_departure', $this->date);
            })
            ->latest()
            ->paginate($this->perPage);
    }

    public function render()
    {
        $flights = $this->flights;
        $airlines = Flight::distinct()->pluck('airline_code');
        $statuses = ['Scheduled', 'Completed', 'Cancelled', 'Delayed'];

        // Add stats data
        $stats = [
            'total_flights' => Flight::count(),
            'today_flights' => Flight::whereDate('scheduled_departure', today())->count(),
            'scheduled_flights' => Flight::where('status', 'Scheduled')->count(),
        ];

        return view('livewire.flights.flight-list', compact('flights', 'airlines', 'statuses', 'stats'));
    }
}