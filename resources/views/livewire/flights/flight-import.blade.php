<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900">Import Flight Data</h1>
            
            <!-- CSV Import Section -->
            <div class="mt-6 bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Import dari CSV File</h2>
                
                <form wire:submit.prevent="importCSV">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">CSV File</label>
                            <input type="file" wire:model="csvFile" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('csvFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                <span wire:loading.remove>Import CSV</span>
                                <span wire:loading>Importing...</span>
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- ✅ CSV Format Info - UPDATE FORMAT -->
                <div class="mt-6 p-4 bg-gray-50 rounded-md">
                    <h3 class="font-medium text-gray-900">Format CSV yang didukung:</h3>
                    <pre class="mt-2 text-sm text-gray-600">
flight_number,airline_code,departure_airport,arrival_airport,scheduled_departure
GA-201,GAR,CGK,DPS,2025-09-27 08:00:00
JT-305,LNI,SUB,CGK,2025-09-27 09:30:00
                    </pre>
                    <p class="text-sm text-gray-600 mt-2">
                        <strong>Note:</strong> Format CSV sekarang hanya 5 kolom (tanpa ETA). Untuk assign CRO dan Shift, gunakan form manual input di bawah.
                    </p>
                </div>
            </div>
            
            <!-- Manual Input Section -->
            <div class="mt-6 bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Input Manual Flight</h2>
                    <button wire:click="toggleManualForm" 
                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ $showManualForm ? 'Tutup Form' : 'Tambah Manual' }}
                    </button>
                </div>
                
                @if($showManualForm)
                <form wire:submit.prevent="addManualFlight">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label for="flight_number" class="block text-sm font-medium text-gray-700">Flight Number</label>
                            <input type="text" wire:model="flight_number" id="flight_number" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('flight_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label for="airline_code" class="block text-sm font-medium text-gray-700">Airline Code</label>
                            <input type="text" wire:model="airline_code" id="airline_code" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('airline_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label for="departure_airport" class="block text-sm font-medium text-gray-700">Departure</label>
                            <input type="text" wire:model="departure_airport" id="departure_airport" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('departure_airport') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label for="arrival_airport" class="block text-sm font-medium text-gray-700">Arrival</label>
                            <input type="text" wire:model="arrival_airport" id="arrival_airport" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('arrival_airport') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- ✅ HANYA STD SAJA -->
                        <div>
                            <label for="scheduled_departure" class="block text-sm font-medium text-gray-700">Scheduled Departure (STD)</label>
                            <input type="datetime-local" wire:model="scheduled_departure" id="scheduled_departure" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('scheduled_departure') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- ❌ HAPUS FIELD SCHEDULED ARRIVAL (ETA) -->
                        
                        <!-- Field untuk pilih CRO -->
                        <div>
                            <label for="cro_id" class="block text-sm font-medium text-gray-700">Assign CRO (Optional)</label>
                            <select wire:model="cro_id" id="cro_id" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select CRO</option>
                                @foreach($cros as $cro)
                                    <option value="{{ $cro->id }}">{{ $cro->name }}</option>
                                @endforeach
                            </select>
                            @error('cro_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Field untuk pilih Shift -->
                        <div>
                            <label for="shift_id" class="block text-sm font-medium text-gray-700">Assign Shift (Optional)</label>
                            <select wire:model="shift_id" id="shift_id" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Shift</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                @endforeach
                            </select>
                            @error('shift_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Tambah Flight
                        </button>
                    </div>
                </form>
                @endif
            </div>

            <!-- Recent Flights -->
            <div class="mt-6 bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Flights</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flight</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STD</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fueling Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CRO</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentFlights as $flight)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $flight->flight_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $flight->airline_code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $flight->departure_airport }} → {{ $flight->arrival_airport }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $flight->scheduled_departure->format('M j, H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $flight->fuelSchedule->scheduled_fueling_time->format('M j, H:i') ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ $flight->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $flight->fuelSchedule->cro->name ?? 'Not assigned' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No flights imported yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>