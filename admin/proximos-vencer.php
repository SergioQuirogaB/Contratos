<?php
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../index.php');
}

$usuario_id = $_SESSION['user_id'];

// Contratos próximos a vencer (próximos 60 días, excluyendo vencidos de más de 8 días)
$sql_proximos_vencer = "SELECT 
    id,
    cliente,
    descripcion,
    valor_pesos_sin_iva,
    porcentaje_ejecucion,
    fecha_inicio,
    fecha_vencimiento,
    DATEDIFF(fecha_vencimiento, CURDATE()) as dias_restantes,
    categoria,
    estado,
    CASE 
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 0 AND DATEDIFF(fecha_vencimiento, CURDATE()) >= -8 THEN 'Vencido'
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 15 THEN 'Crítico'
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 30 THEN 'Alto'
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 60 THEN 'Medio'
        ELSE 'Bajo'
    END as nivel_urgencia,
    CASE 
        WHEN porcentaje_ejecucion >= 100 THEN 'Completado'
        WHEN porcentaje_ejecucion >= 75 THEN 'Avanzado'
        WHEN porcentaje_ejecucion >= 50 THEN 'En Proceso'
        WHEN porcentaje_ejecucion > 0 THEN 'Iniciado'
        ELSE 'Sin Iniciar'
    END as estado_progreso
FROM contratos 
WHERE usuario_id = :usuario_id 
    AND fecha_vencimiento IS NOT NULL
    AND DATEDIFF(fecha_vencimiento, CURDATE()) <= 60
    AND DATEDIFF(fecha_vencimiento, CURDATE()) >= -8
ORDER BY fecha_vencimiento ASC";
$stmt = $pdo->prepare($sql_proximos_vencer);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$contratos_proximos = $stmt->fetchAll();

// Estadísticas de contratos próximos a vencer
$sql_stats_vencer = "SELECT 
    COUNT(*) as total_proximos,
    COUNT(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 0 AND DATEDIFF(fecha_vencimiento, CURDATE()) >= -8 THEN 1 END) as vencidos,
    COUNT(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 15 THEN 1 END) as criticos,
    COUNT(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 16 AND 30 THEN 1 END) as altos,
    COUNT(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 31 AND 60 THEN 1 END) as medios,
    COUNT(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) > 60 THEN 1 END) as bajos,
    SUM(valor_pesos_sin_iva) as valor_total,
    AVG(porcentaje_ejecucion) as promedio_ejecucion
FROM contratos 
WHERE usuario_id = :usuario_id 
    AND fecha_vencimiento IS NOT NULL
    AND DATEDIFF(fecha_vencimiento, CURDATE()) <= 60
    AND DATEDIFF(fecha_vencimiento, CURDATE()) >= -8";
$stmt = $pdo->prepare($sql_stats_vencer);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$stats_vencer = $stmt->fetch();

// Análisis por categoría de contratos próximos a vencer
$sql_categoria_vencer = "SELECT 
    categoria,
    COUNT(*) as cantidad,
    SUM(valor_pesos_sin_iva) as valor_total,
    AVG(porcentaje_ejecucion) as promedio_ejecucion,
    AVG(DATEDIFF(fecha_vencimiento, CURDATE())) as promedio_dias_restantes
FROM contratos 
WHERE usuario_id = :usuario_id 
    AND fecha_vencimiento IS NOT NULL
    AND DATEDIFF(fecha_vencimiento, CURDATE()) <= 60
    AND DATEDIFF(fecha_vencimiento, CURDATE()) >= -8
    AND categoria IS NOT NULL
GROUP BY categoria
ORDER BY cantidad DESC";
$stmt = $pdo->prepare($sql_categoria_vencer);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$categorias_vencer = $stmt->fetchAll();

// Valor en riesgo por semana (próximas 8 semanas)
$sql_valor_semana = "SELECT 
    CONCAT('Semana ', WEEK(fecha_vencimiento, 1)) as semana,
    DATE_FORMAT(fecha_vencimiento, '%Y-%m-%d') as fecha_inicio_semana,
    COUNT(*) as cantidad_contratos,
    SUM(valor_pesos_sin_iva) as valor_total,
    AVG(porcentaje_ejecucion) as promedio_ejecucion
FROM contratos 
WHERE usuario_id = :usuario_id 
    AND fecha_vencimiento IS NOT NULL
    AND DATEDIFF(fecha_vencimiento, CURDATE()) <= 60
    AND DATEDIFF(fecha_vencimiento, CURDATE()) >= -8
GROUP BY WEEK(fecha_vencimiento, 1)
ORDER BY fecha_vencimiento ASC
LIMIT 8";
$stmt = $pdo->prepare($sql_valor_semana);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$valor_semana = $stmt->fetchAll();

// Análisis de progreso vs tiempo restante (para identificar contratos críticos)
$sql_progreso_tiempo = "SELECT 
    CASE 
        WHEN porcentaje_ejecucion >= 100 THEN 'Completado'
        WHEN porcentaje_ejecucion >= 75 THEN 'Avanzado'
        WHEN porcentaje_ejecucion >= 50 THEN 'En Proceso'
        WHEN porcentaje_ejecucion > 0 THEN 'Iniciado'
        ELSE 'Sin Iniciar'
    END as estado_progreso,
    CASE 
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 0 THEN 'Vencido'
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 7 THEN 'Esta Semana'
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 15 THEN 'Próximos 15 días'
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 30 THEN 'Próximos 30 días'
        ELSE 'Más de 30 días'
    END as tiempo_restante,
    COUNT(*) as cantidad,
    SUM(valor_pesos_sin_iva) as valor_total
FROM contratos 
WHERE usuario_id = :usuario_id 
    AND fecha_vencimiento IS NOT NULL
    AND DATEDIFF(fecha_vencimiento, CURDATE()) <= 60
    AND DATEDIFF(fecha_vencimiento, CURDATE()) >= -8
GROUP BY 
    CASE 
        WHEN porcentaje_ejecucion >= 100 THEN 'Completado'
        WHEN porcentaje_ejecucion >= 75 THEN 'Avanzado'
        WHEN porcentaje_ejecucion >= 50 THEN 'En Proceso'
        WHEN porcentaje_ejecucion > 0 THEN 'Iniciado'
        ELSE 'Sin Iniciar'
    END,
    CASE 
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 0 THEN 'Vencido'
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 7 THEN 'Esta Semana'
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 15 THEN 'Próximos 15 días'
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 30 THEN 'Próximos 30 días'
        ELSE 'Más de 30 días'
    END
ORDER BY 
    CASE tiempo_restante
        WHEN 'Vencido' THEN 1
        WHEN 'Esta Semana' THEN 2
        WHEN 'Próximos 15 días' THEN 3
        WHEN 'Próximos 30 días' THEN 4
        ELSE 5
    END,
    CASE estado_progreso
        WHEN 'Sin Iniciar' THEN 1
        WHEN 'Iniciado' THEN 2
        WHEN 'En Proceso' THEN 3
        WHEN 'Avanzado' THEN 4
        WHEN 'Completado' THEN 5
    END";
$stmt = $pdo->prepare($sql_progreso_tiempo);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$progreso_tiempo = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas - Sistema de Gestión Contractual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-gray-900">Alertas</h1>
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
        <!-- Gráficas de Análisis -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-chart-pie text-red-500 mr-2"></i>
                        Distribución por Nivel de Urgencia
                    </h3>
                    <div class="h-80">
                        <canvas id="urgenciaChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-chart-bar text-orange-500 mr-2"></i>
                        Análisis por Categoría
                    </h3>
                    <div class="h-80">
                        <canvas id="categoriaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Contratos Próximos a Vencer -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    <i class="fas fa-table text-gray-500 mr-2"></i>
                    Lista de Contratos Próximos a Vencer
                </h3>
                <p class="text-sm text-gray-600 mb-6">Ordenados por fecha de vencimiento (más próximos primero) - Próximos 60 días, excluyendo vencidos de más de 8 días</p>
                
                <?php if (!empty($contratos_proximos)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progreso</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Días Restantes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nivel Urgencia</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($contratos_proximos as $contrato): ?>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php 
                                    $dias = $contrato['dias_restantes'];
                                    $dias_class = 'text-green-600';
                                    if ($dias < 0) $dias_class = 'text-red-600 font-bold';
                                    elseif ($dias <= 15) $dias_class = 'text-red-600';
                                    elseif ($dias <= 30) $dias_class = 'text-orange-600';
                                    elseif ($dias <= 60) $dias_class = 'text-yellow-600';
                                    ?>
                                    <span class="<?php echo $dias_class; ?> font-medium">
                                        <?php 
                                        if ($dias < 0) {
                                            echo abs($dias) . ' días vencido';
                                        } else {
                                            echo $dias . ' días';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $urgencia_class = 'bg-green-100 text-green-800';
                                    switch($contrato['nivel_urgencia']) {
                                        case 'Vencido': $urgencia_class = 'bg-red-100 text-red-800'; break;
                                        case 'Crítico': $urgencia_class = 'bg-red-100 text-red-800 animate-pulse'; break;
                                        case 'Alto': $urgencia_class = 'bg-orange-100 text-orange-800'; break;
                                        case 'Medio': $urgencia_class = 'bg-yellow-100 text-yellow-800'; break;
                                        case 'Bajo': $urgencia_class = 'bg-green-100 text-green-800'; break;
                                    }
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $urgencia_class; ?>">
                                        <?php echo $contrato['nivel_urgencia']; ?>
                                    </span>
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
                                    <?php echo htmlspecialchars($contrato['categoria'] ?? 'Sin categoría'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-check-circle text-green-400 text-4xl mb-4"></i>
                    <p class="text-gray-500 text-lg">¡Excelente! No tienes contratos próximos a vencer en los próximos 60 días.</p>
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

        // Colores para niveles de urgencia
        const urgencyColors = {
            vencido: '#EF4444',
            critico: '#DC2626',
            alto: '#F97316',
            medio: '#EAB308',
            bajo: '#22C55E'
        };

        // Gráfica de Distribución por Nivel de Urgencia
        const urgenciaCtx = document.getElementById('urgenciaChart').getContext('2d');
        new Chart(urgenciaCtx, {
            type: 'doughnut',
            data: {
                labels: ['Vencidos', 'Críticos', 'Alto Riesgo', 'Medio Riesgo', 'Bajo Riesgo'],
                datasets: [{
                    data: [
                        <?php echo $stats_vencer['vencidos']; ?>,
                        <?php echo $stats_vencer['criticos']; ?>,
                        <?php echo $stats_vencer['altos']; ?>,
                        <?php echo $stats_vencer['medios']; ?>,
                        <?php echo $stats_vencer['bajos']; ?>
                    ],
                    backgroundColor: [
                        urgencyColors.vencido,
                        urgencyColors.critico,
                        urgencyColors.alto,
                        urgencyColors.medio,
                        urgencyColors.bajo
                    ],
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

        // Gráfica de Análisis por Categoría
        const categoriaCtx = document.getElementById('categoriaChart').getContext('2d');
        new Chart(categoriaCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($categorias_vencer, 'categoria')); ?>,
                datasets: [{
                    label: 'Cantidad de Contratos',
                    data: <?php echo json_encode(array_column($categorias_vencer, 'cantidad')); ?>,
                    backgroundColor: urgencyColors.critico,
                    borderWidth: 0,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
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
