<?php
require_once '../includes/functions.php';

// Verificar si está logueado
if (!isLoggedIn()) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    $email = cleanInput($_POST['email']);
    $password_actual = $_POST['password_actual'];
    $password_nueva = $_POST['password_nueva'];
    $password_confirmar = $_POST['password_confirmar'];
    
    if (empty($nombre) || empty($email)) {
        $error = 'Por favor, completa los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, ingresa un email válido.';
    } else {
        // Aquí puedes agregar la lógica para actualizar el perfil
        $success = 'Perfil actualizado exitosamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Sistema de Gestión Contractual</title>
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
                        <h1 class="text-xl font-bold text-gray-900">Mi Perfil</h1>
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
                    <a href="home.php" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver al Dashboard
                    </a>
                    <a href="../logout.php" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Alertas -->
        <?php if ($error): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Información de la Cuenta -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-info-circle text-purple-500 mr-2"></i>
                        <h3 class="text-lg font-medium text-gray-900">Información de la Cuenta</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tipo de Usuario</dt>
                            <dd class="mt-1">
                                <?php if (isAdmin()): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-crown mr-1"></i>
                                        Administrador
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-user mr-1"></i>
                                        Usuario
                                    </span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Estado de la Cuenta</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1"></div>
                                    Activa
                                </span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Último Acceso</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo date('d/m/Y H:i'); ?></dd>
                        </div>
                        
                        <?php if (isset($_SESSION['user_created_at'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Fecha de Registro</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($_SESSION['user_created_at'])); ?></dd>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Formulario de Perfil -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center">
                            <i class="fas fa-user-circle text-blue-500 text-xl mr-3"></i>
                            <h3 class="text-lg font-medium text-gray-900">Editar Perfil</h3>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <form method="POST" action="" class="space-y-6">
                            <!-- Información Personal -->
                            <div>
                                <div class="flex items-center mb-4">
                                    <i class="fas fa-user text-blue-500 mr-2"></i>
                                    <h4 class="text-md font-medium text-gray-900">Datos Personales</h4>
                                </div>
                                
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="nombre" class="block text-sm font-medium text-gray-700">
                                            Nombre Completo <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">
                                            Email <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" required
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Cambio de Contraseña -->
                            <div class="border-t border-gray-200 pt-6">
                                <div class="flex items-center mb-4">
                                    <i class="fas fa-lock text-green-500 mr-2"></i>
                                    <h4 class="text-md font-medium text-gray-900">Cambiar Contraseña</h4>
                                </div>
                                
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div>
                                            <label for="password_actual" class="block text-sm font-medium text-gray-700">
                                                Contraseña Actual
                                            </label>
                                            <input type="password" id="password_actual" name="password_actual"
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                                   placeholder="Contraseña actual">
                                        </div>
                                        
                                        <div>
                                            <label for="password_nueva" class="block text-sm font-medium text-gray-700">
                                                Nueva Contraseña
                                            </label>
                                            <input type="password" id="password_nueva" name="password_nueva"
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                                   placeholder="Nueva contraseña">
                                        </div>
                                        
                                        <div>
                                            <label for="password_confirmar" class="block text-sm font-medium text-gray-700">
                                                Confirmar
                                            </label>
                                            <input type="password" id="password_confirmar" name="password_confirmar"
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                                   placeholder="Confirmar contraseña">
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 bg-blue-50 border-l-4 border-blue-400 p-3">
                                        <p class="text-xs text-blue-700">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Deja los campos vacíos si no deseas cambiar tu contraseña.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botón de envío -->
                            <div class="border-t border-gray-200 pt-6">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <i class="fas fa-save mr-2"></i>
                                    Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
