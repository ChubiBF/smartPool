// resources/js/reservas.js

$(document).ready(function() {
    // Configuración inicial de CSRF para todas las peticiones AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Inicialización de DataTables
    let tablaReservas = $("#tablaReservas").DataTable({
        "responsive": true,
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "columnDefs": [{
            "targets": -1,
            "data": null,
            "defaultContent": `
                <div class='btn-group'>
                    <button class='btn btn-primary btn-sm btnEditar'>Editar</button>
                    <button class='btn btn-danger btn-sm btnBorrar'>Borrar</button>
                </div>`
        }]
    });

    // Variables globales
    let reservaId = null;
    let opcion = null;

    // Botón Nuevo
    $("#btnNuevo").click(function() {
        reservaId = null;
        opcion = 'crear';
        $("#formReservas").trigger("reset");
        $(".modal-header").css("background-color", "#28a745");
        $(".modal-header").css("color", "white");
        $(".modal-title").text("Nueva Reserva");
        $("#modalReserva").modal("show");
    });

    // Botón Editar
    $(document).on("click", ".btnEditar", function() {
        let fila = $(this).closest("tr");
        reservaId = fila.find('td:eq(0)').text();
        
        // Obtener datos de la fila
        let fecha = fila.find('td:eq(1)').text();
        let hora_inicio = fila.find('td:eq(2)').text();
        let hora_fin = fila.find('td:eq(3)').text();
        let ci = fila.find('td:eq(4)').text();
        let piscina = fila.find('td:eq(5)').text();
        let adelanto = fila.find('td:eq(6)').text();
        let descuento = fila.find('td:eq(7)').text();
        let tipo = fila.find('td:eq(8)').text();

        // Llenar el formulario
        $("#ci").val(ci);
        $("#fecha").val(fecha);
        $("#hora_inicio").val(hora_inicio);
        $("#hora_fin").val(hora_fin);
        $("#piscina").val(piscina);
        $("#adelanto").val(adelanto);
        $("#descuento").val(descuento);
        $("#tipo").val(tipo);

        opcion = 'editar';
        $(".modal-header").css("background-color", "#007bff");
        $(".modal-header").css("color", "white");
        $(".modal-title").text("Editar Reserva");
        $("#modalReserva").modal("show");
    });

    // Botón Borrar
    $(document).on("click", ".btnBorrar", function() {
        let fila = $(this).closest("tr");
        let id = fila.find('td:eq(0)').text();
        
        Swal.fire({
            title: '¿Confirma eliminar la reserva?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/reservas/${id}`,
                    method: 'DELETE',
                    success: function(response) {
                        tablaReservas.row(fila).remove().draw();
                        Swal.fire(
                            'Eliminado',
                            'La reserva ha sido eliminada.',
                            'success'
                        );
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error',
                            'No se pudo eliminar la reserva.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Envío del formulario
    $("#formReservas").submit(function(e) {
        e.preventDefault();
        
        let url = opcion === 'crear' ? '/reservas' : `/reservas/${reservaId}`;
        let method = opcion === 'crear' ? 'POST' : 'PUT';
        
        let formData = {
            ci: $("#ci").val(),
            fecha: $("#fecha").val(),
            hora_inicio: $("#hora_inicio").val(),
            hora_fin: $("#hora_fin").val(),
            piscina: $("#piscina").val(),
            adelanto: $("#adelanto").val(),
            descuento: $("#descuento").val(),
            tipo: $("#tipo").val()
        };

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                let reserva = response.data;
                let rowData = [
                    reserva.ID_reserva,
                    reserva.Fecha_reserva,
                    reserva.Hora_inicio,
                    reserva.Hora_fin,
                    reserva.U_CI,
                    reserva.ID_Piscina,
                    reserva.Adelanto,
                    reserva.Descuento,
                    reserva.Tipo_reserva,
                    null // La columna de acciones se genera automáticamente
                ];

                if (opcion === 'crear') {
                    tablaReservas.row.add(rowData).draw();
                } else {
                    tablaReservas.row($(this).parents('tr')).data(rowData).draw();
                }

                $("#modalReserva").modal("hide");
                
                Swal.fire(
                    'Éxito',
                    opcion === 'crear' ? 'Reserva creada correctamente' : 'Reserva actualizada correctamente',
                    'success'
                );
            },
            error: function(xhr) {
                let errorMessage = xhr.responseJSON?.error || 'Ocurrió un error al procesar la solicitud';
                Swal.fire(
                    'Error',
                    errorMessage,
                    'error'
                );
            }
        });
    });

    // Validaciones en tiempo real
    $("#fecha").change(function() {
        let fecha = new Date($(this).val());
        let hoy = new Date();
        
        if (fecha < hoy) {
            Swal.fire({
                icon: 'warning',
                title: 'Fecha inválida',
                text: 'La fecha de reserva debe ser futura'
            });
            $(this).val('');
        }
    });

    // Validación de horas
    $("#hora_fin").change(function() {
        let horaInicio = $("#hora_inicio").val();
        let horaFin = $(this).val();
        
        if (horaInicio && horaFin <= horaInicio) {
            Swal.fire({
                icon: 'warning',
                title: 'Hora inválida',
                text: 'La hora de fin debe ser posterior a la hora de inicio'
            });
            $(this).val('');
        }
    });

    // Validación de montos
    $("#adelanto, #descuento").on('input', function() {
        let valor = parseFloat($(this).val());
        if (valor < 0) {
            $(this).val(0);
            Swal.fire({
                icon: 'warning',
                title: 'Valor inválido',
                text: 'El monto no puede ser negativo'
            });
        }
    });
});