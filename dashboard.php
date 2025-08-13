<?php
require_once 'includes/functions.php';

// Verificar si está logueado
if (!isLoggedIn()) {
    redirect('index.php');
}

// Si es admin, redirigir al panel admin
if (isAdmin()) {
    redirect('admin/home.php');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Contratos</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .user-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
        }
        
        .user-welcome {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .user-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .user-action {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .user-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .user-action-icon {
            font-size: 36px;
            margin-bottom: 15px;
        }
        
        .user-action-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .user-action-btn {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: transform 0.2s ease;
        }
        
        .user-action-btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body style="background: #f8f9fa; min-height: 100vh; padding: 20px;">
    <div class="user-container">
        <div class="admin-header">
            <h1 class="admin-title">Mi Dashboard</h1>
            <div class="user-info">
                <span class="user-name">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
        
        <div class="user-welcome">
            <h2 class="welcome-title">Bienvenido al Sistema</h2>
            <p class="welcome-subtitle">Gestiona tus contratos y documentos desde aquí</p>
        </div>
        
        <div class="user-actions">
            <div class="user-action">
                <div class="user-action-icon">📋</div>
                <h3 class="user-action-title">Mis Contratos</h3>
                <p style="color: #666; margin-bottom: 15px; font-size: 14px;">Ver y gestionar tus contratos</p>
                <a href="admin/mis-contratos.php" class="user-action-btn">Ver Contratos</a>
            </div>
            
            <div class="user-action">
                <div class="user-action-icon">📄</div>
                <h3 class="user-action-title">Nuevo Contrato</h3>
                <p style="color: #666; margin-bottom: 15px; font-size: 14px;">Crear un nuevo contrato</p>
                <a href="admin/nuevo-contrato.php" class="user-action-btn">Crear</a>
            </div>
            
            <div class="user-action">
                <div class="user-action-icon">👤</div>
                <h3 class="user-action-title">Mi Perfil</h3>
                <p style="color: #666; margin-bottom: 15px; font-size: 14px;">Editar información personal</p>
                <a href="admin/perfil.php" class="user-action-btn">Editar</a>
            </div>
            
            <div class="user-action">
                <div class="user-action-icon">📊</div>
                <h3 class="user-action-title">Estadísticas</h3>
                <p style="color: #666; margin-bottom: 15px; font-size: 14px;">Ver mis estadísticas</p>
                <a href="admin/estadisticas.php" class="user-action-btn">Ver</a>
            </div>
        </div>
    </div>
</body>
</html>
