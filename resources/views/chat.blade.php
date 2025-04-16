<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Chat Room
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div x-data="chatApp({{ $chat->id }})">
                        <div class="h-80 overflow-auto border p-3" x-ref="chatContainer">
                            <template x-for="message in messages" :key="message.id">
                                <div class="mb-2">
                                    <strong x-text="message.user.name"></strong>: 
                                    <span x-text="message.message"></span>
                                    <small class="text-gray-500" x-text="message.time"></small>
                                </div>
                            </template>
                        </div>
                        <div class="mt-4">
                            <input type="text" x-model="newMessage" @keyup.enter="sendMessage" class="border p-2 w-full">
                            <button @click="sendMessage" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded">Kirim</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script>
        function chatApp(chatId) {
            return {
                messages: [], 
                newMessage: '',
                isLoading: false,
                hasMore: true,
                async sendMessage() {
                    if (!this.newMessage.trim()) return;

                    const response = await fetch(`/chat/${chatId}/send`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({ message: this.newMessage })
                    });

                    const data = await response.json();
                    if (data.success) {
                        console.log(data.message);
                        this.messages.push({
                            id: data.message.id,
                            message: data.message.message,
                            user: { id: data.message.user.id, name: data.message.user.name },
                            time: data.message.created_at
                        });
                        this.newMessage = '';

                        this.$nextTick(() => {
                            this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight;
                        });
                    }
                },
                async loadMoreMessages() {
                    if (this.isLoading || !this.hasMore) return;

                    this.isLoading = true;

                    // Ambil ID pesan tertua yang ada
                    const oldestMessageId = this.messages[0]?.id;

                    // Fetch pesan lama
                    const response = await fetch(`/chat/${chatId}/messages?before=${oldestMessageId}`);
                    const data = await response.json();

                    console.log(response);
                    if (data.length > 0) {
                        // Tambahkan pesan lama ke awal array
                        this.messages.unshift(...data);
                    } else {
                        this.hasMore = false; // Tidak ada pesan lama lagi
                    }

                    this.isLoading = false;
                },
                init() {
                    fetch(`/chat/${chatId}/messages`)
                        .then(response => response.json())
                        .then(data => {
                            this.messages = Array.isArray(data) ? data : Object.values(data);
                            this.$nextTick(() => {
                                this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight;
                            });
                        });

                    Echo.private(`chat.${chatId}`)
                        .listen('MessageSent', (e) => {
                            console.log('Message received:', e);
                            if (!this.messages.some(msg => msg.id === e.id)) {
                                this.messages.push(e);

                                this.$nextTick(() => {
                                    this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight;
                                });
                            }
                        });

                    // Pasang event listener scroll setelah komponen diinisialisasi
                    this.$refs.chatContainer.addEventListener('scroll', () => {
                        console.log('Scroll detected:', this.$refs.chatContainer.scrollTop);
                        if (this.$refs.chatContainer.scrollTop === 0 && this.hasMore) {
                            console.log('Loading more messages...');
                            this.loadMoreMessages();
                        }
                    });
                }
            };
        }
    </script>
</x-app-layout>
