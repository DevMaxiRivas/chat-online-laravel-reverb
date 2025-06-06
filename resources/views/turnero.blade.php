<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Página de Bienvenida</title>
    <!-- Enlace a Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light"
    style="
    background: url({{ asset('imagenes/banner.webp') }}) no-repeat center center fixed;
    background-size: cover;
    ">
    <div>
        {{-- Boton para cerrar sesion --}}

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger" style="position: absolute; top: 20px; right: 20px;">
                Cerrar Sesión
            </button>
        </form>
    </div>
    <div class="container d-flex align-items-center justify-content-center" style="height: 100vh;">
        <div class="text-center">
            <img src="{{ asset('imagenes/logo_hierronort.png') }}" alt="Logo Hierronort" height="120">
            <h1 class="text-white mt-3" style="font-size: 3rem;">
                TURNO EN PROCESO
            </h1>
            <p class="">
                <strong id="turno-number" class="text-white display-1" style="font-size: 20rem;">
                    0
                </strong>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    {{-- <script src={{ asset('js/websocket.js') }}></script> --}}
    {{-- <script src="/js/app.js"></script> --}}
    <script src="{{ asset('/build/assets/app-CaukWitC.js') }}"></script>
    <script>
        var turnoNumber = 0;

        function updateTurno() {
            // Simular la actualización del número de turno
            turnoNumber++;
            document.getElementById('turno-number').textContent = turnoNumber;

            // Actualizar el número de turno cada 5 segundos
            // setTimeout(updateTurno, 5000);
        }

        document.addEventListener('DOMContentLoaded', function() {

            const room = '{{ $room ?? 'general' }}';

            // Conectar a Echo con debugging
            console.log('🔌 Intentando conectar al canal:', `chat.${room}`);

            const channel = Echo.join(`chat.${room}`)
                .error((error) => {
                    console.error('❌ Error en el canal:', error);
                });

            // Debug adicional - Escuchar TODOS los eventos del canal
            channel.subscription.bind_global((eventName, data) => {
                console.log('🎯 Evento recibido:', eventName, data);
                // Llego el evento
                if (eventName === 'MessageSent') {
                    // addMessage(data.message);
                    updateTurno();
                }
            });

            console.log('📡 Canal creado:', channel);

        });
    </script>
</body>

</html>
