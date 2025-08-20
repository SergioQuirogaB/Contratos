<?php
require_once '../includes/functions.php';

// Verificar si está logueado
if (!isLoggedIn()) {
    redirect('../index.php');
}

// Obtener estadísticas básicas
require_once '../config/database.php';

try {
    if (isAdmin()) {
        // Estadísticas para administradores
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
        $total_usuarios = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as activos FROM usuarios WHERE activo = 1");
        $usuarios_activos = $stmt->fetch()['activos'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as admins FROM usuarios WHERE rol = 'admin'");
        $total_admins = $stmt->fetch()['admins'];
    } else {
        // Estadísticas para usuarios normales
        $total_usuarios = 0;
        $usuarios_activos = 0;
        $total_admins = 0;
    }
    
} catch (PDOException $e) {
    $error = 'Error al cargar estadísticas.';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Gestión Contractual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-gray-900">Sistema de Gestión Contractual</h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-user text-blue-600 text-sm"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <?php if (isAdmin()): ?>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Administrador
                            </span>
                        <?php endif; ?>
                    </div>
                    <a href="../logout.php" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">Acciones Rápidas</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-contract text-blue-600"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="mis-contratos.php" class="focus:outline-none">
                                <span class="absolute inset-0" aria-hidden="true"></span>
                                <p class="text-sm font-medium text-gray-900">Mis Contratos</p>
                                <p class="text-sm text-gray-500 truncate">Ver y gestionar contratos</p>
                            </a>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-arrow-right text-gray-400"></i>
                        </div>
                    </div>

                    <div class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-plus-circle text-green-600"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="nuevo-contrato.php" class="focus:outline-none">
                                <span class="absolute inset-0" aria-hidden="true"></span>
                                <p class="text-sm font-medium text-gray-900">Nuevo Contrato</p>
                                <p class="text-sm text-gray-500 truncate">Crear contrato nuevo</p>
                            </a>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-arrow-right text-gray-400"></i>
                        </div>
                    </div>

                    <div class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-cog text-purple-600"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="perfil.php" class="focus:outline-none">
                                <span class="absolute inset-0" aria-hidden="true"></span>
                                <p class="text-sm font-medium text-gray-900">Mi Perfil</p>
                                <p class="text-sm text-gray-500 truncate">Configurar cuenta</p>
                            </a>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-arrow-right text-gray-400"></i>
                        </div>
                    </div>

                    <div class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-bar text-indigo-600"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="estadisticas.php" class="focus:outline-none">
                                <span class="absolute inset-0" aria-hidden="true"></span>
                                <p class="text-sm font-medium text-gray-900">Reportes</p>
                                <p class="text-sm text-gray-500 truncate">Estadísticas y análisis</p>
                            </a>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-arrow-right text-gray-400"></i>
                        </div>
                    </div>

                    <div class="relative rounded-lg border border-yellow-300 bg-yellow-50 px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-yellow-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-yellow-500">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="proximos-vencer.php" class="focus:outline-none">
                                <span class="absolute inset-0" aria-hidden="true"></span>
                                <p class="text-sm font-medium text-gray-900">Alertas</p>
                                <p class="text-sm text-gray-500 truncate">Próximos a vencer</p>
                            </a>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-arrow-right text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info Section -->
        <div class="mt-8 grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Información del Sistema
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Versión:</span>
                            <span class="text-sm font-medium text-gray-900">1.0.0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Estado del Sistema:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1"></div>
                                Operativo
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Última actualización:</span>
                            <span class="text-sm font-medium text-gray-900"><?php echo date('d/m/Y'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>