<?php
/**
 * Script de eliminación de registro
 * Recibe el ID por GET, elimina el registro y redirige al listado.
 * No muestra ninguna vista HTML propia (solo redirige).
 */

require_once 'config/database.php';

// ── Validar que se recibió un ID entero válido ───────────────
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header('Location: index.php?msg=' . urlencode('ID de registro no válido.') . '&tipo=error');
    exit;
}

try {
    $pdo = obtenerConexion();

    // Verificar que el registro existe antes de eliminarlo
    $stmtVerificar = $pdo->prepare('SELECT id FROM asistencia WHERE id = :id');
    $stmtVerificar->execute([':id' => $id]);

    if (!$stmtVerificar->fetch()) {
        // El registro no existe
        header('Location: index.php?msg=' . urlencode('El registro no fue encontrado.') . '&tipo=error');
        exit;
    }

    // Eliminar el registro
    $stmtEliminar = $pdo->prepare('DELETE FROM asistencia WHERE id = :id');
    $stmtEliminar->execute([':id' => $id]);

    // Redirigir con mensaje de éxito
    header('Location: index.php?msg=' . urlencode('Registro eliminado correctamente.') . '&tipo=exito');
    exit;

} catch (PDOException $e) {
    // Registrar el error real en el log del servidor (no exponer detalles al usuario)
    error_log('Error de base de datos en eliminar.php: ' . $e->getMessage());
    header('Location: index.php?msg=' . urlencode('No se pudo eliminar el registro. Por favor, contacte al administrador.') . '&tipo=error');
    exit;
}
