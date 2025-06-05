@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 text-white p-4">
                <h1 class="text-xl font-semibold">Chat en Línea</h1>
                <p class="text-blue-100">Sala: {{ $room ?? 'general' }}</p>
            </div>

            <!-- Usuarios en línea -->
            <div class="bg-gray-50 p-3 border-b">
                <div class="text-sm text-gray-600">
                    Usuarios en línea: <span id="online-count">0</span>
                </div>
                <div id="online-users" class="flex flex-wrap gap-2 mt-2"></div>
            </div>

            <!-- Mensajes -->
            <div id="messages-container" class="h-96 overflow-y-auto p-4 space-y-3">
                @foreach($messages as $message)
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                            {{ substr($message->user->name, 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-semibold text-gray-900">{{ $message->user->name }}</span>
                                <span class="text-xs text-gray-500">{{ $message->created_at->format('H:i') }}</span>
                            </div>
                            <p class="text-gray-700 mt-1">{{ $message->content }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Formulario de mensaje -->
            <div class="border-t p-4">
                <form id="message-form" class="flex space-x-3">
                    @csrf
                    <input type="hidden" name="room" value="{{ $room ?? 'general' }}">
                    <input 
                        type="text" 
                        name="content" 
                        id="message-content"
                        placeholder="Escribe tu mensaje..."
                        class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                        maxlength="1000"
                    >
                    <button 
                        type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        Enviar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.getElementById('messages-container');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-content');
    const onlineUsersContainer = document.getElementById('online-users');
    const onlineCountElement = document.getElementById('online-count');
    
    const room = '{{ $room ?? "general" }}';
    
    // Conectar a Echo con debugging
    console.log('🔌 Intentando conectar al canal:', `chat.${room}`);
    
    const channel = Echo.join(`chat.${room}`)
        .here((users) => {
            console.log('👥 Usuarios aquí:', users);
            updateOnlineUsers(users);
        })
        .joining((user) => {
            console.log('✅ Usuario se unió:', user.name);
            updateOnlineUsers(channel.subscription.members);
        })
        .leaving((user) => {
            console.log('❌ Usuario se fue:', user.name);
            updateOnlineUsers(channel.subscription.members);
        })
        .listen('MessageSent', (e) => {
            console.log('📩 Mensaje recibido:', e);
            // addMessage(e.message);
        })
        .listenForWhisper('typing', (e) => {
            console.log('👀 Usuario escribiendo:', e);
        })
        .error((error) => {
            console.error('❌ Error en el canal:', error);
        });
    
    // Debug adicional - Escuchar TODOS los eventos del canal
    channel.subscription.bind_global((eventName, data) => {
        console.log('🎯 Evento recibido:', eventName, data);
        // Llego el evento
        if (eventName === 'MessageSent')
            addMessage(data.message);
    });
    
    console.log('📡 Canal creado:', channel);

    // Enviar mensaje
    messageForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const content = messageInput.value.trim();
        if (!content) return;
        
        console.log('📤 Enviando mensaje:', { content, room });
        
        try {
            const response = await fetch('{{ route("chat.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    content: content,
                    room: room
                })
            });
            
            const data = await response.json();
            console.log('✅ Respuesta del servidor:', data);
            
            if (response.ok && data.success) {
                // El mensaje se agregará automáticamente via WebSocket
                // Solo limpiamos el input
                messageInput.value = '';
                console.log('✅ Mensaje enviado exitosamente');
            } else {
                console.error('❌ Error en la respuesta:', data);
            }
        } catch (error) {
            console.error('❌ Error enviando mensaje:', error);
        }
    });

    function addMessage(message) {
        const messageElement = document.createElement('div');
        messageElement.className = 'flex items-start space-x-3';
        messageElement.innerHTML = `
            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                ${message.user.name.charAt(0)}
            </div>
            <div class="flex-1">
                <div class="flex items-center space-x-2">
                    <span class="font-semibold text-gray-900">${message.user.name}</span>
                    <span class="text-xs text-gray-500">${new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}</span>
                </div>
                <p class="text-gray-700 mt-1">${message.content}</p>
            </div>
        `;
        
        messagesContainer.appendChild(messageElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function updateOnlineUsers(users) {
        onlineCountElement.textContent = Object.keys(users).length;
        
        onlineUsersContainer.innerHTML = '';
        Object.values(users).forEach(user => {
            const userElement = document.createElement('span');
            userElement.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800';
            userElement.textContent = user.name;
            onlineUsersContainer.appendChild(userElement);
        });
    }

    // Auto-scroll al final
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
});
</script>
@endsection