{{-- resources/views/livewire/flights/flight-list.blade.php --}}

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Flight Management</h1>
                <p class="text-sm text-gray-600 mt-1">Manage and monitor all flights</p>
            </div>
            <a href="{{ route('flights.import') }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Import Flights
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-500">Total Flights</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total_flights'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-500">Today's Flights</div>
                <div class="text-2xl font-bold text-blue-600">{{ $stats['today_flights'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-500">Scheduled</div>
                <div class="text-2xl font-bold text-green-600">{{ $stats['scheduled_flights'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-500">This Page</div>
                <div class="text-2xl font-bold text-purple-600">{{ $flights->count() }}</div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" wire:model.live="search" id="search" 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Flight number, airline, airport...">
                </div>
                
                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select wire:model.live="status" id="status" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        @foreach($statuses as $statusOption)
                            <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Airline Filter -->
                <div>
                    <label for="airline" class="block text-sm font-medium text-gray-700">Airline</label>
                    <select wire:model.live="airline" id="airline" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Airlines</option>
                        @foreach($airlines as $airlineCode)
                            <option value="{{ $airlineCode }}">{{ $airlineCode }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Filter -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" wire:model.live="date" id="date" 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Additional Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <!-- Per Page -->
                <div>
                    <label for="perPage" class="block text-sm font-medium text-gray-700">Per Page</label>
                    <select wire:model.live="perPage" id="perPage" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>

                <!-- Reset Filters -->
                <div class="flex items-end">
                    @if($search || $status || $airline || $date)
                    <button wire:click="resetFilters" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Reset Filters
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Bulk Actions -->
        @if(count($selectedFlights) > 0)
        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex justify-between items-center">
                <div class="text-sm text-blue-700">
                    {{ count($selectedFlights) }} flight(s) selected
                </div>
                <button wire:click="confirmBulkDelete" 
                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Delete Selected
                </button>
            </div>
        </div>
        @endif

        <!-- Flights Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            @if($flights->count() > 0)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flight</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned CRO</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($flights as $flight)
                        <tr class="hover:bg-gray-50">
                            <!-- Checkbox -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selectedFlights" value="{{ $flight->id }}" 
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            
                            <!-- Flight Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">
                                            {{ substr($flight->airline_code, 0, 2) }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $flight->flight_number }}</div>
                                        <div class="text-sm text-gray-500">{{ $flight->airline_code }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Route -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $flight->departure_airport }} → {{ $flight->arrival_airport }}
                                </div>
                                <div class="text-xs text-gray-500">Direct Flight</div>
                            </td>
                            
                            <!-- ✅ PERBAIKAN: Hanya tampilkan STD -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="font-medium">STD: {{ $flight->scheduled_departure->format('M j, H:i') }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Fueling: {{ $flight->fuelSchedule->scheduled_fueling_time->format('H:i') ?? 'Not scheduled' }}
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-2">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $flight->status === 'Scheduled' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $flight->status === 'Completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $flight->status === 'Cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $flight->status === 'Delayed' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                        {{ $flight->status }}
                                    </span>
                                    @if($flight->fuelSchedule)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $flight->fuelSchedule->status === 'Pending' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $flight->fuelSchedule->status === 'InProgress' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $flight->fuelSchedule->status === 'Completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $flight->fuelSchedule->status === 'Delayed' ? 'bg-red-100 text-red-800' : '' }}">
                                        Fuel: {{ $flight->fuelSchedule->status }}
                                    </span>
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Assigned CRO -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($flight->fuelSchedule && $flight->fuelSchedule->cro)
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 bg-green-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-xs">
                                            {{ strtoupper(substr($flight->fuelSchedule->cro->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $flight->fuelSchedule->cro->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $flight->fuelSchedule->shift->name ?? 'No shift' }}</div>
                                    </div>
                                </div>
                                @else
                                <span class="text-gray-400">Not assigned</span>
                                @endif
                            </td>
                            
                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button wire:click="confirmDelete({{ $flight->id }})" 
                                            class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                @if($search || $status || $airline || $date)
                                    No flights found matching your filters.
                                @else
                                    No flights found in the system.
                                    <a href="{{ route('flights.import') }}" class="text-blue-600 hover:text-blue-900 ml-1">
                                        Import some flights
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $flights->links() }}
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        @if($showDeleteModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg font-medium text-gray-900">Delete Flight</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to delete flight <strong>{{ $flightToDeleteNumber }}</strong>? This action cannot be undone.
                        </p>
                    </div>
                    <div class="flex justify-center space-x-3 mt-4">
                        <button wire:click="resetDeleteModal" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button wire:click="deleteFlight" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Delete Flight
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Bulk Delete Confirmation Modal -->
        @if($showBulkDeleteModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg font-medium text-gray-900">Delete Multiple Flights</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to delete <strong>{{ count($selectedFlights) }}</strong> selected flights? This action cannot be undone.
                        </p>
                    </div>
                    <div class="flex justify-center space-x-3 mt-4">
                        <button wire:click="resetBulkDeleteModal" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button wire:click="bulkDeleteFlights" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Delete All
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>