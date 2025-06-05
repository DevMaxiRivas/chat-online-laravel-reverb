<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
        // Asegurarse de que la relación user esté cargada
        $this->message->load('user');
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat.' . $this->message->room),
        ];
    }

    public function broadcastWith(): array
    {
        $data = [
            'message' => $this->message,
            'user' => $this->message->user,
            'timestamp' => now()->toISOString(),
        ];
        
        Log::info('MessageSent event broadcasting:', $data);
        
        return $data;
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}