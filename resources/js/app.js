// import './bootstrap';

// import Alpine from 'alpinejs';

// window.Alpine = Alpine;

// Alpine.start();
import './bootstrap';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    auth: {
        headers: {
            Authorization: `Bearer ${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}`,
        },
    },
});

// Debug: Mostrar información de conexión
window.Echo.connector.pusher.connection.bind('connected', function() {
    console.log('✅ Conectado a Reverb WebSocket');
});

window.Echo.connector.pusher.connection.bind('disconnected', function() {
    console.log('❌ Desconectado de Reverb WebSocket');
});

window.Echo.connector.pusher.connection.bind('error', function(err) {
    console.error('❌ Error de conexión WebSocket:', err);
});

console.log('🔧 Configuración Echo:', {
    key: import.meta.env.VITE_REVERB_APP_KEY,
    host: import.meta.env.VITE_REVERB_HOST,
    port: import.meta.env.VITE_REVERB_PORT,
    scheme: import.meta.env.VITE_REVERB_SCHEME
});

// Verificar que las variables de entorno se estén cargando
if (!import.meta.env.VITE_REVERB_APP_KEY) {
    console.error('❌ VITE_REVERB_APP_KEY no está definida');
}