<!-- resources/views/chat.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Clone</title>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body>
    <div x-data="chat()">
        <div>
            <div x-for="message in messages" :key="message.id">
                <div x-text="message.message"></div>
            </div>
        </div>
        <input x-model="newMessage" @keyup.enter="sendMessage">
    </div>

    <script>
        function chat() {
            return {
                messages: [],
                newMessage: '',
                init() {
                    Echo.private(`chat.${userId}`)
                        .listen('MessageSent', (e) => {
                            this.messages.push(e.message);
                        });
                },
                sendMessage() {
                    axios.post('/send-message', {
                        receiver_id: receiverId,
                        message: this.newMessage
                    }).then(response => {
                        this.messages.push(response.data);
                        this.newMessage = '';
                    });
                }
            }
        }
    </script>
</body>
</html>