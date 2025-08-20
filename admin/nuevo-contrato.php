<?php
require_once '../includes/functions.php';

// Verificar si está logueado
if (!isLoggedIn()) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y limpiar todos los campos del formulario
    $ano = (int)cleanInput($_POST['ano']);
    $empresa = cleanInput($_POST['empresa']);
    $cliente = cleanInput($_POST['cliente']);
    $no_contrato = cleanInput($_POST['no_contrato']);
    $valor_pesos_sin_iva = (float)str_replace(['$', ',', ' '], '', $_POST['valor_pesos_sin_iva']);
    $valor_dolares = (float)str_replace(['$', ',', ' '], '', $_POST['valor_dolares']);
    $descripcion = cleanInput($_POST['descripcion']);
    $categoria = cleanInput($_POST['categoria']);
    $valor_mensual = (float)str_replace(['$', ',', ' '], '', $_POST['valor_mensual']);
    $observaciones = cleanInput($_POST['observaciones']);
    $fecha_inicio = cleanInput($_POST['fecha_inicio']);
    $fecha_vencimiento = cleanInput($_POST['fecha_vencimiento']);
    $valor_facturado = (float)str_replace(['$', ',', ' '], '', $_POST['valor_facturado']);
    $porcentaje_ejecucion = (float)str_replace(['%', ' '], '', $_POST['porcentaje_ejecucion']);
    $valor_pendiente_ejecutar = (float)str_replace(['$', ',', ' '], '', $_POST['valor_pendiente_ejecutar']);
    $estado = cleanInput($_POST['estado']);
    $no_horas = (int)cleanInput($_POST['no_horas']);
    $factura_no = cleanInput($_POST['factura_no']);
    $no_poliza = cleanInput($_POST['no_poliza']);
    $fecha_vencimiento_poliza = cleanInput($_POST['fecha_vencimiento_poliza']);
    
    // Validar campos obligatorios
    if (empty($cliente) || empty($descripcion) || empty($fecha_inicio) || empty($fecha_vencimiento)) {
        $error = 'Por favor, completa todos los campos obligatorios (Cliente, Descripción, Fecha de Inicio, Fecha de Vencimiento).';
    } else {
        try {
            // Guardar en la base de datos
            $datos_contrato = [
                [
                    $ano, $empresa, $cliente, $no_contrato, $valor_pesos_sin_iva, $valor_dolares,
                    $descripcion, $categoria, $valor_mensual, $observaciones, $fecha_inicio,
                    $fecha_vencimiento, $valor_facturado, $porcentaje_ejecucion, $valor_pendiente_ejecutar,
                    $estado, $no_horas, $factura_no, $no_poliza, $fecha_vencimiento_poliza
                ]
            ];
            
            $registros_guardados = guardarContratos($datos_contrato, $_SESSION['user_id']);
            $success = "✅ Contrato creado exitosamente. Se guardó en la base de datos.";
            
            // Limpiar el formulario después de guardar
            $_POST = array();
            
        } catch (Exception $e) {
            $error = 'Error al guardar el contrato: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Contrato - Sistema de Gestión Contractual</title>
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
                        <h1 class="text-xl font-bold text-gray-900">Nuevo Contrato</h1>
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
                    <a href="mis-contratos.php" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-file-contract mr-2"></i>
                        Ver Mis Contratos
                    </a>
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

        <!-- Formulario -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center mb-6">
                    <i class="fas fa-file-contract text-blue-500 text-2xl mr-3"></i>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Crear Nuevo Contrato</h3>
                </div>
                
                <form method="POST" action="" class="space-y-8">
                    <!-- Información Básica -->
                    <div>
                        <div class="flex items-center mb-4">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            <h4 class="text-md font-medium text-gray-900">Información Básica</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="ano" class="block text-sm font-medium text-gray-700">
                                    Año <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="ano" name="ano" value="<?php echo date('Y'); ?>" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="empresa" class="block text-sm font-medium text-gray-700">
                                    Empresa
                                </label>
                                <input type="text" id="empresa" name="empresa" value="<?php echo htmlspecialchars($_POST['empresa'] ?? ''); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="cliente" class="block text-sm font-medium text-gray-700">
                                    Cliente <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="cliente" name="cliente" value="<?php echo htmlspecialchars($_POST['cliente'] ?? ''); ?>" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="no_contrato" class="block text-sm font-medium text-gray-700">
                                    Número de Contrato
                                </label>
                                <input type="text" id="no_contrato" name="no_contrato" value="<?php echo htmlspecialchars($_POST['no_contrato'] ?? ''); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <label for="descripcion" class="block text-sm font-medium text-gray-700">
                                Descripción del Contrato <span class="text-red-500">*</span>
                            </label>
                            <textarea id="descripcion" name="descripcion" rows="3" required
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 mt-6">
                            <div>
                                <label for="categoria" class="block text-sm font-medium text-gray-700">
                                    Categoría
                                </label>
                                <input type="text" id="categoria" name="categoria" value="<?php echo htmlspecialchars($_POST['categoria'] ?? ''); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700">
                                    Estado
                                </label>
                                <select id="estado" name="estado"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="Pendiente" <?php echo ($_POST['estado'] ?? '') === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="En Proceso" <?php echo ($_POST['estado'] ?? '') === 'En Proceso' ? 'selected' : ''; ?>>En Proceso</option>
                                    <option value="Activo" <?php echo ($_POST['estado'] ?? '') === 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                    <option value="Completado" <?php echo ($_POST['estado'] ?? '') === 'Completado' ? 'selected' : ''; ?>>Completado</option>
                                    <option value="Cancelado" <?php echo ($_POST['estado'] ?? '') === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Valores Monetarios -->
                    <div class="border-t border-gray-200 pt-8">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-dollar-sign text-green-500 mr-2"></i>
                            <h4 class="text-md font-medium text-gray-900">Valores Monetarios</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="valor_pesos_sin_iva" class="block text-sm font-medium text-gray-700">
                                    Valor en Pesos (sin IVA)
                                </label>
                                <input type="text" id="valor_pesos_sin_iva" name="valor_pesos_sin_iva" placeholder="$0.00" value="<?php echo htmlspecialchars($_POST['valor_pesos_sin_iva'] ?? ''); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="valor_dolares" class="block text-sm font-medium text-gray-700">
                                    Valor en Dólares
                                </label>
                                <input type="text" id="valor_dolares" name="valor_dolares" placeholder="$0.00" value="<?php echo htmlspecialchars($_POST['valor_dolares'] ?? ''); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="valor_mensual" class="block text-sm font-medium text-gray-700">
                                    Valor Mensual
                                </label>
                                <input type="text" id="valor_mensual" name="valor_mensual" placeholder="$0.00" value="<?php echo htmlspecialchars($_POST['valor_mensual'] ?? ''); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="valor_facturado" class="block text-sm font-medium text-gray-700">
                                    Valor Facturado
                                </label>
                                <input type="text" id="valor_facturado" name="valor_facturado" placeholder="$0.00" value="<?php echo htmlspecialchars($_POST['valor_facturado'] ?? ''); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="porcentaje_ejecucion" class="block text-sm font-medium text-gray-700">
                                    % Ejecución
                                </label>
                                <input type="text" id="porcentaje_ejecucion" name="porcentaje_ejecucion" placeholder="0%" value="<?php echo htmlspecialchars($_POST['porcentaje_ejecucion'] ?? ''); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="valor_pendiente_ejecutar" class="block text-sm font-medium text-gray-700">
                                    Valor Pendiente por Ejecutar
                                </label>
                                <input type="text" id="valor_pendiente_ejecutar" name="valor_pendiente_ejecutar" placeholder="$0.00" value="<?php echo htmlspecialchars($_POST['valor_pendiente_ejecutar'] ?? ''); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fechas -->
                    <div class="border-t border-gray-200 pt-8">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-calendar-alt text-purple-500 mr-2"></i>
                            <h4 class="text-md font-medium text-gray-900">Fechas</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">
                                    Fecha de Inicio <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($_POST['fecha_inicio'] ?? ''); ?>" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="fecha_vencimiento" class="block text-sm font-medium text-gray-700">
                                    Fecha de Vencimiento <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" value="<?php echo htmlspecialchars($_POST['fecha_vencimiento'] ?? ''); ?>" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <label for="fecha_vencimiento_poliza" class="block text-sm font-medium text-gray-700">
                                Fecha de Vencimiento de Póliza
                            </label>
                            <input type="date" id="fecha_vencimiento_poliza" name="fecha_vencimiento_poliza" value="<?php echo htmlspecialchars($_POST['fecha_vencimiento_poliza'] ?? ''); ?>"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                        </div>
                    </div>
                    
                    <!-- Información Adicional -->
                    <div class="border-t border-gray-200 pt-8">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-clipboard-list text-indigo-500 mr-2"></i>
                            <h4 class="text-md font-medium text-gray-900">Información Adicional</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="no_horas" class="block text-sm font-medium text-gray-700">
                                    Número de Horas
                                </label>
                                <input type="number" id="no_horas" name="no_horas" value="<?php echo htmlspecialchars($_POST['no_horas'] ?? ''); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="factura_no" class="block text-sm font-medium text-gray-700">
                                    Número de Factura
                                </label>
                                <input type="text" id="factura_no" name="factura_no" value="<?php echo htmlspecialchars($_POST['factura_no'] ?? ''); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <label for="no_poliza" class="block text-sm font-medium text-gray-700">
                                Número de Póliza
                            </label>
                            <input type="text" id="no_poliza" name="no_poliza" value="<?php echo htmlspecialchars($_POST['no_poliza'] ?? ''); ?>"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        
                        <div class="mt-6">
                            <label for="observaciones" class="block text-sm font-medium text-gray-700">
                                Observaciones
                            </label>
                            <textarea id="observaciones" name="observaciones" rows="3"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($_POST['observaciones'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Botón de envío -->
                    <div class="border-t border-gray-200 pt-8">
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-save mr-2"></i>
                            Crear Contrato
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <script>
        // Establecer fecha actual como valor por defecto para fecha de inicio
        document.addEventListener('DOMContentLoaded', function() {
            const fechaInicio = document.getElementById('fecha_inicio');
            if (!fechaInicio.value) {
                fechaInicio.value = new Date().toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>
