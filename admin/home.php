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
    <title>Dashboard - Sistema de Contratos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .admin-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .action-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
        }
        
        .action-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .action-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .action-description {
            color: #666;
            margin-bottom: 20px;
        }
        
        .action-btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: transform 0.2s ease;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body style="background: #f8f9fa; min-height: 100vh; padding: 20px;">
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title">Mi Dashboard</h1>
            <div class="user-info">
                <span class="user-name">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
        
        <div class="welcome-card">
            <h2 class="welcome-title">Bienvenido al Sistema</h2>
            <p class="welcome-subtitle">Gestiona tus contratos y documentos desde aquí</p>
        </div>
        
        <div class="stats-grid">
            <?php if (isAdmin()): ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_usuarios; ?></div>
                <div class="stat-label">Usuarios Totales</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $usuarios_activos; ?></div>
                <div class="stat-label">Usuarios Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_admins; ?></div>
                <div class="stat-label">Administradores</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Contratos</div>
            </div>
            <?php else: ?>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Mis Contratos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Contratos Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Completados</div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="admin-actions">
            <div class="action-card">
                <div class="action-icon">📋</div>
                <h3 class="action-title">Mis Contratos</h3>
                <p class="action-description">Ver y gestionar tus contratos personales.</p>
                <a href="mis-contratos.php" class="action-btn">Ver Contratos</a>
            </div>
            
            <div class="action-card">
                <div class="action-icon">📄</div>
                <h3 class="action-title">Nuevo Contrato</h3>
                <p class="action-description">Crear un nuevo contrato.</p>
                <a href="nuevo-contrato.php" class="action-btn">Crear</a>
            </div>
            
            <div class="action-card">
                <div class="action-icon">👤</div>
                <h3 class="action-title">Mi Perfil</h3>
                <p class="action-description">Editar información personal.</p>
                <a href="perfil.php" class="action-btn">Editar</a>
            </div>
            
            <div class="action-card">
                <div class="action-icon">📊</div>
                <h3 class="action-title">Estadísticas</h3>
                <p class="action-description">Ver mis estadísticas personales.</p>
                <a href="estadisticas.php" class="action-btn">Ver</a>
            </div>
        </div>
    </div>
</body>
</html>
