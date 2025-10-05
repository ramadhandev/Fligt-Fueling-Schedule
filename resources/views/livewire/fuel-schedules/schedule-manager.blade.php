<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">
                    @if($this->user->role === 'CRO')
                        My Schedules
                    @elseif($this->user->role === 'Pengawas')
                        Monitoring
                    @else
                        Fuel Schedule Manager
                    @endif
                </h1>
                <div class="text-sm text-gray-500">
                    Total: {{ $schedules->total() }} schedules
                </div>
            </div>

            <!-- Quick Stats -->
            @if(count($stats) > 0)
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-3 lg:grid-cols-5">
                @foreach($stats as $key => $value)
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            {{ ucfirst(str_replace('_', ' ', $key)) }}
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ $value }}
                        </dd>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Filters - Disederhanakan -->
            <div class="mt-6 bg-white shadow rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" wire:model.live="date" id="date" 
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
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

                    @if($this->user->role !== 'CRO')
                    <div>
                        <label for="shift" class="block text-sm font-medium text-gray-700">Shift</label>
                        <select wire:model.live="shift" id="shift" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Shifts</option>
                            @foreach($shifts as $shiftOption)
                                <option value="{{ $shiftOption->id }}">{{ $shiftOption->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="cro" class="block text-sm font-medium text-gray-700">CRO</label>
                        <select wire:model.live="cro" id="cro" 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All CROs</option>
                            @foreach($cros as $croOption)
                                <option value="{{ $croOption->id }}">{{ $croOption->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                <!-- Reset Filters -->
                @if($status || $date || $shift || $cro)
                <div class="mt-4 flex justify-end">
                    <button wire:click="resetFilters" 
                            class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Reset Filters
                    </button>
                </div>
                @endif
            </div>

            <!-- Schedules Table -->
            <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flight</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STD</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CRO & Shift</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($schedules as $schedule)
                            <tr class="hover:bg-gray-50">
                                <!-- Kolom Flight -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $schedule->flight->flight_number ?? 'N/A' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $schedule->flight->airline_code ?? 'N/A' }}
                                    </div>
                                </td>
                                
                                <!-- Kolom Route -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $schedule->flight->departure_airport ?? 'N/A' }} → {{ $schedule->flight->arrival_airport ?? 'N/A' }}
                                    </div>
                                </td>
                                
                                <!-- Kolom STD -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ optional($schedule->flight->scheduled_departure)->format('H:i') ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ optional($schedule->scheduled_fueling_time)->format('M j, H:i') ?? 'N/A' }}
                                    </div>
                                </td>

                                <!-- Kolom CRO & Shift -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $schedule->cro->name ?? 'Unassigned' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $schedule->shift->name ?? '-' }}
                                    </div>
                                </td>
                                
                                <!-- Kolom Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $schedule->status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $schedule->status === 'InProgress' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $schedule->status === 'Completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $schedule->status === 'Delayed' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ $schedule->status }}
                                    </span>
                                    @if($schedule->status === 'Completed' && $schedule->actual_fueling_time)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Completed: {{ $schedule->actual_fueling_time->format('H:i') }}
                                        </div>
                                    @endif
                                </td>
                                
                                <!-- Kolom Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if(in_array($this->user->role, ['CRS', 'Pengawas']))
                                        <button wire:click="openAssignModal({{ $schedule->id }})" 
                                                class="text-blue-600 hover:text-blue-900">
                                            Assign
                                        </button>
                                        <span class="text-gray-300">|</span>
                                        @endif
                                        <button wire:click="openStatusModal({{ $schedule->id }})" 
                                                class="text-green-600 hover:text-green-900">
                                            Status
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    @if($status || $date || $shift || $cro)
                                        No schedules found matching your filters.
                                    @else
                                        No fuel schedules found.
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $schedules->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    @if($showStatusModal)
    <div class="fixed inset-0 overflow-y-auto z-50">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Update Schedule Status</h3>
                
                <form wire:submit.prevent="updateStatus">
                    <div class="space-y-4">
                        <div>
                            <label for="newStatus" class="block text-sm font-medium text-gray-700">Status</label>
                            <select wire:model="newStatus" id="newStatus" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @foreach($statuses as $statusOption)
                                    <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                                @endforeach
                            </select>
                            @error('newStatus') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="actualFuelingTime" class="block text-sm font-medium text-gray-700">Actual Fueling Time</label>
                            <input type="datetime-local" wire:model="actualFuelingTime" id="actualFuelingTime" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Leave empty to use current time when status is set to Completed</p>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:col-start-2 sm:text-sm">
                            Update Status
                        </button>
                        <button type="button" 
                                wire:click="closeStatusModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Assign CRO Modal -->
    @if($showAssignModal)
    <div class="fixed inset-0 overflow-y-auto z-50">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Assign CRO to Schedule</h3>
                
                <form wire:submit.prevent="assignCRO">
                    <div class="space-y-4">
                        <div>
                            <label for="assignCroId" class="block text-sm font-medium text-gray-700">Select CRO</label>
                            <select wire:model="assignCroId" id="assignCroId" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Choose CRO</option>
                                @foreach($cros as $croOption)
                                    <option value="{{ $croOption->id }}">
                                        {{ $croOption->name }} 
                                        @if($croOption->phone_number)
                                            ({{ $croOption->phone_number }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('assignCroId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="assignShiftId" class="block text-sm font-medium text-gray-700">Select Shift</label>
                            <select wire:model="assignShiftId" id="assignShiftId" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Choose Shift</option>
                                @foreach($shifts as $shiftOption)
                                    <option value="{{ $shiftOption->id }}">{{ $shiftOption->name }}</option>
                                @endforeach
                            </select>
                            @error('assignShiftId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                            Assign CRO
                        </button>
                        <button type="button" 
                                wire:click="closeAssignModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>