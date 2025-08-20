<?php
require_once '../includes/functions.php';

// Verificar si está logueado
if (!isLoggedIn()) {
    redirect('../index.php');
}

$error = '';
$success = '';
$contrato = null;

// Obtener ID del contrato
$contrato_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($contrato_id <= 0) {
    redirect('mis-contratos.php');
}

// Obtener datos del contrato
try {
    $contrato = obtenerContratoPorId($contrato_id, $_SESSION['user_id']);
    if (!$contrato) {
        redirect('mis-contratos.php');
    }
} catch (Exception $e) {
    $error = 'Error al obtener el contrato: ' . $e->getMessage();
}

// Procesar formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_contrato'])) {
    try {
        $datos_actualizados = [
            'ano' => $_POST['ano'] ?? '',
            'empresa' => $_POST['empresa'] ?? '',
            'cliente' => $_POST['cliente'] ?? '',
            'no_contrato' => $_POST['no_contrato'] ?? '',
            'valor_pesos_sin_iva' => $_POST['valor_pesos_sin_iva'] ?? '',
            'valor_dolares' => $_POST['valor_dolares'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'categoria' => $_POST['categoria'] ?? '',
            'valor_mensual' => $_POST['valor_mensual'] ?? '',
            'observaciones' => $_POST['observaciones'] ?? '',
            'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
            'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? '',
            'valor_facturado' => $_POST['valor_facturado'] ?? '',
            'porcentaje_ejecucion' => $_POST['porcentaje_ejecucion'] ?? '',
            'valor_pendiente_ejecutar' => $_POST['valor_pendiente_ejecutar'] ?? '',
            'estado' => $_POST['estado'] ?? '',
            'no_horas' => $_POST['no_horas'] ?? '',
            'factura_no' => $_POST['factura_no'] ?? '',
            'no_poliza' => $_POST['no_poliza'] ?? '',
            'fecha_vencimiento_poliza' => $_POST['fecha_vencimiento_poliza'] ?? ''
        ];
        
        if (actualizarContrato($contrato_id, $_SESSION['user_id'], $datos_actualizados)) {
            $success = '✅ Contrato actualizado exitosamente.';
            // Recargar datos del contrato
            $contrato = obtenerContratoPorId($contrato_id, $_SESSION['user_id']);
        } else {
            $error = 'No se pudo actualizar el contrato.';
        }
    } catch (Exception $e) {
        $error = 'Error al actualizar el contrato: ' . $e->getMessage();
    }
}

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_contrato'])) {
    try {
        if (eliminarContrato($contrato_id, $_SESSION['user_id'])) {
            $success = '✅ Contrato eliminado exitosamente.';
            redirect('mis-contratos.php');
        } else {
            $error = 'No se pudo eliminar el contrato.';
        }
    } catch (Exception $e) {
        $error = 'Error al eliminar el contrato: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Contrato - Sistema de Gestión Contractual</title>
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
                        <h1 class="text-xl font-bold text-gray-900">Editar Contrato</h1>
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
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver a Mis Contratos
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

        <?php if ($contrato): ?>
            <!-- Formulario de Edición -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
                        <i class="fas fa-edit text-blue-500 mr-2"></i>
                        Editar Contrato #<?php echo htmlspecialchars($contrato['no_contrato']); ?>
                    </h3>
                    
                    <form method="POST" class="space-y-6">
                        <!-- Información Básica -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label for="ano" class="block text-sm font-medium text-gray-700">Año</label>
                                <input type="number" name="ano" id="ano" value="<?php echo htmlspecialchars($contrato['ano'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="empresa" class="block text-sm font-medium text-gray-700">Empresa</label>
                                <input type="text" name="empresa" id="empresa" value="<?php echo htmlspecialchars($contrato['empresa'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="cliente" class="block text-sm font-medium text-gray-700">Cliente</label>
                                <input type="text" name="cliente" id="cliente" value="<?php echo htmlspecialchars($contrato['cliente'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="no_contrato" class="block text-sm font-medium text-gray-700">Número de Contrato</label>
                                <input type="text" name="no_contrato" id="no_contrato" value="<?php echo htmlspecialchars($contrato['no_contrato'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="valor_pesos_sin_iva" class="block text-sm font-medium text-gray-700">Valor en Pesos (sin IVA)</label>
                                <input type="text" name="valor_pesos_sin_iva" id="valor_pesos_sin_iva" value="<?php echo $contrato['valor_pesos_sin_iva'] ? number_format($contrato['valor_pesos_sin_iva'], 2) : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="0.00">
                            </div>
                            
                            <div>
                                <label for="valor_dolares" class="block text-sm font-medium text-gray-700">Valor en Dólares</label>
                                <input type="text" name="valor_dolares" id="valor_dolares" value="<?php echo $contrato['valor_dolares'] ? number_format($contrato['valor_dolares'], 2) : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="0.00">
                            </div>
                        </div>
                        
                        <!-- Descripción y Categoría -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                                <textarea name="descripcion" id="descripcion" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"><?php echo htmlspecialchars($contrato['descripcion'] ?? ''); ?></textarea>
                            </div>
                            
                            <div>
                                <label for="categoria" class="block text-sm font-medium text-gray-700">Categoría</label>
                                <input type="text" name="categoria" id="categoria" value="<?php echo htmlspecialchars($contrato['categoria'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>
                        
                        <!-- Valores y Observaciones -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label for="valor_mensual" class="block text-sm font-medium text-gray-700">Valor Mensual</label>
                                <input type="text" name="valor_mensual" id="valor_mensual" value="<?php echo $contrato['valor_mensual'] ? number_format($contrato['valor_mensual'], 2) : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="0.00">
                            </div>
                            
                            <div>
                                <label for="valor_facturado" class="block text-sm font-medium text-gray-700">Valor Facturado</label>
                                <input type="text" name="valor_facturado" id="valor_facturado" value="<?php echo $contrato['valor_facturado'] ? number_format($contrato['valor_facturado'], 2) : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="0.00">
                            </div>
                            
                            <div>
                                <label for="porcentaje_ejecucion" class="block text-sm font-medium text-gray-700">% Ejecución</label>
                                <input type="text" name="porcentaje_ejecucion" id="porcentaje_ejecucion" value="<?php echo $contrato['porcentaje_ejecucion'] ? $contrato['porcentaje_ejecucion'] : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="0">
                            </div>
                        </div>
                        
                        <div>
                            <label for="observaciones" class="block text-sm font-medium text-gray-700">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"><?php echo htmlspecialchars($contrato['observaciones'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Fechas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo $contrato['fecha_inicio'] ? date('Y-m-d', strtotime($contrato['fecha_inicio'])) : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="fecha_vencimiento" class="block text-sm font-medium text-gray-700">Fecha de Vencimiento</label>
                                <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" value="<?php echo $contrato['fecha_vencimiento'] ? date('Y-m-d', strtotime($contrato['fecha_vencimiento'])) : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="fecha_vencimiento_poliza" class="block text-sm font-medium text-gray-700">Fecha Vencimiento Póliza</label>
                                <input type="date" name="fecha_vencimiento_poliza" id="fecha_vencimiento_poliza" value="<?php echo $contrato['fecha_vencimiento_poliza'] ? date('Y-m-d', strtotime($contrato['fecha_vencimiento_poliza'])) : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>
                        
                        <!-- Información Adicional -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
                                <select name="estado" id="estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Seleccionar estado</option>
                                    <option value="Activo" <?php echo ($contrato['estado'] ?? '') === 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                    <option value="Inactivo" <?php echo ($contrato['estado'] ?? '') === 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                    <option value="Pendiente" <?php echo ($contrato['estado'] ?? '') === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="Vencido" <?php echo ($contrato['estado'] ?? '') === 'Vencido' ? 'selected' : ''; ?>>Vencido</option>
                                    <option value="Terminado" <?php echo ($contrato['estado'] ?? '') === 'Terminado' ? 'selected' : ''; ?>>Terminado</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="no_horas" class="block text-sm font-medium text-gray-700">Número de Horas</label>
                                <input type="number" name="no_horas" id="no_horas" value="<?php echo htmlspecialchars($contrato['no_horas'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="factura_no" class="block text-sm font-medium text-gray-700">Número de Factura</label>
                                <input type="text" name="factura_no" id="factura_no" value="<?php echo htmlspecialchars($contrato['factura_no'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="no_poliza" class="block text-sm font-medium text-gray-700">Número de Póliza</label>
                                <input type="text" name="no_poliza" id="no_poliza" value="<?php echo htmlspecialchars($contrato['no_poliza'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label for="valor_pendiente_ejecutar" class="block text-sm font-medium text-gray-700">Valor Pendiente por Ejecutar</label>
                            <input type="text" name="valor_pendiente_ejecutar" id="valor_pendiente_ejecutar" value="<?php echo $contrato['valor_pendiente_ejecutar'] ? number_format($contrato['valor_pendiente_ejecutar'], 2) : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="0.00">
                        </div>
                        
                        <!-- Botones de Acción -->
                        <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                            <div class="flex space-x-3">
                                <button type="submit" name="actualizar_contrato" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-save mr-2"></i>
                                    Actualizar Contrato
                                </button>
                                
                                <a href="mis-contratos.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancelar
                                </a>
                            </div>
                            
                            <button type="submit" name="eliminar_contrato" onclick="return confirm('¿Estás seguro de que quieres eliminar este contrato? Esta acción no se puede deshacer.')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <i class="fas fa-trash mr-2"></i>
                                Eliminar Contrato
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6 text-center">
                    <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Contrato no encontrado</h3>
                    <p class="text-gray-500">El contrato que buscas no existe o no tienes permisos para editarlo.</p>
                    <a href="mis-contratos.php" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver a Mis Contratos
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Formatear campos de moneda automáticamente
        document.addEventListener('DOMContentLoaded', function() {
            const camposMoneda = ['valor_pesos_sin_iva', 'valor_dolares', 'valor_mensual', 'valor_facturado', 'valor_pendiente_ejecutar'];
            
            camposMoneda.forEach(function(campo) {
                const input = document.getElementById(campo);
                if (input) {
                    input.addEventListener('blur', function() {
                        let valor = this.value.replace(/[^\d.-]/g, '');
                        if (valor && !isNaN(valor)) {
                            this.value = parseFloat(valor).toFixed(2);
                        }
                    });
                }
            });
            
            // Formatear campo de porcentaje
            const campoPorcentaje = document.getElementById('porcentaje_ejecucion');
            if (campoPorcentaje) {
                campoPorcentaje.addEventListener('blur', function() {
                    let valor = this.value.replace(/[^\d.-]/g, '');
                    if (valor && !isNaN(valor)) {
                        this.value = parseFloat(valor).toFixed(2);
                    }
                });
            }
        });
    </script>
</body>
</html>
