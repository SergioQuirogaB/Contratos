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
  <body class="bg-gray-50 min-h-screen flex flex-col">
     <!-- Header -->
     <header class="bg-white shadow-sm border-b border-gray-200">
         <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
             <div class="flex justify-between items-center h-16">
                 <div class="flex items-center">
                     <div class="flex-shrink-0">
                                                   <h1 class="text-xl font-bold"><span class="text-blue-800">K</span><span class="text-blue-400">O</span><span class="text-blue-800">NTRATOS</span></h1>
                     </div>
                 </div>
                 <div class="flex items-center space-x-4">
                     <div class="flex items-center space-x-2">
                         <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                             <i class="fas fa-user text-blue-600 text-sm"></i>
                         </div>
                         <a href="perfil.php" class="text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200 cursor-pointer">
                             <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                         </a>
                         <?php if (isAdmin()): ?>
                             <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                 Administrador
                             </span>
                         <?php endif; ?>
                     </div>
                     <button id="toggle-sidebar" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">
                         <i class="fas fa-chevron-right mr-2"></i>
                         Menú
                     </button>
                     <a href="../logout.php" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                         <i class="fas fa-sign-out-alt mr-2"></i>
                         Cerrar Sesión
                     </a>
                 </div>
             </div>
         </div>
     </header>

     <!-- Main Layout with Sidebar -->
     <div class="flex flex-1">
         <!-- Sidebar -->
         <aside id="sidebar" class="w-0 overflow-hidden bg-white shadow-lg transition-all duration-300 ease-in-out">
             <div class="p-6">
                 <nav class="space-y-2">
                     <a href="mis-contratos.php" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors duration-200 group">
                         <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-200">
                             <i class="fas fa-file-contract text-blue-600 text-sm"></i>
                         </div>
                         <div>
                             <p class="font-medium">Mis Contratos</p>
                         </div>
                     </a>

                     <a href="nuevo-contrato.php" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors duration-200 group">
                         <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-green-200">
                             <i class="fas fa-plus-circle text-green-600 text-sm"></i>
                         </div>
                         <div>
                             <p class="font-medium">Nuevo Contrato</p>
                         </div>
                     </a>

                     <a href="estadisticas.php" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-700 transition-colors duration-200 group">
                         <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-indigo-200">
                             <i class="fas fa-chart-bar text-indigo-600 text-sm"></i>
                         </div>
                         <div>
                             <p class="font-medium">Reportes</p>
                         </div>
                     </a>

                     <a href="proximos-vencer.php" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-yellow-50 hover:text-yellow-700 transition-colors duration-200 group">
                         <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-yellow-200">
                             <i class="fas fa-exclamation-triangle text-yellow-600 text-sm"></i>
                         </div>
                         <div>
                             <p class="font-medium">Alertas</p>
                         </div>
                     </a>
                 </nav>
             </div>
         </aside>

         <!-- Main Content -->
         <main id="main-content" class="flex-1 p-8 transition-all duration-300 ease-in-out">
             <div class="max-w-4xl mx-auto">
                 <div class="bg-white shadow rounded-lg p-8">
                     <h2 class="text-2xl font-bold text-gray-900 mb-6">Bienvenido al Sistema</h2>
                     <p class="text-gray-600 mb-6">
                         Gestiona tus contratos de manera eficiente. Utiliza el menú lateral para acceder a todas las funcionalidades del sistema.
                     </p>
                     
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                             <h3 class="text-lg font-semibold text-blue-900 mb-2">Contratos</h3>
                             <p class="text-blue-700 text-sm">Accede a todos tus contratos, créalos, edítalos y mantén un control completo.</p>
                         </div>
                         
                         <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                             <h3 class="text-lg font-semibold text-green-900 mb-2">Reportes</h3>
                             <p class="text-green-700 text-sm">Visualiza estadísticas y análisis detallados de tu cartera de contratos.</p>
                         </div>
                     </div>
                 </div>
             </div>
         </main>
     </div>

           <!-- Footer -->
      <footer class="bg-white border-t border-gray-200 mt-auto">
         <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
             <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                 <div class="flex items-center space-x-6 text-sm text-gray-600">
                     <div class="flex items-center space-x-2">
                         <span>Versión:</span>
                         <span class="font-medium text-gray-900">1.0.0</span>
                     </div>
                     <div class="flex items-center space-x-2">
                         <span>Estado:</span>
                         <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                             <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1"></div>
                             Operativo
                         </span>
                     </div>
                     <div class="flex items-center space-x-2">
                         <span>Actualizado:</span>
                         <span class="font-medium text-gray-900">20/08/2025</span>
                     </div>
                 </div>
                 <div class="text-sm text-gray-500">
                     <i class="fas fa-copyright mr-1"></i>
                     KONTRATOS
                 </div>
             </div>
         </div>
           </footer>

             <script>
           // Función para alternar el sidebar
           function toggleSidebar() {
               const sidebar = document.getElementById('sidebar');
               const mainContent = document.getElementById('main-content');
               const toggleButton = document.getElementById('toggle-sidebar');
               const icon = toggleButton.querySelector('i');
               
               if (sidebar.classList.contains('w-64')) {
                   // Cerrar sidebar
                   sidebar.classList.remove('w-64');
                   sidebar.classList.add('w-0', 'overflow-hidden');
                   mainContent.classList.add('ml-0');
                   icon.classList.remove('fa-bars');
                   icon.classList.add('fa-chevron-right');
                   toggleButton.innerHTML = '<i class="fas fa-chevron-right mr-2"></i>Menú';
               } else {
                   // Abrir sidebar
                   sidebar.classList.remove('w-0', 'overflow-hidden');
                   sidebar.classList.add('w-64');
                   mainContent.classList.remove('ml-0');
                   icon.classList.remove('fa-chevron-right');
                   icon.classList.add('fa-bars');
                   toggleButton.innerHTML = '<i class="fas fa-bars mr-2"></i>Menú';
               }
           }
           
           // Event listener para el botón de toggle
           document.getElementById('toggle-sidebar').addEventListener('click', toggleSidebar);
           
           // Función para inicializar el sidebar cerrado
           function initializeSidebar() {
               const sidebar = document.getElementById('sidebar');
               const mainContent = document.getElementById('main-content');
               const toggleButton = document.getElementById('toggle-sidebar');
               
               // Siempre iniciar con el sidebar cerrado
               sidebar.classList.remove('w-64');
               sidebar.classList.add('w-0', 'overflow-hidden');
               mainContent.classList.add('ml-0');
               
               // Configurar el botón para mostrar que está cerrado
               toggleButton.innerHTML = '<i class="fas fa-chevron-right mr-2"></i>Menú';
           }
           
           // Ejecutar al cargar la página
           window.addEventListener('load', initializeSidebar);
       </script>
  </body>
  </html>