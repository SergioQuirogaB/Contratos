<?php
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../index.php');
}

$usuario_id = $_SESSION['user_id'];

// KPIs Principales Avanzados
$sql_kpis = "SELECT 
    COUNT(*) as total_contratos,
    SUM(valor_pesos_sin_iva) as valor_total,
    AVG(valor_pesos_sin_iva) as valor_promedio,
    SUM(valor_facturado) as valor_facturado_total,
    AVG(porcentaje_ejecucion) as promedio_ejecucion,
    COUNT(CASE WHEN fecha_vencimiento < CURDATE() THEN 1 END) as contratos_vencidos,
    COUNT(CASE WHEN fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as contratos_por_vencer,
    COUNT(CASE WHEN porcentaje_ejecucion >= 100 THEN 1 END) as contratos_completados,
    COUNT(CASE WHEN porcentaje_ejecucion > 0 AND porcentaje_ejecucion < 100 THEN 1 END) as contratos_en_proceso,
    COUNT(CASE WHEN porcentaje_ejecucion = 0 THEN 1 END) as contratos_sin_iniciar
FROM contratos WHERE usuario_id = :usuario_id";
$stmt = $pdo->prepare($sql_kpis);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$kpis = $stmt->fetch();

// Análisis de Rentabilidad por Categoría
$sql_rentabilidad = "SELECT 
    categoria,
    COUNT(*) as cantidad,
    SUM(valor_pesos_sin_iva) as valor_total,
    SUM(valor_facturado) as valor_facturado,
    AVG(porcentaje_ejecucion) as promedio_ejecucion,
    (SUM(valor_facturado) / SUM(valor_pesos_sin_iva)) * 100 as rentabilidad
FROM contratos 
WHERE usuario_id = :usuario_id AND categoria IS NOT NULL
GROUP BY categoria
ORDER BY rentabilidad DESC";
$stmt = $pdo->prepare($sql_rentabilidad);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$rentabilidad_categorias = $stmt->fetchAll();

// Análisis de Riesgo por Vencimiento
$sql_riesgo = "SELECT 
    CASE 
        WHEN fecha_vencimiento < CURDATE() THEN 'Vencido'
        WHEN fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'Alto Riesgo'
        WHEN fecha_vencimiento BETWEEN DATE_ADD(CURDATE(), INTERVAL 8 DAY) AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Riesgo Medio'
        ELSE 'Bajo Riesgo'
    END as nivel_riesgo,
    COUNT(*) as cantidad,
    SUM(valor_pesos_sin_iva) as valor_total,
    AVG(porcentaje_ejecucion) as promedio_ejecucion
FROM contratos 
WHERE usuario_id = :usuario_id AND fecha_vencimiento IS NOT NULL
GROUP BY 
    CASE 
        WHEN fecha_vencimiento < CURDATE() THEN 'Vencido'
        WHEN fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'Alto Riesgo'
        WHEN fecha_vencimiento BETWEEN DATE_ADD(CURDATE(), INTERVAL 8 DAY) AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Riesgo Medio'
        ELSE 'Bajo Riesgo'
    END";
$stmt = $pdo->prepare($sql_riesgo);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$analisis_riesgo = $stmt->fetchAll();

// Top 5 Clientes por Valor
$sql_top_clientes = "SELECT 
    cliente,
    COUNT(*) as cantidad_contratos,
    SUM(valor_pesos_sin_iva) as valor_total,
    AVG(porcentaje_ejecucion) as promedio_ejecucion
FROM contratos 
WHERE usuario_id = :usuario_id AND cliente IS NOT NULL
GROUP BY cliente
ORDER BY valor_total DESC
LIMIT 5";
$stmt = $pdo->prepare($sql_top_clientes);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$top_clientes = $stmt->fetchAll();

// Análisis Trimestral
$sql_trimestral = "SELECT 
    CONCAT(YEAR(fecha_inicio), ' Q', QUARTER(fecha_inicio)) as trimestre,
    COUNT(*) as cantidad,
    SUM(valor_pesos_sin_iva) as valor_total,
    AVG(porcentaje_ejecucion) as promedio_ejecucion
FROM contratos 
WHERE usuario_id = :usuario_id AND fecha_inicio IS NOT NULL
GROUP BY YEAR(fecha_inicio), QUARTER(fecha_inicio)
ORDER BY trimestre DESC
LIMIT 8";
$stmt = $pdo->prepare($sql_trimestral);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$datos_trimestrales = $stmt->fetchAll();

// Tendencias Mensuales (Últimos 24 meses)
$sql_tendencias = "SELECT 
    DATE_FORMAT(fecha_inicio, '%Y-%m') as mes,
    COUNT(*) as nuevos_contratos,
    SUM(valor_pesos_sin_iva) as valor_nuevo,
    COUNT(CASE WHEN porcentaje_ejecucion >= 100 THEN 1 END) as completados,
    AVG(porcentaje_ejecucion) as promedio_ejecucion
FROM contratos 
WHERE usuario_id = :usuario_id 
    AND fecha_inicio >= DATE_SUB(NOW(), INTERVAL 24 MONTH)
GROUP BY DATE_FORMAT(fecha_inicio, '%Y-%m')
ORDER BY mes ASC";
$stmt = $pdo->prepare($sql_tendencias);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$tendencias_mensuales = $stmt->fetchAll();

// Contratos Recientes con Análisis Avanzado
$sql_recientes = "SELECT 
    cliente,
    descripcion,
    valor_pesos_sin_iva,
    porcentaje_ejecucion,
    fecha_inicio,
    fecha_vencimiento,
    DATEDIFF(fecha_vencimiento, CURDATE()) as dias_restantes,
    CASE 
        WHEN porcentaje_ejecucion >= 100 THEN 'Completado'
        WHEN porcentaje_ejecucion >= 75 THEN 'Avanzado'
        WHEN porcentaje_ejecucion >= 50 THEN 'En Proceso'
        WHEN porcentaje_ejecucion > 0 THEN 'Iniciado'
        ELSE 'Sin Iniciar'
    END as estado_progreso
FROM contratos 
WHERE usuario_id = :usuario_id 
ORDER BY fecha_creacion DESC 
LIMIT 10";
$stmt = $pdo->prepare($sql_recientes);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$contratos_recientes = $stmt->fetchAll();

// Contratos por Mes de Vencimiento
$sql_vencimiento = "SELECT 
    DATE_FORMAT(fecha_vencimiento, '%Y-%m') as mes_vencimiento,
    COUNT(*) as cantidad_contratos,
    SUM(valor_pesos_sin_iva) as valor_total,
    AVG(porcentaje_ejecucion) as promedio_ejecucion
FROM contratos 
WHERE usuario_id = :usuario_id 
    AND fecha_vencimiento IS NOT NULL
    AND fecha_vencimiento >= CURDATE()
GROUP BY DATE_FORMAT(fecha_vencimiento, '%Y-%m')
ORDER BY mes_vencimiento ASC
LIMIT 12";
$stmt = $pdo->prepare($sql_vencimiento);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$datos_vencimiento = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - Sistema de Gestión Contractual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-gray-900">Estadísticas</h1>
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
        <!-- KPIs Principales -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-contract text-blue-500 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Contratos</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo number_format($kpis['total_contratos']); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-dollar-sign text-green-500 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Valor Total</dt>
                                <dd class="text-lg font-medium text-gray-900">$<?php echo number_format($kpis['valor_total']); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line text-purple-500 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Promedio Ejecución</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo number_format($kpis['promedio_ejecucion'], 1); ?>%</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Completados</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo number_format($kpis['contratos_completados']); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Vencidos</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo number_format($kpis['contratos_vencidos']); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-receipt text-indigo-500 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Facturado</dt>
                                <dd class="text-lg font-medium text-gray-900">$<?php echo number_format($kpis['valor_facturado_total']); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Análisis de Rentabilidad -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                        Análisis de Rentabilidad por Categoría
                    </h3>
                    <div class="h-80">
                        <canvas id="rentabilidadChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Análisis de Riesgo -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-shield-alt text-red-500 mr-2"></i>
                        Análisis de Riesgo por Vencimiento
                    </h3>
                    <div class="h-80">
                        <canvas id="riesgoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica de Tendencias -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Distribución de Contratos por Estado -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-chart-pie text-purple-500 mr-2"></i>
                        Distribución de Contratos por Estado
                    </h3>
                    <div class="h-96">
                        <canvas id="distribucionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Contratos por Mes de Vencimiento -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-calendar-times text-orange-500 mr-2"></i>
                        Contratos por Mes de Vencimiento
                    </h3>
                    <div class="h-96">
                        <canvas id="vencimientoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Triple Gráfica -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Top Clientes -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                        Top 5 Clientes
                    </h3>
                    <div class="h-80">
                        <canvas id="topClientesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Análisis Trimestral -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-calendar-alt text-green-500 mr-2"></i>
                        Performance Trimestral
                    </h3>
                    <div class="h-80">
                        <canvas id="trimestralChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Estado de Progreso -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-tags text-indigo-500 mr-2"></i>
                        Valor Promedio por Categoría
                    </h3>
                    <div class="h-80">
                        <canvas id="valorCategoriaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Contratos Recientes -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    <i class="fas fa-table text-gray-500 mr-2"></i>
                    Contratos Recientes
                </h3>
                
                <?php if (!empty($contratos_recientes)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progreso</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Días Restantes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Riesgo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($contratos_recientes as $contrato): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($contrato['cliente'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php echo htmlspecialchars(substr($contrato['descripcion'] ?? 'Sin descripción', 0, 50)) . '...'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    $<?php echo number_format($contrato['valor_pesos_sin_iva'] ?? 0); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $contrato['porcentaje_ejecucion'] ?? 0; ?>%"></div>
                                        </div>
                                        <span class="text-xs font-medium"><?php echo number_format($contrato['porcentaje_ejecucion'] ?? 0, 0); ?>%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $estado_class = 'bg-gray-100 text-gray-800';
                                    switch($contrato['estado_progreso']) {
                                        case 'Completado': $estado_class = 'bg-green-100 text-green-800'; break;
                                        case 'Avanzado': $estado_class = 'bg-blue-100 text-blue-800'; break;
                                        case 'En Proceso': $estado_class = 'bg-yellow-100 text-yellow-800'; break;
                                        case 'Iniciado': $estado_class = 'bg-purple-100 text-purple-800'; break;
                                    }
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $estado_class; ?>">
                                        <?php echo $contrato['estado_progreso']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php 
                                    $dias = $contrato['dias_restantes'];
                                    if ($dias < 0) {
                                        echo "<span class='text-red-600 font-medium'>Vencido</span>";
                                    } else {
                                        echo $dias . " días";
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php 
                                    $dias = $contrato['dias_restantes'];
                                    if ($dias < 0) {
                                        echo "🔴 Crítico";
                                    } elseif ($dias <= 7) {
                                        echo "🟠 Alto";
                                    } elseif ($dias <= 30) {
                                        echo "🟡 Medio";
                                    } else {
                                        echo "🟢 Bajo";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-chart-bar text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500 text-lg">No hay datos de contratos para mostrar estadísticas.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Configuración global de Chart.js
        Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#374151';
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.8)';
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
        Chart.defaults.plugins.tooltip.padding = 12;

        // Paleta de colores
        const colors = {
            primary: ['#3B82F6', '#8B5CF6', '#06B6D4', '#10B981', '#F59E0B'],
            success: ['#10B981', '#059669', '#047857', '#065F46'],
            warning: ['#F59E0B', '#D97706', '#B45309', '#92400E'],
            danger: ['#EF4444', '#DC2626', '#B91C1C', '#991B1B']
        };

        // Gráfica de Rentabilidad por Categoría
        const rentabilidadCtx = document.getElementById('rentabilidadChart').getContext('2d');
        new Chart(rentabilidadCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($rentabilidad_categorias, 'categoria')); ?>,
                datasets: [{
                    label: 'Rentabilidad (%)',
                    data: <?php echo json_encode(array_column($rentabilidad_categorias, 'rentabilidad')); ?>,
                    backgroundColor: colors.primary,
                    borderWidth: 0,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(1) + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Gráfica de Análisis de Riesgo
        const riesgoCtx = document.getElementById('riesgoChart').getContext('2d');
        new Chart(riesgoCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($analisis_riesgo, 'nivel_riesgo')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($analisis_riesgo, 'cantidad')); ?>,
                    backgroundColor: colors.danger,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfica de Distribución de Contratos por Estado
        const distribucionCtx = document.getElementById('distribucionChart').getContext('2d');
        new Chart(distribucionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completados', 'En Proceso', 'Sin Iniciar'],
                datasets: [{
                    data: [
                        <?php echo $kpis['contratos_completados']; ?>,
                        <?php echo $kpis['contratos_en_proceso']; ?>,
                        <?php echo $kpis['contratos_sin_iniciar']; ?>
                    ],
                    backgroundColor: ['#10B981', '#F59E0B', '#6B7280'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfica de Contratos por Mes de Vencimiento
        const vencimientoCtx = document.getElementById('vencimientoChart').getContext('2d');
        new Chart(vencimientoCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($datos_vencimiento, 'mes_vencimiento')); ?>,
                datasets: [{
                    label: 'Cantidad de Contratos',
                    data: <?php echo json_encode(array_column($datos_vencimiento, 'cantidad_contratos')); ?>,
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#F59E0B',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }, {
                    label: 'Valor Total ($)',
                    data: <?php echo json_encode(array_column($datos_vencimiento, 'valor_total')); ?>,
                    borderColor: '#EF4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#EF4444',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad de Contratos'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Valor Total ($)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Mes de Vencimiento'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 0) {
                                    return `Contratos: ${context.parsed.y}`;
                                } else {
                                    return `Valor: $${context.parsed.y.toLocaleString()}`;
                                }
                            }
                        }
                    }
                }
            }
        });

        // Top Clientes
        const topClientesCtx = document.getElementById('topClientesChart').getContext('2d');
        new Chart(topClientesCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($top_clientes, 'cliente')); ?>,
                datasets: [{
                    label: 'Valor Total ($)',
                    data: <?php echo json_encode(array_column($top_clientes, 'valor_total')); ?>,
                    backgroundColor: colors.primary,
                    borderWidth: 0,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Performance Trimestral
        const trimestralCtx = document.getElementById('trimestralChart').getContext('2d');
        new Chart(trimestralCtx, {
            type: 'radar',
            data: {
                labels: <?php echo json_encode(array_column($datos_trimestrales, 'trimestre')); ?>,
                datasets: [{
                    label: 'Cantidad de Contratos',
                    data: <?php echo json_encode(array_column($datos_trimestrales, 'cantidad')); ?>,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderWidth: 2
                }, {
                    label: 'Promedio Ejecución (%)',
                    data: <?php echo json_encode(array_column($datos_trimestrales, 'promedio_ejecucion')); ?>,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Valor Promedio por Categoría
        const valorCategoriaCtx = document.getElementById('valorCategoriaChart').getContext('2d');
        new Chart(valorCategoriaCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($rentabilidad_categorias, 'categoria')); ?>,
                datasets: [{
                    label: 'Valor Promedio ($)',
                    data: <?php echo json_encode(array_column($rentabilidad_categorias, 'valor_total')); ?>,
                    backgroundColor: colors.primary,
                    borderWidth: 0,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>

