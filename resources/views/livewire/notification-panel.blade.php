<div class="relative">
    <!-- Notification Bell - ICON BELL YANG LEBIH BAGUS -->
    <button 
        wire:click="togglePanel" 
        class="relative p-2 text-white hover:text-gray-200 focus:outline-none transition-colors duration-200 group"
    >
        <!-- Bell Icon yang lebih modern -->
        <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
        </svg>
        
        <!-- Badge Count -->
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center animate-pulse text-[10px]">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Notification Panel -->
    @if($showPanel)
        <div class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-96 overflow-y-auto"
             x-data
             x-on:click.outside="$wire.showPanel = false">
            <div class="p-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Notifikasi</h3>
                    @if($unreadCount > 0)
                        <button 
                            wire:click="markAllAsRead" 
                            class="text-sm text-blue-600 hover:text-blue-800 transition-colors"
                        >
                            Tandai semua dibaca
                        </button>
                    @endif
                </div>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($notifications as $notification)
                    <div 
                        class="p-4 hover:bg-gray-50 transition-colors duration-150 cursor-pointer {{ is_null($notification->read_at) ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}"
                        wire:click="handleNotificationClick({{ $notification->id }}, {{ $notification->fuel_schedule_id }}, '{{ $notification->type }}')"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $notification->title }}
                                </p>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ $notification->message }}
                                </p>
                                
                                <!-- INFO FLIGHT -->
                                @if(isset($notification->data['flight_number']))
                                    <div class="mt-2 text-xs border border-gray-200 p-2 rounded">
                                        <div class="font-medium text-gray-900">Flight: {{ $notification->data['flight_number'] }}</div>
                                        @if(isset($notification->data['scheduled_fueling_time']))
                                            <div class="text-gray-700">Jadwal: {{ $notification->data['scheduled_fueling_time'] }}</div>
                                        @endif
                                        @if(isset($notification->data['new_status']))
                                            <div class="text-gray-700">Status: {{ $notification->data['new_status'] }}</div>
                                        @endif
                                        @if(isset($notification->data['shift_name']))
                                            <div class="text-gray-700">Shift: {{ $notification->data['shift_name'] }}</div>
                                        @endif
                                    </div>
                                @endif
                                
                                <p class="text-xs text-gray-500 mt-2">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                            
                            @if(is_null($notification->read_at))
                                <span class="ml-2 w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 animate-pulse"></span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Tidak ada notifikasi</p>
                    </div>
                @endforelse
            </div>

            @if($notifications->count() > 0)
                <div class="p-4 border-t border-gray-200 text-center">
                    <!-- TOMBOL LIHAT SEMUA NOTIFIKASI -->
                    <button 
                        wire:click="viewAllNotifications"
                        class="text-sm text-blue-600 hover:text-blue-800 transition-colors font-medium"
                    >
                        Lihat Semua Notifikasi ({{ $totalNotifications }})
                    </button>
                </div>
            @endif
        </div>
    @endif

    <!-- Audio Element untuk Notifikasi Sound -->
    <audio id="notification-sound" preload="auto">
        <!-- Gunakan file audio dari storage -->
        <source src="{{ Storage::url('sounds/win-notification.wav') }}" type="audio/wav">
        <!-- Fallback ke online sound -->
      
    </audio>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Listen untuk event play sound dari Livewire
    Livewire.on('play-notification-sound', () => {
        const audio = document.getElementById('notification-sound');
        if (audio) {
            audio.play().catch(e => console.log('Audio play failed:', e));
        }
    });

    // Polling untuk check new notifications setiap 10 detik
    setInterval(() => {
        Livewire.dispatch('refreshNotifications');
    }, 10000);
});
</script>