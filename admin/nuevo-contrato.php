<?php
require_once '../includes/functions.php';

// Verificar si está logueado
if (!isLoggedIn()) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y limpiar todos los campos del formulario
    $ano = (int)cleanInput($_POST['ano']);
    $empresa = cleanInput($_POST['empresa']);
    $cliente = cleanInput($_POST['cliente']);
    $no_contrato = cleanInput($_POST['no_contrato']);
    $valor_pesos_sin_iva = (float)str_replace(['$', ',', ' '], '', $_POST['valor_pesos_sin_iva']);
    $valor_dolares = (float)str_replace(['$', ',', ' '], '', $_POST['valor_dolares']);
    $descripcion = cleanInput($_POST['descripcion']);
    $categoria = cleanInput($_POST['categoria']);
    $valor_mensual = (float)str_replace(['$', ',', ' '], '', $_POST['valor_mensual']);
    $observaciones = cleanInput($_POST['observaciones']);
    $fecha_inicio = cleanInput($_POST['fecha_inicio']);
    $fecha_vencimiento = cleanInput($_POST['fecha_vencimiento']);
    $valor_facturado = (float)str_replace(['$', ',', ' '], '', $_POST['valor_facturado']);
    $porcentaje_ejecucion = (float)str_replace(['%', ' '], '', $_POST['porcentaje_ejecucion']);
    $valor_pendiente_ejecutar = (float)str_replace(['$', ',', ' '], '', $_POST['valor_pendiente_ejecutar']);
    $estado = cleanInput($_POST['estado']);
    $no_horas = (int)cleanInput($_POST['no_horas']);
    $factura_no = cleanInput($_POST['factura_no']);
    $no_poliza = cleanInput($_POST['no_poliza']);
    $fecha_vencimiento_poliza = cleanInput($_POST['fecha_vencimiento_poliza']);
    
    // Validar campos obligatorios
    if (empty($cliente) || empty($descripcion) || empty($fecha_inicio) || empty($fecha_vencimiento)) {
        $error = 'Por favor, completa todos los campos obligatorios (Cliente, Descripción, Fecha de Inicio, Fecha de Vencimiento).';
    } else {
        try {
            // Guardar en la base de datos
            $datos_contrato = [
                [
                    $ano, $empresa, $cliente, $no_contrato, $valor_pesos_sin_iva, $valor_dolares,
                    $descripcion, $categoria, $valor_mensual, $observaciones, $fecha_inicio,
                    $fecha_vencimiento, $valor_facturado, $porcentaje_ejecucion, $valor_pendiente_ejecutar,
                    $estado, $no_horas, $factura_no, $no_poliza, $fecha_vencimiento_poliza
                ]
            ];
            
            $registros_guardados = guardarContratos($datos_contrato, $_SESSION['user_id']);
            $success = "✅ Contrato creado exitosamente. Se guardó en la base de datos.";
            
            // Limpiar el formulario después de guardar
            $_POST = array();
            
        } catch (Exception $e) {
            $error = 'Error al guardar el contrato: ' . $e->getMessage();
        }
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
        
        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
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
        
        .form-group label.required::after {
            content: " *";
            color: #dc3545;
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
            padding: 15px 25px;
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
        
        .section-divider {
            border-top: 2px solid #e1e5e9;
            margin: 30px 0;
            padding-top: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
    </style>
</head>
<body class="page-container">
    <div class="content-container">
        <div class="page-header">
            <h1 class="admin-title">Nuevo Contrato</h1>
            <div class="user-info">
                <span class="user-name">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="mis-contratos.php" class="back-btn">← Ver Mis Contratos</a>
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
                <!-- Información Básica -->
                <div class="section-title">📋 Información Básica</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="ano" class="required">Año</label>
                        <input type="number" id="ano" name="ano" value="<?php echo date('Y'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="empresa">Empresa</label>
                        <input type="text" id="empresa" name="empresa" value="<?php echo htmlspecialchars($_POST['empresa'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="cliente" class="required">Cliente</label>
                        <input type="text" id="cliente" name="cliente" value="<?php echo htmlspecialchars($_POST['cliente'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="no_contrato">Número de Contrato</label>
                        <input type="text" id="no_contrato" name="no_contrato" value="<?php echo htmlspecialchars($_POST['no_contrato'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descripcion" class="required">Descripción del Contrato</label>
                    <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="categoria">Categoría</label>
                        <input type="text" id="categoria" name="categoria" value="<?php echo htmlspecialchars($_POST['categoria'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="Pendiente" <?php echo ($_POST['estado'] ?? '') === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="En Proceso" <?php echo ($_POST['estado'] ?? '') === 'En Proceso' ? 'selected' : ''; ?>>En Proceso</option>
                            <option value="Activo" <?php echo ($_POST['estado'] ?? '') === 'Activo' ? 'selected' : ''; ?>>Activo</option>
                            <option value="Completado" <?php echo ($_POST['estado'] ?? '') === 'Completado' ? 'selected' : ''; ?>>Completado</option>
                            <option value="Cancelado" <?php echo ($_POST['estado'] ?? '') === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                </div>
                
                <!-- Valores Monetarios -->
                <div class="section-divider"></div>
                <div class="section-title">💰 Valores Monetarios</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="valor_pesos_sin_iva">Valor en Pesos (sin IVA)</label>
                        <input type="text" id="valor_pesos_sin_iva" name="valor_pesos_sin_iva" placeholder="$0.00" value="<?php echo htmlspecialchars($_POST['valor_pesos_sin_iva'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="valor_dolares">Valor en Dólares</label>
                        <input type="text" id="valor_dolares" name="valor_dolares" placeholder="$0.00" value="<?php echo htmlspecialchars($_POST['valor_dolares'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="valor_mensual">Valor Mensual</label>
                        <input type="text" id="valor_mensual" name="valor_mensual" placeholder="$0.00" value="<?php echo htmlspecialchars($_POST['valor_mensual'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="valor_facturado">Valor Facturado</label>
                        <input type="text" id="valor_facturado" name="valor_facturado" placeholder="$0.00" value="<?php echo htmlspecialchars($_POST['valor_facturado'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="porcentaje_ejecucion">% Ejecución</label>
                        <input type="text" id="porcentaje_ejecucion" name="porcentaje_ejecucion" placeholder="0%" value="<?php echo htmlspecialchars($_POST['porcentaje_ejecucion'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="valor_pendiente_ejecutar">Valor Pendiente por Ejecutar</label>
                        <input type="text" id="valor_pendiente_ejecutar" name="valor_pendiente_ejecutar" placeholder="$0.00" value="<?php echo htmlspecialchars($_POST['valor_pendiente_ejecutar'] ?? ''); ?>">
                    </div>
                </div>
                
                <!-- Fechas -->
                <div class="section-divider"></div>
                <div class="section-title">📅 Fechas</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha_inicio" class="required">Fecha de Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($_POST['fecha_inicio'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_vencimiento" class="required">Fecha de Vencimiento</label>
                        <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" value="<?php echo htmlspecialchars($_POST['fecha_vencimiento'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="fecha_vencimiento_poliza">Fecha de Vencimiento de Póliza</label>
                    <input type="date" id="fecha_vencimiento_poliza" name="fecha_vencimiento_poliza" value="<?php echo htmlspecialchars($_POST['fecha_vencimiento_poliza'] ?? ''); ?>">
                </div>
                
                <!-- Información Adicional -->
                <div class="section-divider"></div>
                <div class="section-title">📝 Información Adicional</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="no_horas">Número de Horas</label>
                        <input type="number" id="no_horas" name="no_horas" value="<?php echo htmlspecialchars($_POST['no_horas'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="factura_no">Número de Factura</label>
                        <input type="text" id="factura_no" name="factura_no" value="<?php echo htmlspecialchars($_POST['factura_no'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="no_poliza">Número de Póliza</label>
                    <input type="text" id="no_poliza" name="no_poliza" value="<?php echo htmlspecialchars($_POST['no_poliza'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones"><?php echo htmlspecialchars($_POST['observaciones'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="submit-btn">📝 Crear Contrato</button>
            </form>
        </div>
    </div>
    
    <script>
        // Establecer fecha actual como valor por defecto para fecha de inicio
        document.addEventListener('DOMContentLoaded', function() {
            const fechaInicio = document.getElementById('fecha_inicio');
            if (!fechaInicio.value) {
                fechaInicio.value = new Date().toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>
