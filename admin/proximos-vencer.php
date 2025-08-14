<?php
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../index.php');
}

$usuario_id = $_SESSION['user_id'];

// Contratos próximos a vencer (próximos 60 días)
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
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 0 THEN 'Vencido'
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 7 THEN 'Crítico'
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 15 THEN 'Alto'
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 30 THEN 'Medio'
        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 60 THEN 'Bajo'
        ELSE 'Muy Bajo'
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
ORDER BY fecha_vencimiento ASC";
$stmt = $pdo->prepare($sql_proximos_vencer);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$contratos_proximos = $stmt->fetchAll();

// Estadísticas de contratos próximos a vencer
$sql_stats_vencer = "SELECT 
    COUNT(*) as total_proximos,
    COUNT(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 0 THEN 1 END) as vencidos,
    COUNT(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 7 THEN 1 END) as criticos,
    COUNT(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 8 AND 15 THEN 1 END) as altos,
    COUNT(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 16 AND 30 THEN 1 END) as medios,
    COUNT(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 31 AND 60 THEN 1 END) as bajos,
    SUM(valor_pesos_sin_iva) as valor_total,
    AVG(porcentaje_ejecucion) as promedio_ejecucion
FROM contratos 
WHERE usuario_id = :usuario_id 
    AND fecha_vencimiento IS NOT NULL
    AND DATEDIFF(fecha_vencimiento, CURDATE()) <= 60";
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
    AND categoria IS NOT NULL
GROUP BY categoria
ORDER BY cantidad DESC";
$stmt = $pdo->prepare($sql_categoria_vencer);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$categorias_vencer = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚠️ Contratos Próximos a Vencer - Sistema de Contratos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        .page-container {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 25%, #ff9ff3 50%, #f368e0 75%, #ff3838 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .content-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 1800px;
            margin: 0 auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 4px solid rgba(255, 107, 107, 0.3);
        }
        
        .admin-title {
            font-size: 36px;
            font-weight: 900;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24, #ff3838);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            text-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 25px;
        }
        
        .user-name {
            font-weight: 700;
            color: #ff6b6b;
            font-size: 18px;
        }
        
        .back-btn, .logout-btn {
            display: inline-block;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 15px;
            font-weight: 700;
            transition: all 0.4s ease;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            font-size: 14px;
        }
        
        .back-btn {
            background: linear-gradient(135deg, #495057, #343a40);
            color: white;
        }
        
        .back-btn:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 15px 35px rgba(73, 80, 87, 0.4);
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        
        .logout-btn:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 15px 35px rgba(220, 53, 69, 0.4);
        }
        
        .alert-banner {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 25px;
            border-radius: 20px;
            margin-bottom: 40px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(255, 107, 107, 0.3);
            border: 2px solid rgba(255,255,255,0.2);
        }
        
        .alert-title {
            font-size: 28px;
            font-weight: 900;
            margin-bottom: 10px;
        }
        
        .alert-subtitle {
            font-size: 18px;
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.4s ease;
            box-shadow: 0 15px 35px rgba(255, 107, 107, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.8s;
        }
        
        .stat-card:hover::before {
            left: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px rgba(255, 107, 107, 0.5);
        }
        
        .stat-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 900;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .charts-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
        }
        
        .chart-container {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 2px solid rgba(255, 107, 107, 0.1);
        }
        
        .chart-title {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 25px;
            color: #ff6b6b;
            text-align: center;
        }
        
        .chart-wrapper {
            height: 300px;
        }
        
        .contracts-table {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }
        
        .table-header {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 25px;
            text-align: center;
        }
        
        .table-title {
            font-size: 24px;
            font-weight: 800;
            margin: 0;
        }
        
        .table-subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin-top: 8px;
        }
        
        .contracts-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .contracts-table th {
            background: #f8f9fa;
            color: #333;
            padding: 18px 15px;
            text-align: left;
            font-weight: 700;
            font-size: 14px;
            border-bottom: 2px solid #dee2e6;
        }
        
        .contracts-table td {
            padding: 20px 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        .contracts-table tr:hover {
            background-color: #fff5f5;
            transform: scale(1.01);
            transition: all 0.3s ease;
        }
        
        .urgency-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        
        .urgency-vencido {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        
        .urgency-critico {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            animation: pulse 2s infinite;
        }
        
        .urgency-alto {
            background: linear-gradient(135deg, #fd7e14, #e83e8c);
            color: white;
        }
        
        .urgency-medio {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }
        
        .urgency-bajo {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .progress-ring {
            position: relative;
            width: 60px;
            height: 60px;
        }
        
        .progress-ring-circle {
            stroke: #e9ecef;
            stroke-width: 5;
            fill: transparent;
        }
        
        .progress-ring-progress {
            stroke: #28a745;
            stroke-width: 5;
            fill: transparent;
            stroke-linecap: round;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
            transition: stroke-dasharray 0.8s ease;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 50px;
            font-size: 18px;
        }
        
        .days-remaining {
            font-weight: 700;
            font-size: 16px;
        }
        
        .days-vencido {
            color: #dc3545;
        }
        
        .days-critico {
            color: #ff6b6b;
        }
        
        .days-alto {
            color: #fd7e14;
        }
        
        .days-medio {
            color: #ffc107;
        }
        
        .days-bajo {
            color: #28a745;
        }
        
        @media (max-width: 768px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                gap: 25px;
                text-align: center;
            }
            
            .user-info {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .contracts-table {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body class="page-container">
    <div class="content-container">
        <div class="page-header">
            <h1 class="admin-title">⚠️ Contratos Próximos a Vencer</h1>
            <div class="user-info">
                <span class="user-name">👋 Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="home.php" class="back-btn">← Dashboard</a>
                <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
        
        <!-- Banner de Alerta -->
        <div class="alert-banner">
            <div class="alert-title">🚨 Atención Requerida</div>
            <div class="alert-subtitle">
                Tienes <strong><?php echo $stats_vencer['total_proximos']; ?> contratos</strong> próximos a vencer en los próximos 60 días
            </div>
        </div>
        
        <!-- Estadísticas de Urgencia -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🔴</div>
                <div class="stat-number"><?php echo $stats_vencer['vencidos']; ?></div>
                <div class="stat-label">Vencidos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⚠️</div>
                <div class="stat-number"><?php echo $stats_vencer['criticos']; ?></div>
                <div class="stat-label">Críticos (≤7 días)</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🟠</div>
                <div class="stat-number"><?php echo $stats_vencer['altos']; ?></div>
                <div class="stat-label">Alto Riesgo (8-15 días)</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🟡</div>
                <div class="stat-number"><?php echo $stats_vencer['medios']; ?></div>
                <div class="stat-label">Medio Riesgo (16-30 días)</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🟢</div>
                <div class="stat-number"><?php echo $stats_vencer['bajos']; ?></div>
                <div class="stat-label">Bajo Riesgo (31-60 días)</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number">$<?php echo number_format($stats_vencer['valor_total']); ?></div>
                <div class="stat-label">Valor Total en Riesgo</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number"><?php echo number_format($stats_vencer['promedio_ejecucion'], 1); ?>%</div>
                <div class="stat-label">Promedio Ejecución</div>
            </div>
        </div>
        
        <!-- Gráficas de Análisis -->
        <div class="charts-section">
            <div class="chart-container">
                <h3 class="chart-title">📊 Distribución por Nivel de Urgencia</h3>
                <div class="chart-wrapper">
                    <canvas id="urgenciaChart"></canvas>
                </div>
            </div>
            
            <div class="chart-container">
                <h3 class="chart-title">📈 Análisis por Categoría</h3>
                <div class="chart-wrapper">
                    <canvas id="categoriaChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Tabla de Contratos Próximos a Vencer -->
        <div class="contracts-table">
            <div class="table-header">
                <h2 class="table-title">📋 Lista de Contratos Próximos a Vencer</h2>
                <div class="table-subtitle">Ordenados por fecha de vencimiento (más próximos primero) - Próximos 60 días</div>
            </div>
            
            <?php if (!empty($contratos_proximos)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Descripción</th>
                        <th>Valor</th>
                        <th>Progreso</th>
                        <th>Días Restantes</th>
                        <th>Nivel Urgencia</th>
                        <th>Estado</th>
                        <th>Categoría</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contratos_proximos as $contrato): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($contrato['cliente'] ?? 'N/A'); ?></strong></td>
                        <td><?php echo htmlspecialchars(substr($contrato['descripcion'] ?? 'Sin descripción', 0, 60)) . '...'; ?></td>
                        <td><strong>$<?php echo number_format($contrato['valor_pesos_sin_iva'] ?? 0); ?></strong></td>
                        <td>
                            <div class="progress-ring">
                                <svg width="60" height="60">
                                    <circle class="progress-ring-circle" cx="30" cy="30" r="25"></circle>
                                    <circle class="progress-ring-progress" cx="30" cy="30" r="25" 
                                            stroke-dasharray="<?php echo ($contrato['porcentaje_ejecucion'] ?? 0) * 1.57; ?> 157"></circle>
                                </svg>
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 10px; font-weight: bold;">
                                    <?php echo number_format($contrato['porcentaje_ejecucion'] ?? 0, 0); ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php 
                            $dias = $contrato['dias_restantes'];
                            $dias_class = 'days-bajo';
                                                         if ($dias < 0) $dias_class = 'days-vencido';
                             elseif ($dias <= 7) $dias_class = 'days-critico';
                             elseif ($dias <= 15) $dias_class = 'days-alto';
                             elseif ($dias <= 30) $dias_class = 'days-medio';
                             elseif ($dias <= 60) $dias_class = 'days-bajo';
                            ?>
                            <span class="days-remaining <?php echo $dias_class; ?>">
                                <?php 
                                if ($dias < 0) {
                                    echo abs($dias) . ' días vencido';
                                } else {
                                    echo $dias . ' días';
                                }
                                ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $urgencia_class = 'urgency-bajo';
                                                         switch($contrato['nivel_urgencia']) {
                                 case 'Vencido': $urgencia_class = 'urgency-vencido'; break;
                                 case 'Crítico': $urgencia_class = 'urgency-critico'; break;
                                 case 'Alto': $urgencia_class = 'urgency-alto'; break;
                                 case 'Medio': $urgencia_class = 'urgency-medio'; break;
                                 case 'Bajo': $urgencia_class = 'urgency-bajo'; break;
                             }
                            ?>
                            <span class="urgency-badge <?php echo $urgencia_class; ?>">
                                <?php echo $contrato['nivel_urgencia']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="urgency-badge urgency-bajo">
                                <?php echo $contrato['estado_progreso']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($contrato['categoria'] ?? 'Sin categoría'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-data">
                <p>🎉 ¡Excelente! No tienes contratos próximos a vencer en los próximos 60 días.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Configuración global de Chart.js
        Chart.defaults.font.family = 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif';
        Chart.defaults.font.size = 14;
        Chart.defaults.color = '#333';
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.9)';
        Chart.defaults.plugins.tooltip.cornerRadius = 12;
        Chart.defaults.plugins.tooltip.padding = 15;

        // Colores para niveles de urgencia
        const urgencyColors = {
            vencido: '#dc3545',
            critico: '#ff6b6b',
            alto: '#fd7e14',
            medio: '#ffc107',
            bajo: '#28a745'
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
                    borderWidth: 0,
                    hoverOffset: 12,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 25,
                            usePointStyle: true,
                            font: { size: 14, weight: '700' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${context.parsed} contratos (${percentage}%)`;
                            }
                        }
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
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { weight: '700' }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            font: { weight: '700', size: 12 }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                const categorias = <?php echo json_encode($categorias_vencer); ?>;
                                if (categorias[index]) {
                                    return [
                                        `Valor: $${categorias[index].valor_total.toLocaleString()}`,
                                        `Promedio Ejecución: ${categorias[index].promedio_ejecucion.toFixed(1)}%`,
                                        `Promedio Días Restantes: ${categorias[index].promedio_dias_restantes.toFixed(0)} días`
                                    ];
                                }
                                return '';
                            }
                        }
                    }
                }
            }
        });

        // Animaciones de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(30px) rotateX(10deg)';
                    card.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0) rotateX(0deg)';
                    }, 100);
                }, index * 150);
            });
        });
    </script>
</body>
</html>
