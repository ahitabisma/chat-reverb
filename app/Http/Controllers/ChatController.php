<?php 
namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Jobs\SendMessage;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', Auth::id())->get();
        $chats = Chat::whereHas('members', function ($query) {
            $query->where('user_id', Auth::id())->where('is_group', true);
        })->get();

        return view('dashboard', [
            'users' => $users,
            'chats' => $chats
        ]);
    }

    public function startChat($userId, $type)
    {
        $authUserId = Auth::id();
    
        if ($type === 'group') {
            // Cek apakah user sudah ada dalam grup yang sama
            $groupChat = Chat::where('is_group', true)
                ->whereHas('members', fn($query) => $query->where('user_id', $authUserId))
                ->whereHas('members', fn($query) => $query->where('user_id', $userId))
                ->first();
    
            if ($groupChat) {
                return redirect()->route('chat.show', $groupChat->id);
            }
    
            // Buat grup baru
            $chat = Chat::create([
                'is_group' => true,
                'name' => 'Grup Baru', // Opsional: bisa diubah sesuai input user
            ]);
    
            // Tambahkan user ke dalam grup
            $chat->members()->attach([$authUserId, $userId]);
    
        } elseif ($type === 'personal') {
            // Cek apakah chat personal sudah ada
            $chat = Chat::where('is_group', false)
                ->whereHas('members', fn($query) => $query->where('user_id', $authUserId))
                ->whereHas('members', fn($query) => $query->where('user_id', $userId))
                ->first();
    
            if ($chat) {
                return redirect()->route('chat.show', $chat->id);
            }
    
            // Buat chat personal baru
            $chat = Chat::create(['is_group' => false]);
            $chat->members()->attach([$authUserId, $userId]);
        } else {
            return back()->with('error', 'Tipe chat tidak valid.');
        }
    
        return redirect()->route('chat.show', $chat->id);
    }
    
    

    public function show($chatId)
    {
        $chat = Chat::with('members', 'messages.user')->findOrFail($chatId);

        if (!$chat->members->contains(Auth::id())) {
            abort(403, 'Anda tidak memiliki akses ke chat ini.');
        }

        return view('chat', compact('chat'));
    }

    public function getMessages($chatId)
    {
        $chat = Chat::findOrFail($chatId);
    
        if (!$chat->members->contains(Auth::id())) {
            abort(403, 'Anda tidak memiliki akses ke chat ini.');
        }
    
        // Ambil parameter `before` untuk load pesan lama
        $beforeId = request('before');
    
        $query = $chat->messages()
            ->with('user')
            ->orderBy('created_at', 'desc') // Ambil dari terbaru ke terlama
            ->take(50);
    
            if ($beforeId) {
                // Ambil created_at dari pesan yang memiliki ID tersebut
                $beforeMessage = $chat->messages()->where('id', $beforeId)->first();
        
                if ($beforeMessage) {
                    $query->where('created_at', '<', $beforeMessage->created_at);
                }
            }
    
        $messages = $query->get()->reverse()->values(); // Balikkan agar urutan tetap dari lama ke baru
    
        return response()->json($messages);
    }
    

    public function sendMessage(Request $request, $chatId)
    {
        $request->validate(['message' => 'required|string|max:500']);
    
        $chat = Chat::findOrFail($chatId);
    
        if (!$chat->members->contains(Auth::id())) {
            return response()->json(['error' => 'Anda bukan anggota chat ini'], 403);
        }
    
        $message = Message::create([
            'chat_id' => $chatId,
            'user_id' => Auth::id(),
            'message' => $request->message
        ]);
        Log::info('Broadcasting MessageSent event for chat ID: ' . $chatId);
        broadcast(new MessageSent($message))->toOthers();
    
        return response()->json([
            'success' => true,
            'message' => [
                'id'      => $message->id,
                'user'    => Auth::user()->name,
                'message' => $message->message,
                'time'    => $message->created_at->format('H:i')
            ]
        ]);
    }
}