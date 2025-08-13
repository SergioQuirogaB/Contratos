<?php
require_once '../includes/functions.php';

// Verificar si está logueado
if (!isLoggedIn()) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    $email = cleanInput($_POST['email']);
    $password_actual = $_POST['password_actual'];
    $password_nueva = $_POST['password_nueva'];
    $password_confirmar = $_POST['password_confirmar'];
    
    if (empty($nombre) || empty($email)) {
        $error = 'Por favor, completa los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, ingresa un email válido.';
    } else {
        // Aquí puedes agregar la lógica para actualizar el perfil
        $success = 'Perfil actualizado exitosamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Sistema de Contratos</title>
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
            max-width: 600px;
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
        
        .profile-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .profile-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .submit-btn {
            width: 100%;
            padding: 12px 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
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
        
        .password-section {
            border-top: 2px solid #e9ecef;
            padding-top: 20px;
            margin-top: 20px;
        }
        
        .password-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
    </style>
</head>
<body class="page-container">
    <div class="content-container">
        <div class="page-header">
            <h1 class="admin-title">Mi Perfil</h1>
            <div class="user-info">
                <span class="user-name">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="home.php" class="back-btn">← Volver al Dashboard</a>
                <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
        
        <?php if ($error): ?>
            <?php echo showError($error); ?>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <?php echo showSuccess($success); ?>
        <?php endif; ?>
        
        <div class="profile-section">
            <h2 class="profile-title">👤 Información Personal</h2>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nombre">Nombre Completo *</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" required>
                </div>
                
                <div class="password-section">
                    <h3 class="password-title">🔒 Cambiar Contraseña</h3>
                    
                    <div class="form-group">
                        <label for="password_actual">Contraseña Actual</label>
                        <input type="password" id="password_actual" name="password_actual">
                    </div>
                    
                    <div class="form-group">
                        <label for="password_nueva">Nueva Contraseña</label>
                        <input type="password" id="password_nueva" name="password_nueva">
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirmar">Confirmar Nueva Contraseña</label>
                        <input type="password" id="password_confirmar" name="password_confirmar">
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">💾 Guardar Cambios</button>
            </form>
        </div>
    </div>
</body>
</html>
