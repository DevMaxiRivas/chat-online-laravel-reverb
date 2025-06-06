<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $messages = Message::with('user')
            ->where('room', 'general')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return view('chat.index', compact('messages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'room' => 'required|string|max:50',
        ]);

        $message = Message::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'room' => $request->room,
        ]);

        // Cargar la relación user antes de enviar el evento
        $message->load('user');

        // Enviar el evento a todos los usuarios (incluyendo el remitente para debug)
        broadcast(new MessageSent($message));

        return response()->json([
            'message' => $message,
            'success' => true,
        ]);
    }

    public function room($room)
    {
        $messages = Message::with('user')
            ->where('room', $room)
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return view('chat.index', compact('messages', 'room'));
    }

    public function turnero()
    {
        return view('turnero');
    }
}