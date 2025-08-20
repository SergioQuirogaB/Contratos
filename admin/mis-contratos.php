<?php
require_once '../includes/functions.php';

// Verificar si está logueado
if (!isLoggedIn()) {
    redirect('../index.php');
}

$error = '';
$success = '';
$excel_data = [];

// Configuración de paginación y filtros
$registros_por_pagina = isset($_GET['registros']) ? (int)$_GET['registros'] : 25;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$filtro_cliente = isset($_GET['cliente']) ? trim($_GET['cliente']) : '';

// Validar valores de paginación
if (!in_array($registros_por_pagina, [25, 50, 100])) {
    $registros_por_pagina = 25;
}
if ($pagina_actual < 1) {
    $pagina_actual = 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_excel'])) {
        // Procesar subida de archivo
        if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['excel_file'];
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_extension, ['csv', 'txt'])) {
                try {
                    // Procesar archivo CSV
                    $handle = fopen($file['tmp_name'], 'r');
                    if ($handle !== false) {
                        $rows = [];
                        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                            $rows[] = $data;
                        }
                        fclose($handle);
                        
                        if (!empty($rows)) {
                            $_SESSION['excel_data'] = $rows;
                            $excel_data = $rows;
                            $success = 'Archivo CSV cargado exitosamente. Se encontraron ' . count($rows) . ' filas.';
                        } else {
                            $error = 'El archivo está vacío o no contiene datos válidos.';
                        }
                    } else {
                        $error = 'No se pudo abrir el archivo.';
                    }
                } catch (Exception $e) {
                    $error = 'Error al procesar el archivo: ' . $e->getMessage();
                }
            } else {
                $error = 'Tipo de archivo no válido. Solo se permiten archivos CSV (.csv) o texto (.txt).';
            }
        } else {
            $error = 'Error al subir el archivo.';
        }
    } elseif (isset($_POST['guardar_bd'])) {
        // Guardar datos en la base de datos
        if (isset($_SESSION['excel_data']) && !empty($_SESSION['excel_data'])) {
            try {
                $datos_sin_encabezados = array_slice($_SESSION['excel_data'], 1); // Excluir encabezados
                $registros_guardados = guardarContratos($datos_sin_encabezados, $_SESSION['user_id']);
                $success = "✅ Se guardaron exitosamente $registros_guardados contratos en la base de datos.";
                unset($_SESSION['excel_data']); // Limpiar datos de sesión
                $excel_data = []; // Limpiar datos de la vista
            } catch (Exception $e) {
                $error = 'Error al guardar en la base de datos: ' . $e->getMessage();
            }
        } else {
            $error = 'No hay datos para guardar. Primero sube un archivo CSV.';
        }
    }
}

// Recuperar datos de la sesión si existen
if (isset($_SESSION['excel_data'])) {
    $excel_data = $_SESSION['excel_data'];
}

// Calcular paginación para datos del CSV
$total_registros = count($excel_data) - 1; // Restamos 1 para excluir los encabezados
$total_paginas = ceil($total_registros / $registros_por_pagina);
$inicio = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener contratos guardados en la base de datos
$contratos_bd = [];
$total_contratos_bd = 0;
$clientes_unicos = [];
try {
    $total_contratos_bd = contarContratosUsuario($_SESSION['user_id'], $filtro_cliente);
    if ($total_contratos_bd > 0) {
        $contratos_bd = obtenerContratosUsuario($_SESSION['user_id'], $registros_por_pagina, $inicio, $filtro_cliente);
    }
    
    // Obtener lista de clientes únicos para el filtro
    $clientes_unicos = obtenerClientesUnicos($_SESSION['user_id']);
} catch (Exception $e) {
    $error = 'Error al obtener contratos de la base de datos: ' . $e->getMessage();
}

// Calcular paginación para contratos de la BD
$total_paginas_bd = ceil($total_contratos_bd / $registros_por_pagina);

// Obtener datos de la página actual
$datos_pagina = [];
if (!empty($excel_data)) {
    $encabezados = $excel_data[0];
    $datos = array_slice($excel_data, 1); // Excluir encabezados
    $datos_pagina = array_slice($datos, $inicio, $registros_por_pagina);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Contratos - Sistema de Gestión Contractual</title>
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
                        <h1 class="text-xl font-bold text-gray-900">Mis Contratos</h1>
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
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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
        
        <!-- Mensajes de sesión -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error_message']); ?></span>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['success_message']); ?></span>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Sección de Subida de Archivos -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                                 <div class="flex justify-between items-center mb-6">
                     <div class="flex items-center space-x-4">
                         <h3 class="text-lg leading-6 font-medium text-gray-900">
                             <i class="fas fa-upload text-blue-500 mr-2"></i>
                             Contratos
                         </h3>
                     </div>
                     
                     <div class="flex items-center space-x-4">
                         <!-- Filtro por cliente mejorado -->
                         <div class="relative">
                             <div class="flex items-center space-x-2 bg-white border border-gray-300 rounded-lg shadow-sm px-3 py-2 hover:border-blue-400 transition-colors duration-200">
                                 <i class="fas fa-filter text-gray-400 text-sm"></i>
                                 <select id="filtro_cliente_header" onchange="aplicarFiltro()" class="border-none bg-transparent text-sm text-gray-700 focus:outline-none focus:ring-0 cursor-pointer min-w-[150px]">
                                     <option value="">Todos los clientes</option>
                                     <?php foreach ($clientes_unicos as $cliente): ?>
                                         <option value="<?php echo htmlspecialchars($cliente); ?>" <?php echo $filtro_cliente === $cliente ? 'selected' : ''; ?>>
                                             <?php echo htmlspecialchars($cliente); ?>
                                         </option>
                                     <?php endforeach; ?>
                                 </select>
                                 <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                             </div>
                             
                             <?php if (!empty($filtro_cliente)): ?>
                                 <div class="absolute -top-2 -right-2">
                                     <a href="?registros=<?php echo $registros_por_pagina; ?>" class="inline-flex items-center justify-center w-5 h-5 bg-red-500 text-white text-xs rounded-full hover:bg-red-600 transition-colors duration-200" title="Limpiar filtro">
                                         <i class="fas fa-times"></i>
                                     </a>
                                 </div>
                             <?php endif; ?>
                         </div>
                         
                         <a href="nuevo-contrato.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                             <i class="fas fa-plus mr-2"></i>
                             Crear Nuevo Contrato
                         </a>
                     </div>
                 </div>
                
                <!-- Instrucciones -->
                <!-- <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-blue-800">Instrucciones:</h4>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Sube un archivo CSV con los datos de tus contratos</li>
                                    <li>La primera fila debe contener los nombres de las columnas</li>
                                    <li>Los datos deben estar separados por comas</li>
                                    <li>Puedes exportar desde Excel como CSV</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div> -->
                
                <!-- Formulario de subida -->
                <!-- <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-2">
                            Seleccionar archivo CSV
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label for="excel_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Subir archivo</span>
                                        <input id="excel_file" name="excel_file" type="file" class="sr-only" accept=".csv,.txt" required>
                                    </label>
                                    <p class="pl-1">o arrastrar y soltar</p>
                                </div>
                                <p class="text-xs text-gray-500">CSV, TXT hasta 10MB</p>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="upload_excel" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-upload mr-2"></i>
                        Subir y Procesar
                    </button>
                </form> -->
            </div>
        </div>

        <!-- Sección de Datos CSV -->
        <?php if (!empty($excel_data)): ?>
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
                        <i class="fas fa-table text-green-500 mr-2"></i>
                        Datos de Contratos
                    </h3>
                    
                    <!-- Botón de guardar -->
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-save text-green-400"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <form method="POST" class="inline">
                                    <button type="submit" name="guardar_bd" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" onclick="return confirm('¿Estás seguro de que quieres guardar estos contratos en la base de datos?')">
                                        <i class="fas fa-database mr-2"></i>
                                        Guardar en Base de Datos
                                    </button>
                                </form>
                                <p class="mt-1 text-sm text-green-700">Los datos se guardarán permanentemente en la base de datos</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resumen de datos -->
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-chart-bar text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-blue-800">Resumen de Datos</h4>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p><strong>Total de registros:</strong> <?php echo $total_registros; ?></p>
                                    <p><strong>Registros por página:</strong> <?php echo $registros_por_pagina; ?></p>
                                    <p><strong>Página actual:</strong> <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Controles de paginación -->
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 space-y-2 sm:space-y-0">
                        <div class="flex items-center space-x-2">
                            <label for="registros" class="text-sm font-medium text-gray-700">Mostrar:</label>
                            <select id="registros" onchange="cambiarRegistros(this.value)" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="25" <?php echo $registros_por_pagina == 25 ? 'selected' : ''; ?>>25 registros</option>
                                <option value="50" <?php echo $registros_por_pagina == 50 ? 'selected' : ''; ?>>50 registros</option>
                                <option value="100" <?php echo $registros_por_pagina == 100 ? 'selected' : ''; ?>>100 registros</option>
                            </select>
                        </div>
                        
                        <div class="text-sm text-gray-700">
                            Mostrando <?php echo $inicio + 1; ?> - <?php echo min($inicio + $registros_por_pagina, $total_registros); ?> de <?php echo $total_registros; ?> registros
                        </div>
                        
                        <!-- Paginación -->
                        <div class="flex space-x-1">
                            <?php if ($pagina_actual > 1): ?>
                                <a href="?pagina=1&registros=<?php echo $registros_por_pagina; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                                <a href="?pagina=<?php echo $pagina_actual - 1; ?>&registros=<?php echo $registros_por_pagina; ?>" class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed">
                                    <i class="fas fa-angle-double-left"></i>
                                </span>
                                <span class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed">
                                    <i class="fas fa-angle-left"></i>
                                </span>
                            <?php endif; ?>
                            
                            <?php
                            $inicio_paginas = max(1, $pagina_actual - 2);
                            $fin_paginas = min($total_paginas, $pagina_actual + 2);
                            
                            for ($i = $inicio_paginas; $i <= $fin_paginas; $i++):
                            ?>
                                <?php if ($i == $pagina_actual): ?>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 text-sm font-medium text-blue-600">
                                        <?php echo $i; ?>
                                    </span>
                                <?php else: ?>
                                    <a href="?pagina=<?php echo $i; ?>&registros=<?php echo $registros_por_pagina; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($pagina_actual < $total_paginas): ?>
                                <a href="?pagina=<?php echo $pagina_actual + 1; ?>&registros=<?php echo $registros_por_pagina; ?>" class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                                <a href="?pagina=<?php echo $total_paginas; ?>&registros=<?php echo $registros_por_pagina; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed">
                                    <i class="fas fa-angle-right"></i>
                                </span>
                                <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed">
                                    <i class="fas fa-angle-double-right"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Tabla de datos -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <?php if (!empty($encabezados)): ?>
                                        <?php foreach ($encabezados as $header): ?>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <?php echo htmlspecialchars($header); ?>
                                            </th>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($datos_pagina as $fila): ?>
                                    <tr class="hover:bg-gray-50">
                                        <?php foreach ($fila as $cell): ?>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($cell); ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Mensaje cuando no hay datos -->
            <!-- <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6 text-center">
                    <i class="fas fa-file-alt text-gray-400 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay datos para mostrar</h3>
                    <p class="text-gray-500">Sube un archivo CSV para ver los datos de contratos aquí.</p>
                </div>
            </div>
        <?php endif; ?> -->
        
        <!-- Sección de Contratos Guardados en BD -->
        <?php if ($total_contratos_bd > 0): ?>
            <div class="bg-white shadow rounded-lg mt-6">
                <div class="px-4 py-5 sm:p-6">
                    <!-- <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
                        <i class="fas fa-database text-purple-500 mr-2"></i>
                        Contratos Guardados en Base de Datos
                    </h3> -->
                    
                    <!-- Resumen de contratos guardados -->
                    <!-- <div class="bg-purple-50 border-l-4 border-purple-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-chart-bar text-purple-400"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-purple-800">Resumen de Contratos Guardados</h4>
                                <div class="mt-2 text-sm text-purple-700">
                                    <p><strong>Total de contratos guardados:</strong> <?php echo $total_contratos_bd; ?></p>
                                    <p><strong>Registros por página:</strong> <?php echo $registros_por_pagina; ?></p>
                                    <p><strong>Página actual:</strong> <?php echo $pagina_actual; ?> de <?php echo $total_paginas_bd; ?></p>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    
                                         <!-- Información de ordenamiento -->
                     <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                         <div class="flex items-center text-sm text-blue-700">
                             <i class="fas fa-info-circle mr-2"></i>
                             <span>Los contratos están ordenados por <strong>año descendente</strong> (más actual al más antiguo)</span>
                         </div>
                     </div>
                    
                    <!-- Controles de paginación para contratos BD -->
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 space-y-2 sm:space-y-0">
                        <div class="flex items-center space-x-2">
                            <label for="registros_bd" class="text-sm font-medium text-gray-700">Mostrar:</label>
                            <select id="registros_bd" onchange="cambiarRegistrosBD(this.value)" class="border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                <option value="25" <?php echo $registros_por_pagina == 25 ? 'selected' : ''; ?>>25 registros</option>
                                <option value="50" <?php echo $registros_por_pagina == 50 ? 'selected' : ''; ?>>50 registros</option>
                                <option value="100" <?php echo $registros_por_pagina == 100 ? 'selected' : ''; ?>>100 registros</option>
                            </select>
                        </div>
                        
                        <div class="text-sm text-gray-700">
                            Mostrando <?php echo $inicio + 1; ?> - <?php echo min($inicio + $registros_por_pagina, $total_contratos_bd); ?> de <?php echo $total_contratos_bd; ?> contratos
                        </div>
                        
                        <!-- Paginación para contratos BD -->
                        <div class="flex space-x-1">
                            <?php 
                            // Construir parámetros de URL para mantener el filtro
                            $parametros_url = "registros=" . $registros_por_pagina;
                            if (!empty($filtro_cliente)) {
                                $parametros_url .= "&cliente=" . urlencode($filtro_cliente);
                            }
                            ?>
                            
                            <?php if ($pagina_actual > 1): ?>
                                <a href="?pagina=1&<?php echo $parametros_url; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                                <a href="?pagina=<?php echo $pagina_actual - 1; ?>&<?php echo $parametros_url; ?>" class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed">
                                    <i class="fas fa-angle-double-left"></i>
                                </span>
                                <span class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed">
                                    <i class="fas fa-angle-left"></i>
                                </span>
                            <?php endif; ?>
                            
                            <?php
                            $inicio_paginas = max(1, $pagina_actual - 2);
                            $fin_paginas = min($total_paginas_bd, $pagina_actual + 2);
                            
                            for ($i = $inicio_paginas; $i <= $fin_paginas; $i++):
                            ?>
                                <?php if ($i == $pagina_actual): ?>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-purple-500 bg-purple-50 text-sm font-medium text-purple-600">
                                        <?php echo $i; ?>
                                    </span>
                                <?php else: ?>
                                    <a href="?pagina=<?php echo $i; ?>&<?php echo $parametros_url; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($pagina_actual < $total_paginas_bd): ?>
                                <a href="?pagina=<?php echo $pagina_actual + 1; ?>&<?php echo $parametros_url; ?>" class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                                <a href="?pagina=<?php echo $total_paginas_bd; ?>&<?php echo $parametros_url; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed">
                                    <i class="fas fa-angle-right"></i>
                                </span>
                                <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed">
                                    <i class="fas fa-angle-double-right"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Tabla de contratos guardados -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-sort-numeric-down mr-1"></i>AÑO
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CLIENTE</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No contrato</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor en pesos sin IVA</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor en dólares</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Mensual</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observaciones</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Inicio</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Vencimiento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Facturado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% Ejecución</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Pendiente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No de horas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No de Póliza</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Vencimiento Póliza</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($contratos_bd as $contrato): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($contrato['ano'] ?? ''); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($contrato['empresa'] ?? ''); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($contrato['cliente'] ?? ''); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($contrato['no_contrato'] ?? ''); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $contrato['valor_pesos_sin_iva'] ? '$' . number_format($contrato['valor_pesos_sin_iva'], 2) : ''; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $contrato['valor_dolares'] ? '$' . number_format($contrato['valor_dolares'], 2) : ''; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($contrato['descripcion'] ?? ''); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($contrato['categoria'] ?? ''); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $contrato['valor_mensual'] ? '$' . number_format($contrato['valor_mensual'], 2) : ''; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($contrato['observaciones'] ?? ''); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $contrato['fecha_inicio'] ? date('d/m/Y', strtotime($contrato['fecha_inicio'])) : ''; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $contrato['fecha_vencimiento'] ? date('d/m/Y', strtotime($contrato['fecha_vencimiento'])) : ''; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $contrato['valor_facturado'] ? '$' . number_format($contrato['valor_facturado'], 2) : ''; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $contrato['porcentaje_ejecucion'] ? $contrato['porcentaje_ejecucion'] . '%' : ''; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $contrato['valor_pendiente_ejecutar'] ? '$' . number_format($contrato['valor_pendiente_ejecutar'], 2) : ''; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($contrato['estado'] ?? ''); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($contrato['no_horas'] ?? ''); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($contrato['factura_no'] ?? ''); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($contrato['no_poliza'] ?? ''); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $contrato['fecha_vencimiento_poliza'] ? date('d/m/Y', strtotime($contrato['fecha_vencimiento_poliza'])) : ''; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="flex space-x-2">
                                                <a href="editar-contrato.php?id=<?php echo $contrato['id']; ?>" class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Editar
                                                </a>
                                                <button onclick="eliminarContrato(<?php echo $contrato['id']; ?>, '<?php echo htmlspecialchars($contrato['no_contrato'] ?? 'este contrato'); ?>')" class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    <i class="fas fa-trash mr-1"></i>
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function cambiarRegistros(valor) {
            // Obtener parámetros actuales de la URL
            const urlParams = new URLSearchParams(window.location.search);
            const paginaActual = urlParams.get('pagina') || 1;
            const clienteFiltro = urlParams.get('cliente') || '';
            
            // Construir nueva URL
            let nuevaUrl = `?pagina=${paginaActual}&registros=${valor}`;
            if (clienteFiltro) {
                nuevaUrl += `&cliente=${encodeURIComponent(clienteFiltro)}`;
            }
            
            // Redirigir con los nuevos parámetros
            window.location.href = nuevaUrl;
        }
        
        function cambiarRegistrosBD(valor) {
            // Obtener parámetros actuales de la URL
            const urlParams = new URLSearchParams(window.location.search);
            const paginaActual = urlParams.get('pagina') || 1;
            const clienteFiltro = urlParams.get('cliente') || '';
            
            // Construir nueva URL
            let nuevaUrl = `?pagina=${paginaActual}&registros=${valor}`;
            if (clienteFiltro) {
                nuevaUrl += `&cliente=${encodeURIComponent(clienteFiltro)}`;
            }
            
            // Redirigir con los nuevos parámetros
            window.location.href = nuevaUrl;
        }
        
                 function aplicarFiltro() {
             const filtroCliente = document.getElementById('filtro_cliente_header').value;
             const urlParams = new URLSearchParams(window.location.search);
             const registrosActual = urlParams.get('registros') || 25;
             
             // Construir nueva URL
             let nuevaUrl = `?pagina=1&registros=${registrosActual}`;
             if (filtroCliente) {
                 nuevaUrl += `&cliente=${encodeURIComponent(filtroCliente)}`;
             }
             
             // Redirigir con el filtro aplicado
             window.location.href = nuevaUrl;
         }
        
        // Sincronizar selectores de datos CSV
        document.addEventListener('DOMContentLoaded', function() {
            const selector1 = document.getElementById('registros');
            const selector2 = document.getElementById('registros2');
            
            if (selector1 && selector2) {
                selector1.addEventListener('change', function() {
                    selector2.value = this.value;
                });
                
                selector2.addEventListener('change', function() {
                    selector1.value = this.value;
                });
            }
            
            // Sincronizar selectores de contratos BD
            const selectorBD1 = document.getElementById('registros_bd');
            const selectorBD2 = document.getElementById('registros_bd2');
            
            if (selectorBD1 && selectorBD2) {
                selectorBD1.addEventListener('change', function() {
                    selectorBD2.value = this.value;
                });
                
                selectorBD2.addEventListener('change', function() {
                    selectorBD1.value = this.value;
                });
            }
        });
        
        // Función para eliminar contrato
        function eliminarContrato(contratoId, nombreContrato) {
            if (confirm('¿Estás seguro de que quieres eliminar el contrato "' + nombreContrato + '"? Esta acción no se puede deshacer.')) {
                // Crear formulario temporal para enviar la solicitud de eliminación
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'eliminar-contrato.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'contrato_id';
                input.value = contratoId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
