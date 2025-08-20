<?php
require_once '../includes/functions.php';

// Verificar si está logueado
if (!isLoggedIn()) {
    redirect('../index.php');
}

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('mis-contratos.php');
}

// Obtener ID del contrato
$contrato_id = isset($_POST['contrato_id']) ? (int)$_POST['contrato_id'] : 0;

if ($contrato_id <= 0) {
    redirect('mis-contratos.php');
}

try {
    // Intentar eliminar el contrato
    if (eliminarContrato($contrato_id, $_SESSION['user_id'])) {
        $_SESSION['success_message'] = '✅ Contrato eliminado exitosamente.';
    } else {
        $_SESSION['error_message'] = 'No se pudo eliminar el contrato.';
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Error al eliminar el contrato: ' . $e->getMessage();
}

// Redirigir de vuelta a mis contratos
redirect('mis-contratos.php');
?>
