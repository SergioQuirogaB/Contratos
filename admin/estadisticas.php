<?php
require_once '../includes/functions.php';

// Verificar si está logueado
if (!isLoggedIn()) {
    redirect('../index.php');
}

// Datos de ejemplo para las estadísticas
$total_contratos = 15;
$contratos_activos = 8;
$contratos_completados = 5;
$contratos_pendientes = 2;
$valor_total = 450000;
$valor_promedio = 30000;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - Sistema de Contratos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-container {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 20px;
        }
        
        .content-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
            font-size: 16px;
            font-weight: 500;
        }
        
        .chart-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .chart-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
        }
        
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        
        .recent-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
        }
        
        .recent-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        
        .recent-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .recent-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .recent-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .recent-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-activo {
            background: #d4edda;
            color: #155724;
        }
        
        .status-completado {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-pendiente {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body class="page-container">
    <div class="content-container">
        <div class="page-header">
            <h1 class="admin-title">📊 Mis Estadísticas</h1>
            <div class="user-info">
                <span class="user-name">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="home.php" class="back-btn">← Volver al Dashboard</a>
                <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-number"><?php echo $total_contratos; ?></div>
                <div class="stat-label">Total de Contratos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?php echo $contratos_activos; ?></div>
                <div class="stat-label">Contratos Activos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-number"><?php echo $contratos_completados; ?></div>
                <div class="stat-label">Completados</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-number"><?php echo $contratos_pendientes; ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number">$<?php echo number_format($valor_total); ?></div>
                <div class="stat-label">Valor Total</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-number">$<?php echo number_format($valor_promedio); ?></div>
                <div class="stat-label">Valor Promedio</div>
            </div>
        </div>
        
        <div class="chart-section">
            <h2 class="chart-title">📊 Distribución de Contratos</h2>
            <div class="chart-container">
                <div style="margin-bottom: 20px;">
                    <strong>Activos:</strong> <?php echo round(($contratos_activos / $total_contratos) * 100); ?>%
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo ($contratos_activos / $total_contratos) * 100; ?>%"></div>
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <strong>Completados:</strong> <?php echo round(($contratos_completados / $total_contratos) * 100); ?>%
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo ($contratos_completados / $total_contratos) * 100; ?>%"></div>
                    </div>
                </div>
                
                <div>
                    <strong>Pendientes:</strong> <?php echo round(($contratos_pendientes / $total_contratos) * 100); ?>%
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo ($contratos_pendientes / $total_contratos) * 100; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="recent-section">
            <h2 class="recent-title">📋 Contratos Recientes</h2>
            <table class="recent-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Descripción</th>
                        <th>Valor</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Empresa ABC</td>
                        <td>Desarrollo de Software</td>
                        <td>$50,000</td>
                        <td><span class="status-badge status-activo">Activo</span></td>
                        <td>2024-01-15</td>
                    </tr>
                    <tr>
                        <td>Corporación XYZ</td>
                        <td>Consultoría IT</td>
                        <td>$75,000</td>
                        <td><span class="status-badge status-activo">Activo</span></td>
                        <td>2024-02-01</td>
                    </tr>
                    <tr>
                        <td>Startup Tech</td>
                        <td>Implementación CRM</td>
                        <td>$30,000</td>
                        <td><span class="status-badge status-completado">Completado</span></td>
                        <td>2024-03-10</td>
                    </tr>
                    <tr>
                        <td>Industrias DEF</td>
                        <td>Mantenimiento Web</td>
                        <td>$25,000</td>
                        <td><span class="status-badge status-activo">Activo</span></td>
                        <td>2024-01-01</td>
                    </tr>
                    <tr>
                        <td>Comercio GHI</td>
                        <td>Diseño de Base de Datos</td>
                        <td>$20,000</td>
                        <td><span class="status-badge status-pendiente">Pendiente</span></td>
                        <td>2024-04-01</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
