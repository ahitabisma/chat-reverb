<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('List Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Pilih User untuk Chat</h3>
                    @forelse ($users as $user)
                        <div class="flex items-center justify-between p-2 border-b">
                            <span>{{ $user->name }}</span>
                            <a href="{{ route('chat.start', [$user->id, 'personal']) }}" 
                               class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                                Chat
                            </a>
                        </div>
                    @empty
                        <p>Tidak ada user lain yang tersedia.</p>
                    @endforelse
                </div>
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Pilih Grup untuk Chat</h3>
                    @forelse ($chats as $chat)
                        <div class="flex items-center justify-between p-2 border-b">
                            <span>{{ $chat->name }}</span> {{$chat->id}}
                            <a href="{{ route('chat.start', [$chat->id, 'group']) }}" 
                               class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                                Chat
                            </a>
                        </div>
                    @empty
                        <p>Tidak ada user lain yang tersedia.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
