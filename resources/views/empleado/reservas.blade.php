<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestión de Reservas - SmartPool</title>

    <!-- Estilos -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.tailwindcss.min.css" rel="stylesheet">

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100" x-data="reservasApp()">
    <!-- Navbar -->
    <nav class="bg-blue-800 p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <a href="{{ route('dashboard.empleado') }}" class="text-white text-2xl font-bold">SmartPool</a>
            <div class="flex items-center space-x-4">
                <span class="text-white">{{ Auth::user()->Nombre }}</span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container mx-auto px-4 py-8">
        <!-- Encabezado y Botón Nueva Reserva -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Gestión de Reservas</h1>
            <button @click="openModal()"
                class="bg-green-600 hover:bg-green-500 text-white px-6 py-3 rounded-lg transition duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Reserva
            </button>
        </div>

        <!-- Tabla de Reservas -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6">
                <table id="tablaReservas" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                CI</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Horario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Piscina</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Adelanto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Descuento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reservasData as $reserva)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $reserva['id'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $reserva['cliente'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $reserva['ci'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $reserva['fecha'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $reserva['hora_inicio'] }} - {{ $reserva['hora_fin'] }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $reserva['piscina'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">Bs. {{ $reserva['adelanto'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">Bs. {{ $reserva['descuento'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                                                {{ $reserva['tipo'] === 'Individual' ? 'bg-green-100 text-green-800' :
                            ($reserva['tipo'] === 'Familiar' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                                                        {{ $reserva['tipo'] }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <button @click="editarReserva({{ json_encode($reserva) }})"
                                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                                        Editar
                                                    </button>
                                                    <button @click="eliminarReserva({{ $reserva['id'] }})"
                                                        class="text-red-600 hover:text-red-900">
                                                        Eliminar
                                                    </button>
                                                </td>
                                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal de Reserva -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
            @click.away="showModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <div class="relative bg-white rounded-lg max-w-3xl w-full mx-auto shadow-xl">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-2xl font-semibold text-gray-900"
                                x-text="isEditing ? 'Editar Reserva' : 'Nueva Reserva'"></h3>
                            <button @click="showModal = false" class="text-gray-400 hover:text-gray-500">
                                <span class="sr-only">Cerrar</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="guardarReserva" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- CI Cliente -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">CI Cliente</label>
                                <input type="text" x-model="formData.ci" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Fecha -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fecha de Reserva</label>
                                <input type="date" x-model="formData.fecha" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Hora Inicio -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Hora Inicio</label>
                                <input type="time" x-model="formData.hora_inicio" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Hora Fin -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Hora Fin</label>
                                <input type="time" x-model="formData.hora_fin" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Piscina -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Piscina</label>
                                <select x-model="formData.piscina" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione una piscina</option>
                                    @foreach($piscinas as $piscina)
                                        <option value="{{ $piscina->ID_Piscina }}">
                                            Piscina {{ $piscina->ID_Piscina }} - Capacidad: {{ $piscina->Capacidad }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipo de Reserva -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Reserva</label>
                                <select x-model="formData.tipo" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione un tipo</option>
                                    <option value="Individual">Individual</option>
                                    <option value="Familiar">Familiar</option>
                                    <option value="Clase">Clase</option>
                                </select>
                            </div>

                            <!-- Adelanto -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Adelanto (Bs)</label>
                                <input type="number" step="0.01" x-model="formData.adelanto" required min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Descuento -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descuento (Bs)</label>
                                <input type="number" step="0.01" x-model="formData.descuento" min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="showModal = false"
                                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        let tablaReservas;

        function initDataTable() {
            if ($.fn.DataTable.isDataTable('#tablaReservas')) {
                $('#tablaReservas').DataTable().destroy();
            }

            tablaReservas = $('#tablaReservas').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
                },
                responsive: true,
                order: [[0, 'desc']],
                pageLength: 10,
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
            });
        }

        function reservasApp() {
            return {
                showModal: false,
                isEditing: false,
                formData: {
                    id: null,
                    ci: '',
                    fecha: '',
                    hora_inicio: '',
                    hora_fin: '',
                    piscina: '',
                    adelanto: '',
                    descuento: '0',
                    tipo: ''
                },

                // Ya no necesitamos init() aquí
                openModal() {
                    this.isEditing = false;
                    this.resetForm();
                    this.showModal = true;
                },

                resetForm() {
                    this.formData = {
                        id: null,
                        ci: '',
                        fecha: '',
                        hora_inicio: '',
                        hora_fin: '',
                        piscina: '',
                        adelanto: '',
                        descuento: '0',
                        tipo: ''
                    };
                },



                editarReserva(reserva) {
                    this.isEditing = true;
                    this.formData = {
                        id: reserva.id,
                        ci: reserva.ci,
                        fecha: reserva.fecha,
                        hora_inicio: reserva.hora_inicio,
                        hora_fin: reserva.hora_fin,
                        piscina: reserva.piscina_id,
                        adelanto: reserva.adelanto,
                        descuento: reserva.descuento,
                        tipo: reserva.tipo
                    };
                    this.showModal = true;
                },

                async guardarReserva() {
                    try {
                        const url = this.isEditing
                            ? `/empleado/reservas/${this.formData.id}`
                            : '/empleado/reservas';

                        const method = this.isEditing ? 'PUT' : 'POST';

                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Error al procesar la solicitud');
                        }

                        // Mostrar mensaje de éxito
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });

                        // Cerrar modal y recargar tabla
                        this.showModal = false;
                        window.location.reload();

                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message,
                        });
                    }
                },

                async eliminarReserva(id) {
                    const result = await Swal.fire({
                        title: '¿Está seguro?',
                        text: "Esta acción no se puede revertir",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    });

                    if (result.isConfirmed) {
                        try {
                            const response = await fetch(`/empleado/reservas/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                throw new Error(data.message || 'Error al eliminar la reserva');
                            }

                            Swal.fire(
                                'Eliminado',
                                'La reserva ha sido eliminada.',
                                'success'
                            );

                            // Recargar la página para actualizar la tabla
                            window.location.reload();

                        } catch (error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error.message,
                            });
                        }
                    }
                },

                validarHorario() {
                    if (this.formData.hora_inicio && this.formData.hora_fin) {
                        if (this.formData.hora_fin <= this.formData.hora_inicio) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'La hora de fin debe ser posterior a la hora de inicio'
                            });
                            this.formData.hora_fin = '';
                        }
                    }
                },

                validarFecha() {
                    const hoy = new Date().toISOString().split('T')[0];
                    if (this.formData.fecha < hoy) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'La fecha debe ser igual o posterior a hoy'
                        });
                        this.formData.fecha = hoy;
                    }
                }
            };
        }

        // Inicializar tooltips y popovers de Bootstrap si los estás usando
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof bootstrap !== 'undefined') {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
                popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl);
                });
            }
        });
    </script>
    <!-- Scripts para DataTables -->
    <script>
        $(document).ready(function () {
            $('#tablaReservas').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
                },
                responsive: true,
                pageLength: 10,
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: false, targets: -1 } // Deshabilitar ordenamiento en la columna de acciones
                ],
                dom: '<"flex justify-between items-center mb-4"lf>rtip',
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
                initComplete: function () {
                    // Personalizar el aspecto de los controles de DataTables con Tailwind
                    $('.dataTables_length select').addClass('rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500');
                    $('.dataTables_filter input').addClass('rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500');
                    $('.dataTables_filter input').attr('placeholder', 'Buscar...');
                }
            });
        });
    </script>

    <!-- Alpine.js Initialization -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('reservasApp', reservasApp)
        });
    </script>
</body>

</html>