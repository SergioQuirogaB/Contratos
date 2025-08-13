<?php
require_once '../includes/functions.php';

// Verificar si está logueado
if (!isLoggedIn()) {
    redirect('../index.php');
}

$error = '';
$success = '';
$excel_data = [];

// Configuración de paginación
$registros_por_pagina = isset($_GET['registros']) ? (int)$_GET['registros'] : 25;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

// Validar valores de paginación
if (!in_array($registros_por_pagina, [25, 50, 100])) {
    $registros_por_pagina = 25;
}
if ($pagina_actual < 1) {
    $pagina_actual = 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_excel'])) {
        // Procesar subida de archivo
        if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['excel_file'];
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_extension, ['csv', 'txt'])) {
                try {
                    // Procesar archivo CSV
                    $handle = fopen($file['tmp_name'], 'r');
                    if ($handle !== false) {
                        $rows = [];
                        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                            $rows[] = $data;
                        }
                        fclose($handle);
                        
                        if (!empty($rows)) {
                            $_SESSION['excel_data'] = $rows;
                            $excel_data = $rows;
                            $success = 'Archivo CSV cargado exitosamente. Se encontraron ' . count($rows) . ' filas.';
                        } else {
                            $error = 'El archivo está vacío o no contiene datos válidos.';
                        }
                    } else {
                        $error = 'No se pudo abrir el archivo.';
                    }
                } catch (Exception $e) {
                    $error = 'Error al procesar el archivo: ' . $e->getMessage();
                }
            } else {
                $error = 'Tipo de archivo no válido. Solo se permiten archivos CSV (.csv) o texto (.txt).';
            }
        } else {
            $error = 'Error al subir el archivo.';
        }
    }
}

// Recuperar datos de la sesión si existen
if (isset($_SESSION['excel_data'])) {
    $excel_data = $_SESSION['excel_data'];
}

// Calcular paginación
$total_registros = count($excel_data) - 1; // Restamos 1 para excluir los encabezados
$total_paginas = ceil($total_registros / $registros_por_pagina);
$inicio = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener datos de la página actual
$datos_pagina = [];
if (!empty($excel_data)) {
    $encabezados = $excel_data[0];
    $datos = array_slice($excel_data, 1); // Excluir encabezados
    $datos_pagina = array_slice($datos, $inicio, $registros_por_pagina);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Contratos - Sistema de Contratos</title>
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
        
        .upload-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .upload-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
        
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-input {
            width: 100%;
            padding: 12px;
            border: 2px dashed #667eea;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }
        
        .file-input:hover {
            border-color: #764ba2;
        }
        
        .upload-btn {
            margin-top: 15px;
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
        
        .upload-btn:hover {
            transform: translateY(-2px);
        }
        
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .excel-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .excel-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .excel-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
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
        
        .instructions {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .instructions h4 {
            margin: 0 0 10px 0;
            color: #1976d2;
        }
        
        .instructions ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 5px;
        }
        
        .pagination-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .records-selector {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .records-selector label {
            font-weight: 500;
            color: #555;
        }
        
        .records-selector select {
            padding: 8px 12px;
            border: 2px solid #e1e5e9;
            border-radius: 6px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }
        
        .records-selector select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .pagination-info {
            color: #666;
            font-size: 14px;
        }
        
        .pagination {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #e1e5e9;
            border-radius: 4px;
            text-decoration: none;
            color: #667eea;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .pagination .current {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .pagination .disabled {
            color: #ccc;
            cursor: not-allowed;
            pointer-events: none;
        }
        
        .data-summary {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .data-summary h4 {
            margin: 0 0 10px 0;
            color: #1976d2;
        }
        
        .data-summary p {
            margin: 5px 0;
            color: #333;
        }
    </style>
</head>
<body class="page-container">
    <div class="content-container">
        <div class="page-header">
            <h1 class="admin-title">Mis Contratos</h1>
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
        
        <div class="upload-section">
            <h2 class="upload-title">📊 Subir Archivo de Contratos</h2>
            
            <div class="instructions">
                <h4>📋 Instrucciones:</h4>
                <ul>
                    <li>Sube un archivo CSV con los datos de tus contratos</li>
                    <li>La primera fila debe contener los nombres de las columnas</li>
                    <li>Los datos deben estar separados por comas</li>
                    <li>Puedes exportar desde Excel como CSV</li>
                </ul>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="file-input-wrapper">
                    <input type="file" name="excel_file" class="file-input" accept=".csv,.txt" required>
                </div>
                <button type="submit" name="upload_excel" class="upload-btn">📤 Subir y Procesar</button>
            </form>
        </div>
        
        <?php if (!empty($excel_data)): ?>
            <div class="excel-section">
                <h2 class="upload-title">📋 Datos de Contratos</h2>
                
                <div class="data-summary">
                    <h4>📊 Resumen de Datos</h4>
                    <p><strong>Total de registros:</strong> <?php echo $total_registros; ?></p>
                    <p><strong>Registros por página:</strong> <?php echo $registros_por_pagina; ?></p>
                    <p><strong>Página actual:</strong> <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?></p>
                </div>
                
                <!-- Controles de paginación -->
                <div class="pagination-controls">
                    <div class="records-selector">
                        <label for="registros">Mostrar:</label>
                        <select id="registros" onchange="cambiarRegistros(this.value)">
                            <option value="25" <?php echo $registros_por_pagina == 25 ? 'selected' : ''; ?>>25 registros</option>
                            <option value="50" <?php echo $registros_por_pagina == 50 ? 'selected' : ''; ?>>50 registros</option>
                            <option value="100" <?php echo $registros_por_pagina == 100 ? 'selected' : ''; ?>>100 registros</option>
                        </select>
                    </div>
                    
                    <div class="pagination-info">
                        Mostrando <?php echo $inicio + 1; ?> - <?php echo min($inicio + $registros_por_pagina, $total_registros); ?> de <?php echo $total_registros; ?> registros
                    </div>
                    
                    <div class="pagination">
                        <?php if ($pagina_actual > 1): ?>
                            <a href="?pagina=1&registros=<?php echo $registros_por_pagina; ?>">« Primera</a>
                            <a href="?pagina=<?php echo $pagina_actual - 1; ?>&registros=<?php echo $registros_por_pagina; ?>">‹ Anterior</a>
                        <?php else: ?>
                            <span class="disabled">« Primera</span>
                            <span class="disabled">‹ Anterior</span>
                        <?php endif; ?>
                        
                        <?php
                        $inicio_paginas = max(1, $pagina_actual - 2);
                        $fin_paginas = min($total_paginas, $pagina_actual + 2);
                        
                        for ($i = $inicio_paginas; $i <= $fin_paginas; $i++):
                        ?>
                            <?php if ($i == $pagina_actual): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?pagina=<?php echo $i; ?>&registros=<?php echo $registros_por_pagina; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($pagina_actual < $total_paginas): ?>
                            <a href="?pagina=<?php echo $pagina_actual + 1; ?>&registros=<?php echo $registros_por_pagina; ?>">Siguiente ›</a>
                            <a href="?pagina=<?php echo $total_paginas; ?>&registros=<?php echo $registros_por_pagina; ?>">Última »</a>
                        <?php else: ?>
                            <span class="disabled">Siguiente ›</span>
                            <span class="disabled">Última »</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="excel-table">
                        <thead>
                            <tr>
                                <?php if (!empty($encabezados)): ?>
                                    <?php foreach ($encabezados as $header): ?>
                                        <th><?php echo htmlspecialchars($header); ?></th>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($datos_pagina as $fila): ?>
                                <tr>
                                    <?php foreach ($fila as $cell): ?>
                                        <td><?php echo htmlspecialchars($cell); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Controles de paginación (abajo) -->
                <div class="pagination-controls">
                    <div class="records-selector">
                        <label for="registros2">Mostrar:</label>
                        <select id="registros2" onchange="cambiarRegistros(this.value)">
                            <option value="25" <?php echo $registros_por_pagina == 25 ? 'selected' : ''; ?>>25 registros</option>
                            <option value="50" <?php echo $registros_por_pagina == 50 ? 'selected' : ''; ?>>50 registros</option>
                            <option value="100" <?php echo $registros_por_pagina == 100 ? 'selected' : ''; ?>>100 registros</option>
                        </select>
                    </div>
                    
                    <div class="pagination-info">
                        Mostrando <?php echo $inicio + 1; ?> - <?php echo min($inicio + $registros_por_pagina, $total_registros); ?> de <?php echo $total_registros; ?> registros
                    </div>
                    
                    <div class="pagination">
                        <?php if ($pagina_actual > 1): ?>
                            <a href="?pagina=1&registros=<?php echo $registros_por_pagina; ?>">« Primera</a>
                            <a href="?pagina=<?php echo $pagina_actual - 1; ?>&registros=<?php echo $registros_por_pagina; ?>">‹ Anterior</a>
                        <?php else: ?>
                            <span class="disabled">« Primera</span>
                            <span class="disabled">‹ Anterior</span>
                        <?php endif; ?>
                        
                        <?php
                        $inicio_paginas = max(1, $pagina_actual - 2);
                        $fin_paginas = min($total_paginas, $pagina_actual + 2);
                        
                        for ($i = $inicio_paginas; $i <= $fin_paginas; $i++):
                        ?>
                            <?php if ($i == $pagina_actual): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?pagina=<?php echo $i; ?>&registros=<?php echo $registros_por_pagina; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($pagina_actual < $total_paginas): ?>
                            <a href="?pagina=<?php echo $pagina_actual + 1; ?>&registros=<?php echo $registros_por_pagina; ?>">Siguiente ›</a>
                            <a href="?pagina=<?php echo $total_paginas; ?>&registros=<?php echo $registros_por_pagina; ?>">Última »</a>
                        <?php else: ?>
                            <span class="disabled">Siguiente ›</span>
                            <span class="disabled">Última »</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="no-data">
                <h3>📄 No hay datos para mostrar</h3>
                <p>Sube un archivo CSV para ver los datos de contratos aquí.</p>
            </div>
        <?php endif; ?>
            </div>
        
        <script>
            function cambiarRegistros(valor) {
                // Obtener la página actual de la URL
                const urlParams = new URLSearchParams(window.location.search);
                const paginaActual = urlParams.get('pagina') || 1;
                
                // Redirigir con los nuevos parámetros
                window.location.href = `?pagina=${paginaActual}&registros=${valor}`;
            }
            
            // Sincronizar ambos selectores
            document.addEventListener('DOMContentLoaded', function() {
                const selector1 = document.getElementById('registros');
                const selector2 = document.getElementById('registros2');
                
                if (selector1 && selector2) {
                    selector1.addEventListener('change', function() {
                        selector2.value = this.value;
                    });
                    
                    selector2.addEventListener('change', function() {
                        selector1.value = this.value;
                    });
                }
            });
        </script>
    </body>
</html>
