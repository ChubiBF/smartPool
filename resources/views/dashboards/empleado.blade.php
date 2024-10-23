<!-- resources/views/dashboards/empleado.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Empleado - SmartPool</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="#" class="text-white text-2xl font-bold">SmartPool Admin</a>
            <div>
                <span class="text-white mr-4">Bienvenido, {{ Auth::user()->Nombre }}</span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-white text-blue-800 py-2 px-4 rounded hover:bg-blue-100">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8">
        <h1 class="text-3xl font-bold mb-6">Dashboard de Empleado</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Gestionar Reservas</h2>
                <p class="mb-4">Ver, confirmar, modificar o cancelar reservas.</p>
                <a href="{{ route('empleado.reservas') }}" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Gestionar Reservas</a>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Ventas y Compras</h2>
                <p class="mb-4">Registrar y visualizar ventas y compras diarias.</p>
                <a href="#" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">Ver Transacciones</a>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Gestionar Productos</h2>
                <p class="mb-4">Añadir, modificar o eliminar productos del inventario.</p>
                <a href="#" class="bg-yellow-500 text-white py-2 px-4 rounded hover:bg-yellow-600">Gestionar Productos</a>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Reportes Financieros</h2>
                <p class="mb-4">Generar reportes de ganancias e ingresos.</p>
                <a href="#" class="bg-purple-500 text-white py-2 px-4 rounded hover:bg-purple-600">Ver Reportes</a>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Gestionar Servicios</h2>
                <p class="mb-4">Actualizar y agregar nuevos servicios ofrecidos.</p>
                <a href="#" class="bg-indigo-500 text-white py-2 px-4 rounded hover:bg-indigo-600">Gestionar Servicios</a>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Gestionar Usuarios</h2>
                <p class="mb-4">Administrar cuentas de usuarios y permisos.</p>
                <a href="#" class="bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">Gestionar Usuarios</a>
            </div>
        </div>
    </div>
</body>
</html>