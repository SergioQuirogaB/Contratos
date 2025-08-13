<?php
require_once 'includes/functions.php';

// Si ya está logueado, redirigir al dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/home.php');
    } else {
        redirect('dashboard.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';
    
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ? AND activo = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['rol'];
                
                if ($user['rol'] === 'admin') {
                    redirect('admin/home.php');
                } else {
                    redirect('dashboard.php');
                }
            } else {
                $error = 'Email o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            $error = 'Error en el sistema. Por favor, intenta más tarde.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Contratos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1 class="form-title">Iniciar Sesión</h1>
        
        <?php if ($error): ?>
            <?php echo showError($error); ?>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Iniciar Sesión</button>
        </form>
        
        <div class="form-footer">
            <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
        </div>
    </div>
</body>
</html>
