@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Semua Notifikasi</h1>
            <div class="text-sm text-gray-500">
                Total: {{ $notifications->total() }} notifikasi
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($notifications->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($notifications as $notification)
                        <div class="p-6 hover:bg-gray-50 transition-colors duration-150 {{ is_null($notification->read_at) ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="text-lg font-medium text-gray-900">
                                        {{ $notification->title }}
                                    </p>
                                    <p class="text-gray-600 mt-1">
                                        {{ $notification->message }}
                                    </p>
                                    
                                    @if(isset($notification->data['flight_number']))
                                        <div class="mt-3 text-sm border border-gray-200 p-3 rounded">
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
                                    
                                    <p class="text-sm text-gray-500 mt-3">
                                        {{ $notification->created_at->format('d M Y H:i') }}
                                        ({{ $notification->created_at->diffForHumans() }})
                                    </p>
                                </div>
                                
                                @if(is_null($notification->read_at))
                                    <span class="ml-4 w-3 h-3 bg-blue-500 rounded-full flex-shrink-0"></span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="p-8 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 17h5l-5 5v-5zM8.586 3.586A2 2 0 0111.172 2h1.656a2 2 0 011.586.586L16 4h4a2 2 0 012 2v2a2 2 0 01-.586 1.414l-.828.828A2 2 0 0120 10.828V16a2 2 0 01-2 2H6a2 2 0 01-2-2v-5.172a2 2 0 01.586-1.414L3.586 9A2 2 0 013 7.586V5.586A2 2 0 014.586 4z" />
                    </svg>
                    <p class="mt-4 text-lg text-gray-500">Belum ada notifikasi</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection