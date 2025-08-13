<?php
require_once '../includes/functions.php';

// Verificar si está logueado
if (!isLoggedIn()) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente = cleanInput($_POST['cliente']);
    $descripcion = cleanInput($_POST['descripcion']);
    $fecha_inicio = cleanInput($_POST['fecha_inicio']);
    $fecha_fin = cleanInput($_POST['fecha_fin']);
    $valor = cleanInput($_POST['valor']);
    $estado = cleanInput($_POST['estado']);
    
    if (empty($cliente) || empty($descripcion) || empty($fecha_inicio) || empty($fecha_fin) || empty($valor)) {
        $error = 'Por favor, completa todos los campos obligatorios.';
    } else {
        // Aquí puedes agregar la lógica para guardar en la base de datos
        $success = 'Contrato creado exitosamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Contrato - Sistema de Contratos</title>
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
            max-width: 800px;
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
        
        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .form-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
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
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
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
    </style>
</head>
<body class="page-container">
    <div class="content-container">
        <div class="page-header">
            <h1 class="admin-title">Nuevo Contrato</h1>
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
        
        <div class="form-section">
            <h2 class="form-title">📄 Crear Nuevo Contrato</h2>
            
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="cliente">Cliente *</label>
                        <input type="text" id="cliente" name="cliente" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="valor">Valor *</label>
                        <input type="text" id="valor" name="valor" placeholder="$0.00" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción del Contrato *</label>
                    <textarea id="descripcion" name="descripcion" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Inicio *</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_fin">Fecha de Fin *</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="Pendiente">Pendiente</option>
                        <option value="En Proceso">En Proceso</option>
                        <option value="Activo">Activo</option>
                        <option value="Completado">Completado</option>
                        <option value="Cancelado">Cancelado</option>
                    </select>
                </div>
                
                <button type="submit" class="submit-btn">📝 Crear Contrato</button>
            </form>
        </div>
    </div>
</body>
</html>
