<?php
/**
 * Endpoint JSON para buscar una persona por número de documento.
 * Uso: GET api/buscar_persona.php?documento=<numero>
 *
 * Respuesta exitosa:  { "found": true,  "persona": { ...campos... } }
 * Respuesta negativa: { "found": false }
 */

require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

// Solo aceptar GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}

$documento = trim($_GET['documento'] ?? '');

if ($documento === '') {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro "documento" es obligatorio.']);
    exit;
}

try {
    $pdo  = obtenerConexion();
    $stmt = $pdo->prepare(
        'SELECT numero_documento, nombre, apellido, color_equipo, lider_celula, linea
         FROM personas
         WHERE numero_documento = :documento
         LIMIT 1'
    );
    $stmt->execute([':documento' => $documento]);
    $persona = $stmt->fetch();

    if ($persona) {
        echo json_encode(['found' => true, 'persona' => $persona]);
    } else {
        echo json_encode(['found' => false]);
    }
} catch (PDOException $e) {
    error_log('Error en api/buscar_persona.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor.']);
}
