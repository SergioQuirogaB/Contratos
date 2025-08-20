<?php
session_start();

// Incluir configuración de base de datos
require_once __DIR__ . '/../config/database.php';

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Función para verificar si el usuario es admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Función para redirigir
function redirect($url) {
    header("Location: $url");
    exit();
}

// Función para limpiar datos de entrada
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para mostrar mensajes de error
function showError($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

// Función para mostrar mensajes de éxito
function showSuccess($message) {
    return "<div class='alert alert-success'>$message</div>";
}

// Función para guardar contratos en la base de datos
function guardarContratos($datos, $usuario_id) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO contratos (
            ano, empresa, cliente, no_contrato, valor_pesos_sin_iva, valor_dolares, 
            descripcion, categoria, valor_mensual, observaciones, fecha_inicio, 
            fecha_vencimiento, valor_facturado, porcentaje_ejecucion, valor_pendiente_ejecutar, 
            estado, no_horas, factura_no, no_poliza, fecha_vencimiento_poliza, usuario_id
        ) VALUES (
            :ano, :empresa, :cliente, :no_contrato, :valor_pesos_sin_iva, :valor_dolares,
            :descripcion, :categoria, :valor_mensual, :observaciones, :fecha_inicio,
            :fecha_vencimiento, :valor_facturado, :porcentaje_ejecucion, :valor_pendiente_ejecutar,
            :estado, :no_horas, :factura_no, :no_poliza, :fecha_vencimiento_poliza, :usuario_id
        )";
        
        $stmt = $pdo->prepare($sql);
        $registros_guardados = 0;
        
        foreach ($datos as $fila) {
            // Mapear las columnas del CSV a los campos de la BD
            $params = [
                ':ano' => !empty($fila[0]) ? (int)$fila[0] : null,
                ':empresa' => !empty($fila[1]) ? cleanInput($fila[1]) : null,
                ':cliente' => !empty($fila[2]) ? cleanInput($fila[2]) : null,
                ':no_contrato' => !empty($fila[3]) ? cleanInput($fila[3]) : null,
                ':valor_pesos_sin_iva' => !empty($fila[4]) ? (float)str_replace(['$', ',', ' '], '', $fila[4]) : null,
                ':valor_dolares' => !empty($fila[5]) ? (float)str_replace(['$', ',', ' '], '', $fila[5]) : null,
                ':descripcion' => !empty($fila[6]) ? cleanInput($fila[6]) : null,
                ':categoria' => !empty($fila[7]) ? cleanInput($fila[7]) : null,
                ':valor_mensual' => !empty($fila[8]) ? (float)str_replace(['$', ',', ' '], '', $fila[8]) : null,
                ':observaciones' => !empty($fila[9]) ? cleanInput($fila[9]) : null,
                ':fecha_inicio' => !empty($fila[10]) ? convertirFecha($fila[10]) : null,
                ':fecha_vencimiento' => !empty($fila[11]) ? convertirFecha($fila[11]) : null,
                ':valor_facturado' => !empty($fila[12]) ? (float)str_replace(['$', ',', ' '], '', $fila[12]) : null,
                ':porcentaje_ejecucion' => !empty($fila[13]) ? (float)str_replace(['%', ' '], '', $fila[13]) : null,
                ':valor_pendiente_ejecutar' => !empty($fila[14]) ? (float)str_replace(['$', ',', ' '], '', $fila[14]) : null,
                ':estado' => !empty($fila[15]) ? cleanInput($fila[15]) : null,
                ':no_horas' => !empty($fila[16]) ? (int)$fila[16] : null,
                ':factura_no' => !empty($fila[17]) ? cleanInput($fila[17]) : null,
                ':no_poliza' => !empty($fila[18]) ? cleanInput($fila[18]) : null,
                ':fecha_vencimiento_poliza' => !empty($fila[19]) ? convertirFecha($fila[19]) : null,
                ':usuario_id' => $usuario_id
            ];
            
            $stmt->execute($params);
            $registros_guardados++;
        }
        
        $pdo->commit();
        return $registros_guardados;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Función para convertir fechas de diferentes formatos
function convertirFecha($fecha) {
    if (empty($fecha)) return null;
    
    // Limpiar la fecha
    $fecha = trim($fecha);
    
    // Intentar diferentes formatos de fecha
    $formatos = [
        'd/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y', 'd.m.Y',
        'd/m/y', 'd-m-y', 'y-m-d', 'm/d/y', 'd.m.y'
    ];
    
    foreach ($formatos as $formato) {
        $fecha_obj = DateTime::createFromFormat($formato, $fecha);
        if ($fecha_obj !== false) {
            return $fecha_obj->format('Y-m-d');
        }
    }
    
    // Si no se puede convertir, devolver null
    return null;
}

// Función para obtener contratos del usuario
function obtenerContratosUsuario($usuario_id, $limit = null, $offset = null, $filtro_cliente = null) {
    global $pdo;
    
    $sql = "SELECT * FROM contratos WHERE usuario_id = :usuario_id";
    $params = [':usuario_id' => $usuario_id];
    
    // Agregar filtro por cliente si se especifica
    if (!empty($filtro_cliente)) {
        $sql .= " AND cliente LIKE :cliente";
        $params[':cliente'] = '%' . $filtro_cliente . '%';
    }
    
    // Ordenar por año descendente (más actual al más antiguo)
    $sql .= " ORDER BY ano DESC, fecha_creacion DESC";
    
    if ($limit !== null) {
        $sql .= " LIMIT :limit";
        if ($offset !== null) {
            $sql .= " OFFSET :offset";
        }
    }
    
    $stmt = $pdo->prepare($sql);
    
    // Bind de parámetros
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    if ($limit !== null) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        if ($offset !== null) {
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
    }
    
    $stmt->execute();
    return $stmt->fetchAll();
}

// Función para contar contratos del usuario
function contarContratosUsuario($usuario_id, $filtro_cliente = null) {
    global $pdo;
    
    $sql = "SELECT COUNT(*) as total FROM contratos WHERE usuario_id = :usuario_id";
    $params = [':usuario_id' => $usuario_id];
    
    // Agregar filtro por cliente si se especifica
    if (!empty($filtro_cliente)) {
        $sql .= " AND cliente LIKE :cliente";
        $params[':cliente'] = '%' . $filtro_cliente . '%';
    }
    
    $stmt = $pdo->prepare($sql);
    
    // Bind de parámetros
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $resultado = $stmt->fetch();
    return $resultado['total'];
}

// Función para obtener un contrato específico por ID
function obtenerContratoPorId($contrato_id, $usuario_id) {
    global $pdo;
    
    $sql = "SELECT * FROM contratos WHERE id = :contrato_id AND usuario_id = :usuario_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':contrato_id', $contrato_id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch();
}

// Función para actualizar un contrato
function actualizarContrato($contrato_id, $usuario_id, $datos) {
    global $pdo;
    
    try {
        $sql = "UPDATE contratos SET 
            ano = :ano,
            empresa = :empresa,
            cliente = :cliente,
            no_contrato = :no_contrato,
            valor_pesos_sin_iva = :valor_pesos_sin_iva,
            valor_dolares = :valor_dolares,
            descripcion = :descripcion,
            categoria = :categoria,
            valor_mensual = :valor_mensual,
            observaciones = :observaciones,
            fecha_inicio = :fecha_inicio,
            fecha_vencimiento = :fecha_vencimiento,
            valor_facturado = :valor_facturado,
            porcentaje_ejecucion = :porcentaje_ejecucion,
            valor_pendiente_ejecutar = :valor_pendiente_ejecutar,
            estado = :estado,
            no_horas = :no_horas,
            factura_no = :factura_no,
            no_poliza = :no_poliza,
            fecha_vencimiento_poliza = :fecha_vencimiento_poliza,
            fecha_actualizacion = NOW()
            WHERE id = :contrato_id AND usuario_id = :usuario_id";
        
        $stmt = $pdo->prepare($sql);
        
        $params = [
            ':ano' => !empty($datos['ano']) ? (int)$datos['ano'] : null,
            ':empresa' => !empty($datos['empresa']) ? cleanInput($datos['empresa']) : null,
            ':cliente' => !empty($datos['cliente']) ? cleanInput($datos['cliente']) : null,
            ':no_contrato' => !empty($datos['no_contrato']) ? cleanInput($datos['no_contrato']) : null,
            ':valor_pesos_sin_iva' => !empty($datos['valor_pesos_sin_iva']) ? (float)str_replace(['$', ',', ' '], '', $datos['valor_pesos_sin_iva']) : null,
            ':valor_dolares' => !empty($datos['valor_dolares']) ? (float)str_replace(['$', ',', ' '], '', $datos['valor_dolares']) : null,
            ':descripcion' => !empty($datos['descripcion']) ? cleanInput($datos['descripcion']) : null,
            ':categoria' => !empty($datos['categoria']) ? cleanInput($datos['categoria']) : null,
            ':valor_mensual' => !empty($datos['valor_mensual']) ? (float)str_replace(['$', ',', ' '], '', $datos['valor_mensual']) : null,
            ':observaciones' => !empty($datos['observaciones']) ? cleanInput($datos['observaciones']) : null,
            ':fecha_inicio' => !empty($datos['fecha_inicio']) ? convertirFecha($datos['fecha_inicio']) : null,
            ':fecha_vencimiento' => !empty($datos['fecha_vencimiento']) ? convertirFecha($datos['fecha_vencimiento']) : null,
            ':valor_facturado' => !empty($datos['valor_facturado']) ? (float)str_replace(['$', ',', ' '], '', $datos['valor_facturado']) : null,
            ':porcentaje_ejecucion' => !empty($datos['porcentaje_ejecucion']) ? (float)str_replace(['%', ' '], '', $datos['porcentaje_ejecucion']) : null,
            ':valor_pendiente_ejecutar' => !empty($datos['valor_pendiente_ejecutar']) ? (float)str_replace(['$', ',', ' '], '', $datos['valor_pendiente_ejecutar']) : null,
            ':estado' => !empty($datos['estado']) ? cleanInput($datos['estado']) : null,
            ':no_horas' => !empty($datos['no_horas']) ? (int)$datos['no_horas'] : null,
            ':factura_no' => !empty($datos['factura_no']) ? cleanInput($datos['factura_no']) : null,
            ':no_poliza' => !empty($datos['no_poliza']) ? cleanInput($datos['no_poliza']) : null,
            ':fecha_vencimiento_poliza' => !empty($datos['fecha_vencimiento_poliza']) ? convertirFecha($datos['fecha_vencimiento_poliza']) : null,
            ':contrato_id' => $contrato_id,
            ':usuario_id' => $usuario_id
        ];
        
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
        
    } catch (Exception $e) {
        throw $e;
    }
}

// Función para eliminar un contrato
function eliminarContrato($contrato_id, $usuario_id) {
    global $pdo;
    
    try {
        $sql = "DELETE FROM contratos WHERE id = :contrato_id AND usuario_id = :usuario_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':contrato_id', $contrato_id, PDO::PARAM_INT);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
        
    } catch (Exception $e) {
        throw $e;
    }
}

// Función para obtener lista de clientes únicos
function obtenerClientesUnicos($usuario_id) {
    global $pdo;
    
    try {
        $sql = "SELECT DISTINCT cliente FROM contratos WHERE usuario_id = :usuario_id AND cliente IS NOT NULL AND cliente != '' ORDER BY cliente ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $clientes = [];
        while ($row = $stmt->fetch()) {
            $clientes[] = $row['cliente'];
        }
        
        return $clientes;
        
    } catch (Exception $e) {
        throw $e;
    }
}
?>
