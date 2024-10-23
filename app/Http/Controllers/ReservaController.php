<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ReservaController extends Controller
{
    public function index()
    {
        try {
            // Consulta básica sin relaciones complejas
            $reservas = DB::table('reserva as r')
                ->select(
                    'r.ID_reserva',
                    'r.Fecha_reserva',
                    'r.Hora_inicio',
                    'r.Hora_fin',
                    'r.Adelanto',
                    'r.Descuento',
                    'r.Tipo_reserva',
                    'c.U_CI',
                    'u.Nombre',
                    'u.Apellido',
                    'p.ID_Piscina',
                    'p.Capacidad'
                )
                ->join('cliente as c', 'r.ID_Cliente', '=', 'c.ID_Cliente')
                ->join('usuario as u', 'c.ID_Usuario', '=', 'u.ID_Usuario')
                ->join('piscina as p', 'r.ID_Piscina', '=', 'p.ID_Piscina')
                ->orderBy('r.Fecha_reserva', 'desc')
                ->get();

            // Formatear los datos para la vista
            $reservasData = $reservas->map(function ($reserva) {
                return [
                    'id' => $reserva->ID_reserva,
                    'cliente' => $reserva->Nombre . ' ' . $reserva->Apellido,
                    'ci' => $reserva->U_CI,
                    'fecha' => Carbon::parse($reserva->Fecha_reserva)->format('Y-m-d'),
                    'hora_inicio' => $reserva->Hora_inicio,
                    'hora_fin' => $reserva->Hora_fin,
                    'piscina' => "Piscina {$reserva->ID_Piscina} (Cap: {$reserva->Capacidad})",
                    'piscina_id' => $reserva->ID_Piscina,
                    'adelanto' => number_format($reserva->Adelanto, 2),
                    'descuento' => number_format($reserva->Descuento, 2),
                    'tipo' => $reserva->Tipo_reserva
                ];
            });

            // Obtener piscinas para el formulario
            $piscinas = DB::table('piscina')
                ->select('ID_Piscina', 'Capacidad', 'Precio')
                ->get();

            return view('empleado.reservas', compact('reservasData', 'piscinas'));

        } catch (\Exception $e) {
            Log::error('Error en ReservaController@index: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las reservas: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'ci' => 'required',
                'fecha' => 'required|date',
                'hora_inicio' => 'required',
                'hora_fin' => 'required',
                'piscina' => 'required|exists:piscina,ID_Piscina',
                'adelanto' => 'required|numeric|min:0',
                'descuento' => 'nullable|numeric|min:0',
                'tipo' => 'required'
            ]);

            // Verificar el cliente
            $cliente = DB::table('cliente')->where('U_CI', $request->ci)->first();
            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el cliente con el CI proporcionado'
                ], 404);
            }

            // Verificar disponibilidad de la piscina
            $existeReserva = DB::table('reserva')
                ->where('Fecha_reserva', $request->fecha)
                ->where('ID_Piscina', $request->piscina)
                ->where(function($query) use ($request) {
                    $query->whereBetween('Hora_inicio', [$request->hora_inicio, $request->hora_fin])
                          ->orWhereBetween('Hora_fin', [$request->hora_inicio, $request->hora_fin])
                          ->orWhere(function($q) use ($request) {
                              $q->where('Hora_inicio', '<=', $request->hora_inicio)
                                ->where('Hora_fin', '>=', $request->hora_fin);
                          });
                })->exists();

            if ($existeReserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'La piscina no está disponible en ese horario'
                ], 422);
            }

            // Obtener el ID del empleado actual
            $empleado = DB::table('empleado')
                ->where('ID_Usuario', Auth::id())
                ->first();

            if (!$empleado) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la información del empleado'
                ], 404);
            }

            // Crear la reserva
            $reservaId = DB::table('reserva')->insertGetId([
                'Fecha_reserva' => $request->fecha,
                'Hora_inicio' => $request->hora_inicio,
                'Hora_fin' => $request->hora_fin,
                'Adelanto' => $request->adelanto,
                'Descuento' => $request->descuento ?? 0,
                'Tipo_reserva' => $request->tipo,
                'ID_Cliente' => $cliente->ID_Cliente,
                'ID_Piscina' => $request->piscina,
                'ID_empleado' => $empleado->ID_empleado
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reserva creada exitosamente',
                'reservaId' => $reservaId
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ReservaController@store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'ci' => 'required',
                'fecha' => 'required|date',
                'hora_inicio' => 'required',
                'hora_fin' => 'required',
                'piscina' => 'required|exists:piscina,ID_Piscina',
                'adelanto' => 'required|numeric|min:0',
                'descuento' => 'nullable|numeric|min:0',
                'tipo' => 'required'
            ]);

            // Verificar el cliente
            $cliente = DB::table('cliente')->where('U_CI', $request->ci)->first();
            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el cliente con el CI proporcionado'
                ], 404);
            }

            // Verificar disponibilidad excluyendo la reserva actual
            $existeReserva = DB::table('reserva')
                ->where('Fecha_reserva', $request->fecha)
                ->where('ID_Piscina', $request->piscina)
                ->where('ID_reserva', '!=', $id)
                ->where(function($query) use ($request) {
                    $query->whereBetween('Hora_inicio', [$request->hora_inicio, $request->hora_fin])
                          ->orWhereBetween('Hora_fin', [$request->hora_inicio, $request->hora_fin])
                          ->orWhere(function($q) use ($request) {
                              $q->where('Hora_inicio', '<=', $request->hora_inicio)
                                ->where('Hora_fin', '>=', $request->hora_fin);
                          });
                })->exists();

            if ($existeReserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'La piscina no está disponible en ese horario'
                ], 422);
            }

            // Actualizar la reserva
            DB::table('reserva')
                ->where('ID_reserva', $id)
                ->update([
                    'Fecha_reserva' => $request->fecha,
                    'Hora_inicio' => $request->hora_inicio,
                    'Hora_fin' => $request->hora_fin,
                    'Adelanto' => $request->adelanto,
                    'Descuento' => $request->descuento ?? 0,
                    'Tipo_reserva' => $request->tipo,
                    'ID_Cliente' => $cliente->ID_Cliente,
                    'ID_Piscina' => $request->piscina
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Reserva actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ReservaController@update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = DB::table('reserva')->where('ID_reserva', $id)->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la reserva'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Reserva eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ReservaController@destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la reserva: ' . $e->getMessage()
            ], 500);
        }
    }
}