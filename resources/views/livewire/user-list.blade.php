{{-- resources/views/livewire/user-list.blade.php --}}
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            <div class="text-sm text-gray-500">
                Total: {{ $stats['total_users'] }} users
            </div>
               <a href="{{ route('users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Add New User
                    </a>
        </div>

        <!-- Stats Cards -->
        <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-500">Total Users</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-500">CRS</div>
                <div class="text-2xl font-bold text-purple-600">{{ $stats['crs_count'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-500">Pengawas</div>
                <div class="text-2xl font-bold text-blue-600">{{ $stats['pengawas_count'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-500">CRO</div>
                <div class="text-2xl font-bold text-green-600">{{ $stats['cro_count'] }}</div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="mt-6 bg-white shadow rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" wire:model.live="search" id="search" 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Search by name, email, phone...">
                </div>
                
                <!-- Role Filter -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                    <select wire:model.live="role" id="role" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Roles</option>
                        @foreach($roles as $roleOption)
                            <option value="{{ $roleOption }}">{{ $roleOption }}</option>
                        @endforeach
                    </select>
                </div>

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
            </div>

            <!-- Reset Filters -->
            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    @if($search || $role)
                        Filters active
                    @endif
                </div>
                @if($search || $role)
                <button wire:click="resetFilters" 
                        class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Reset Filters
                </button>
                @endif
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Users Table -->
        <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Info</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <!-- User Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Role -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $user->role === 'CRS' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $user->role === 'Pengawas' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $user->role === 'CRO' ? 'bg-green-100 text-green-800' : '' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            
                            <!-- Contact -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->phone_number ?? '-' }}
                            </td>
                            
                           
                            <!-- Registered Date -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->format('M j, Y') }}
                            </td>
                            
                           <!-- Actions -->
<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
    <div class="flex space-x-2">
        <!-- Tombol Edit -->
        <a href="{{ route('users.edit', $user) }}" class="text-blue-600 hover:text-blue-900">
            Edit
        </a>
        
        <!-- Tombol Delete -->
        <button 
            wire:click="deleteUser({{ $user->id }})" 
            onclick="return confirm('Are you sure you want to delete {{ $user->name }}?')"
            class="text-red-600 hover:text-red-900"
            {{ $user->id === auth()->id() ? 'disabled' : '' }}
        >
            Delete
        </button>
    </div>
</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                @if($search || $role)
                                    No users found matching your filters.
                                @else
                                    No users found in the system.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>