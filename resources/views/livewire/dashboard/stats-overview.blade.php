<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            
            <!-- Stats Grid -->
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
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

            <!-- Quick Actions -->
            <div class="mt-8">
                <h2 class="text-lg font-medium text-gray-900">Quick Actions</h2>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @if(auth()->user()->role === 'CRS')
                    <a href="{{ route('flights.import') }}" class="bg-white p-6 rounded-lg shadow border border-gray-200 hover:border-blue-500 transition">
                        <h3 class="text-lg font-medium text-gray-900">Import Flight Data</h3>
                        <p class="mt-2 text-sm text-gray-500">Upload CSV file dengan data flight</p>
                    </a>
                    
                    <a href="{{ route('flights.index') }}" class="bg-white p-6 rounded-lg shadow border border-gray-200 hover:border-blue-500 transition">
                        <h3 class="text-lg font-medium text-gray-900">View All Flights</h3>
                        <p class="mt-2 text-sm text-gray-500">Lihat dan kelola data flight</p>
                    </a>

                    <a href="{{ route('fuel-schedules.manager') }}" class="bg-white p-6 rounded-lg shadow border border-gray-200 hover:border-blue-500 transition">
                        <h3 class="text-lg font-medium text-gray-900">Fuel Schedules</h3>
                        <p class="mt-2 text-sm text-gray-500">Kelola jadwal pengisian bahan bakar</p>
                    </a>
                    @endif

                    @if(auth()->user()->role === 'Pengawas')
                    <a href="{{ route('reports.shift') }}" class="bg-white p-6 rounded-lg shadow border border-gray-200 hover:border-blue-500 transition">
                        <h3 class="text-lg font-medium text-gray-900">Shift Reports</h3>
                        <p class="mt-2 text-sm text-gray-500">Laporan aktivitas shift</p>
                    </a>

                    <a href="{{ route('monitoring.index') }}" class="bg-white p-6 rounded-lg shadow border border-gray-200 hover:border-blue-500 transition">
                        <h3 class="text-lg font-medium text-gray-900">Monitoring</h3>
                        <p class="mt-2 text-sm text-gray-500">Pantau aktivitas pengisian</p>
                    </a>
                    @endif

                    @if(auth()->user()->role === 'CRO')
                    <a href="{{ route('my-schedules') }}" class="bg-white p-6 rounded-lg shadow border border-gray-200 hover:border-blue-500 transition">
                        <h3 class="text-lg font-medium text-gray-900">My Schedules</h3>
                        <p class="mt-2 text-sm text-gray-500">Lihat jadwal pengisian saya</p>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>