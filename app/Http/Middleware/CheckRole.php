<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Verificar si el usuario está autenticado
        if (!$request->user()) {
            return redirect('/login');
        }

        // Verificar el rol del usuario
        if($request->user()->ID_Rol != $this->getRoleId($role)) {
            return redirect('/')->with('error', 'No tienes permiso para acceder a esta página.');
        }

        return $next($request);
    }

    private function getRoleId($roleName)
    {
        $roles = [
            'cliente' => 1,
            'empleado' => 2
            // Agrega más roles si los tienes
        ];

        return $roles[strtolower($roleName)] ?? null;
    }
}