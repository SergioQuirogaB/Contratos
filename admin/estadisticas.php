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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎯 Dashboard Ejecutivo Avanzado - Sistema de Contratos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        .page-container {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 25%, #667eea 50%, #764ba2 75%, #f093fb 100%);
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
            border-bottom: 4px solid rgba(102, 126, 234, 0.3);
        }
        
        .admin-title {
            font-size: 36px;
            font-weight: 900;
            background: linear-gradient(135deg, #1e3c72, #2a5298, #667eea);
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
            color: #1e3c72;
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
        
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .kpi-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 35px;
            border-radius: 25px;
            text-align: center;
            transition: all 0.5s ease;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.1);
        }
        
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.8s;
        }
        
        .kpi-card:hover::before {
            left: 100%;
        }
        
        .kpi-card:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 30px 60px rgba(102, 126, 234, 0.5);
        }
        
        .kpi-icon {
            font-size: 56px;
            margin-bottom: 20px;
            filter: drop-shadow(0 6px 12px rgba(0,0,0,0.3));
        }
        
        .kpi-number {
            font-size: 42px;
            font-weight: 900;
            margin-bottom: 10px;
            text-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        
        .kpi-label {
            font-size: 18px;
            opacity: 0.95;
            font-weight: 600;
        }
        
        .kpi-trend {
            font-size: 14px;
            margin-top: 10px;
            opacity: 0.8;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
        }
        
        .chart-container {
            background: white;
            padding: 35px;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 2px solid rgba(102, 126, 234, 0.1);
            transition: all 0.4s ease;
        }
        
        .chart-container:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        }
        
        .chart-title {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 30px;
            color: #1e3c72;
            text-align: center;
            position: relative;
        }
        
        .chart-title::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 2px;
        }
        
        .chart-wrapper {
            position: relative;
            height: 400px;
        }
        
        .full-width-chart {
            grid-column: 1 / -1;
        }
        
        .triple-chart {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
        }
        
        .recent-section {
            background: white;
            padding: 35px;
            border-radius: 25px;
            margin-top: 50px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 2px solid rgba(102, 126, 234, 0.1);
        }
        
        .recent-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 30px;
            color: #1e3c72;
            text-align: center;
        }
        
        .recent-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .recent-table th {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 20px 15px;
            text-align: left;
            font-weight: 700;
            font-size: 15px;
        }
        
        .recent-table td {
            padding: 18px 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        .recent-table tr:hover {
            background-color: #f8f9ff;
            transform: scale(1.01);
            transition: all 0.3s ease;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        
        .status-completado {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .status-avanzado {
            background: linear-gradient(135deg, #17a2b8, #20c997);
            color: white;
        }
        
        .status-proceso {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }
        
        .status-iniciado {
            background: linear-gradient(135deg, #6f42c1, #e83e8c);
            color: white;
        }
        
        .status-sin-iniciar {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }
        
        .risk-high {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        
        .risk-medium {
            background: linear-gradient(135deg, #fd7e14, #e83e8c);
            color: white;
        }
        
        .risk-low {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .progress-ring {
            position: relative;
            width: 70px;
            height: 70px;
        }
        
        .progress-ring-circle {
            stroke: #e9ecef;
            stroke-width: 6;
            fill: transparent;
        }
        
        .progress-ring-progress {
            stroke: #28a745;
            stroke-width: 6;
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
        
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .triple-chart {
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
            
            .kpi-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
        
        .loading {
            display: inline-block;
            width: 25px;
            height: 25px;
            border: 4px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .metric-highlight {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.3);
        }
    </style>
</head>
<body class="page-container">
    <div class="content-container">
        <div class="page-header">
            <h1 class="admin-title">🎯 Dashboard Ejecutivo Avanzado</h1>
            <div class="user-info">
                <span class="user-name">👋 Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="home.php" class="back-btn">← Dashboard</a>
                <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
        
        <!-- KPIs Principales -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon">📊</div>
                <div class="kpi-number"><?php echo number_format($kpis['total_contratos']); ?></div>
                <div class="kpi-label">Total Contratos</div>
                <div class="kpi-trend">📈 Portfolio Activo</div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon">💰</div>
                <div class="kpi-number">$<?php echo number_format($kpis['valor_total']); ?></div>
                <div class="kpi-label">Valor Total</div>
                <div class="kpi-trend">💎 Capital en Gestión</div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon">📈</div>
                <div class="kpi-number"><?php echo number_format($kpis['promedio_ejecucion'], 1); ?>%</div>
                <div class="kpi-label">Promedio Ejecución</div>
                <div class="kpi-trend">🎯 Eficiencia Operativa</div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon">✅</div>
                <div class="kpi-number"><?php echo number_format($kpis['contratos_completados']); ?></div>
                <div class="kpi-label">Completados</div>
                <div class="kpi-trend">🏆 Entregas Exitosas</div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon">⚠️</div>
                <div class="kpi-number"><?php echo number_format($kpis['contratos_vencidos']); ?></div>
                <div class="kpi-label">Vencidos</div>
                <div class="kpi-trend">🚨 Requiere Atención</div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon">🎯</div>
                <div class="kpi-number">$<?php echo number_format($kpis['valor_facturado_total']); ?></div>
                <div class="kpi-label">Facturado</div>
                <div class="kpi-trend">💵 Ingresos Realizados</div>
            </div>
        </div>
        
        <!-- Gráficas Avanzadas -->
        <div class="charts-grid">
            <!-- Análisis de Rentabilidad -->
            <div class="chart-container">
                <h3 class="chart-title">📊 Análisis de Rentabilidad por Categoría</h3>
                <div class="chart-wrapper">
                    <canvas id="rentabilidadChart"></canvas>
                </div>
            </div>
            
            <!-- Análisis de Riesgo -->
            <div class="chart-container">
                <h3 class="chart-title">⚠️ Análisis de Riesgo por Vencimiento</h3>
                <div class="chart-wrapper">
                    <canvas id="riesgoChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Gráfica de Tendencias Avanzada -->
        <div class="chart-container full-width-chart">
            <h3 class="chart-title">📈 Análisis de Tendencias y Proyección (24 Meses)</h3>
            <div class="chart-wrapper">
                <canvas id="tendenciasChart"></canvas>
            </div>
        </div>
        
        <!-- Triple Gráfica -->
        <div class="triple-chart">
            <!-- Top Clientes -->
            <div class="chart-container">
                <h3 class="chart-title">🏆 Top 5 Clientes</h3>
                <div class="chart-wrapper">
                    <canvas id="topClientesChart"></canvas>
                </div>
            </div>
            
            <!-- Análisis Trimestral -->
            <div class="chart-container">
                <h3 class="chart-title">📅 Performance Trimestral</h3>
                <div class="chart-wrapper">
                    <canvas id="trimestralChart"></canvas>
                </div>
            </div>
            
            <!-- Estado de Progreso -->
            <div class="chart-container">
                <h3 class="chart-title">🎯 Estado de Progreso</h3>
                <div class="chart-wrapper">
                    <canvas id="progresoChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Tabla de Contratos con Análisis -->
        <div class="recent-section">
            <h2 class="recent-title">📋 Análisis Detallado de Contratos Recientes</h2>
            <?php if (!empty($contratos_recientes)): ?>
            <table class="recent-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Descripción</th>
                        <th>Valor</th>
                        <th>Progreso</th>
                        <th>Estado</th>
                        <th>Días Restantes</th>
                        <th>Riesgo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contratos_recientes as $contrato): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($contrato['cliente'] ?? 'N/A'); ?></strong></td>
                        <td><?php echo htmlspecialchars(substr($contrato['descripcion'] ?? 'Sin descripción', 0, 70)) . '...'; ?></td>
                        <td><strong>$<?php echo number_format($contrato['valor_pesos_sin_iva'] ?? 0); ?></strong></td>
                        <td>
                            <div class="progress-ring">
                                <svg width="70" height="70">
                                    <circle class="progress-ring-circle" cx="35" cy="35" r="30"></circle>
                                    <circle class="progress-ring-progress" cx="35" cy="35" r="30" 
                                            stroke-dasharray="<?php echo ($contrato['porcentaje_ejecucion'] ?? 0) * 1.88; ?> 188"></circle>
                                </svg>
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 12px; font-weight: bold;">
                                    <?php echo number_format($contrato['porcentaje_ejecucion'] ?? 0, 0); ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php 
                            $estado_class = 'status-sin-iniciar';
                            switch($contrato['estado_progreso']) {
                                case 'Completado': $estado_class = 'status-completado'; break;
                                case 'Avanzado': $estado_class = 'status-avanzado'; break;
                                case 'En Proceso': $estado_class = 'status-proceso'; break;
                                case 'Iniciado': $estado_class = 'status-iniciado'; break;
                            }
                            ?>
                            <span class="status-badge <?php echo $estado_class; ?>">
                                <?php echo $contrato['estado_progreso']; ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $dias = $contrato['dias_restantes'];
                            if ($dias < 0) {
                                echo "<span class='status-badge risk-high'>Vencido</span>";
                            } elseif ($dias <= 7) {
                                echo "<span class='status-badge risk-high'>{$dias} días</span>";
                            } elseif ($dias <= 30) {
                                echo "<span class='status-badge risk-medium'>{$dias} días</span>";
                            } else {
                                echo "<span class='status-badge risk-low'>{$dias} días</span>";
                            }
                            ?>
                        </td>
                        <td>
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
            <?php else: ?>
            <div class="no-data">
                <p>🎉 ¡Excelente! No hay contratos recientes para mostrar.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Configuración global avanzada de Chart.js
        Chart.defaults.font.family = 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif';
        Chart.defaults.font.size = 14;
        Chart.defaults.color = '#333';
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.9)';
        Chart.defaults.plugins.tooltip.cornerRadius = 12;
        Chart.defaults.plugins.tooltip.padding = 15;
        Chart.defaults.plugins.tooltip.titleFont.size = 16;
        Chart.defaults.plugins.tooltip.bodyFont.size = 14;

        // Paleta de colores ejecutiva
        const executiveColors = {
            primary: ['#1e3c72', '#2a5298', '#667eea', '#764ba2', '#f093fb'],
            success: ['#28a745', '#20c997', '#17a2b8', '#6f42c1'],
            warning: ['#ffc107', '#fd7e14', '#e83e8c', '#dc3545'],
            risk: ['#dc3545', '#fd7e14', '#ffc107', '#28a745']
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
                    backgroundColor: executiveColors.primary,
                    borderWidth: 3,
                    borderRadius: 10,
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
                            callback: function(value) {
                                return value.toFixed(1) + '%';
                            },
                            font: { weight: '700', size: 12 }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)',
                            lineWidth: 2
                        }
                    },
                    x: {
                        ticks: {
                            font: { weight: '700', size: 11 }
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
                            label: function(context) {
                                return `Rentabilidad: ${context.parsed.y.toFixed(1)}%`;
                            }
                        }
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
                    backgroundColor: executiveColors.risk,
                    borderWidth: 0,
                    hoverOffset: 12,
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 30,
                            usePointStyle: true,
                            font: { size: 14, weight: '700' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${context.label}: ${context.parsed} contratos (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Gráfica de Tendencias Avanzada
        const tendenciasCtx = document.getElementById('tendenciasChart').getContext('2d');
        new Chart(tendenciasCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($tendencias_mensuales, 'mes')); ?>,
                datasets: [{
                    label: 'Nuevos Contratos',
                    data: <?php echo json_encode(array_column($tendencias_mensuales, 'nuevos_contratos')); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 3,
                    pointRadius: 8,
                    pointHoverRadius: 12,
                    yAxisID: 'y'
                }, {
                    label: 'Valor Nuevo ($)',
                    data: <?php echo json_encode(array_column($tendencias_mensuales, 'valor_nuevo')); ?>,
                    borderColor: '#f093fb',
                    backgroundColor: 'rgba(240, 147, 251, 0.1)',
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#f093fb',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 3,
                    pointRadius: 8,
                    pointHoverRadius: 12,
                    yAxisID: 'y1'
                }, {
                    label: 'Promedio Ejecución (%)',
                    data: <?php echo json_encode(array_column($tendencias_mensuales, 'promedio_ejecucion')); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 4,
                    fill: false,
                    tension: 0.4,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 3,
                    pointRadius: 8,
                    pointHoverRadius: 12,
                    yAxisID: 'y2'
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
                        ticks: {
                            font: { weight: '700' }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            },
                            font: { weight: '700' }
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                    y2: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            },
                            font: { weight: '700' }
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            font: { weight: '700' }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 14, weight: '700' },
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        // Top Clientes
        const topClientesCtx = document.getElementById('topClientesChart').getContext('2d');
        new Chart(topClientesCtx, {
            type: 'horizontalBar',
            data: {
                labels: <?php echo json_encode(array_column($top_clientes, 'cliente')); ?>,
                datasets: [{
                    label: 'Valor Total ($)',
                    data: <?php echo json_encode(array_column($top_clientes, 'valor_total')); ?>,
                    backgroundColor: executiveColors.primary,
                    borderWidth: 2,
                    borderRadius: 8
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
                            },
                            font: { weight: '700' }
                        }
                    },
                    y: {
                        ticks: {
                            font: { weight: '700' }
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
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.2)',
                    borderWidth: 3,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }, {
                    label: 'Promedio Ejecución (%)',
                    data: <?php echo json_encode(array_column($datos_trimestrales, 'promedio_ejecucion')); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderWidth: 3,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        ticks: {
                            font: { weight: '700' }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 12, weight: '700' }
                        }
                    }
                }
            }
        });

        // Estado de Progreso
        const progresoCtx = document.getElementById('progresoChart').getContext('2d');
        new Chart(progresoCtx, {
            type: 'pie',
            data: {
                labels: ['Completados', 'En Proceso', 'Sin Iniciar'],
                datasets: [{
                    data: [
                        <?php echo $kpis['contratos_completados']; ?>,
                        <?php echo $kpis['contratos_en_proceso']; ?>,
                        <?php echo $kpis['contratos_sin_iniciar']; ?>
                    ],
                    backgroundColor: ['#28a745', '#ffc107', '#6c757d'],
                    borderWidth: 0,
                    hoverOffset: 8
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
                            font: { size: 12, weight: '700' }
                        }
                    }
                }
            }
        });

        // Animaciones de entrada avanzadas
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.kpi-card');
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

