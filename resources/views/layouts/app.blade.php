<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel Management System</title>
    
    <!-- META TAGS UNTUK NOTIFIKASI -->
    <meta name="user-id" content="{{ Auth::check() ? Auth::id() : '' }}">
    <meta name="broadcast-driver" content="{{ config('broadcasting.default', 'log') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    @livewireStyles
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-bold">Fuel Management System</h1>
                    @auth
                        <span class="bg-blue-500 px-2 py-1 rounded text-sm">
                            {{ auth()->user()->role }} - {{ auth()->user()->name }}
                        </span>
                    @endauth
                </div>
                
                @auth
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="hover:bg-blue-500 px-3 py-2 rounded">Dashboard</a>
                    
                    @if(auth()->user()->role === 'CRS' || auth()->user()->role === 'Pengawas')
                        <a href="{{ route('users.index') }}" class="hover:bg-blue-500 px-3 py-2 rounded">Users</a>
                    @endif
                    
                    @if(auth()->user()->role === 'CRS')
                        <a href="{{ route('flights.import') }}" class="hover:bg-blue-500 px-3 py-2 rounded">Import Flight</a>
                        <a href="{{ route('flights.index') }}" class="hover:bg-blue-500 px-3 py-2 rounded">Data Flight</a>
                        <a href="{{ route('fuel-schedules.manager') }}" class="hover:bg-blue-500 px-3 py-2 rounded">Fuel Schedules</a>
                        
                        <!-- ✅ NOTIFICATION PANEL UNTUK CRS - DI SEBELAH FUEL SCHEDULES -->
                        <livewire:notification-panel />
                    @endif
                    
                    @if(auth()->user()->role === 'Pengawas')
                        <a href="{{ route('reports.shift') }}" class="hover:bg-blue-500 px-3 py-2 rounded">Laporan Shift</a>
                        <a href="{{ route('monitoring.index') }}" class="hover:bg-blue-500 px-3 py-2 rounded">Monitoring</a>
                        
                        <!-- ✅ NOTIFICATION PANEL UNTUK PENGAWAS - DI SEBELAH MONITORING -->
                        <livewire:notification-panel />
                    @endif

                    @if(auth()->user()->role === 'CRO')
                        <a href="{{ route('my-schedules') }}" class="hover:bg-blue-500 px-3 py-2 rounded">Jadwal Saya</a>
                        
                        <!-- ✅ NOTIFICATION PANEL UNTUK CRO - DI SEBELAH JADWAL SAYA -->
                        <livewire:notification-panel />
                    @endif
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="hover:bg-blue-500 px-3 py-2 rounded">Logout</button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 px-4">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    @livewireScripts
</body>
</html>