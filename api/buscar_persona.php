<?php
/**
 * Endpoint de búsqueda de persona por número de documento.
 * Responde JSON: { "encontrada": true, "datos": {...} }
 *                { "encontrada": false }
 *                { "error": "mensaje" }  (solo para peticiones inválidas)
 *
 * Solo acepta peticiones GET.
 * No expone detalles internos de base de datos al cliente.
 */

header('Content-Type: application/json; charset=utf-8');

// ── Solo GET ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}

// ── Obtener y validar el parámetro ───────────────────────────
$doc = trim($_GET['doc'] ?? '');

if ($doc === '') {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro doc es obligatorio.']);
    exit;
}

// Solo dígitos, máximo 20 caracteres
if (!preg_match('/^\d{1,20}$/', $doc)) {
    http_response_code(400);
    echo json_encode(['error' => 'El número de documento debe contener solo dígitos (máximo 20).']);
    exit;
}

// ── Consultar la base de datos ───────────────────────────────
require_once __DIR__ . '/../config/database.php';

try {
    $pdo  = obtenerConexion();
    $stmt = $pdo->prepare(
        'SELECT numero_documento, nombre, apellido, color_equipo, lider_celula, linea
         FROM personas
         WHERE numero_documento = :doc
         LIMIT 1'
    );
    $stmt->execute([':doc' => $doc]);
    $persona = $stmt->fetch();

    if ($persona) {
        echo json_encode([
            'encontrada' => true,
            'datos'      => [
                'nombre'       => $persona['nombre'],
                'apellido'     => $persona['apellido'],
                'color_equipo' => $persona['color_equipo'],
                'lider_celula' => $persona['lider_celula'],
                'linea'        => $persona['linea'],
            ],
        ]);
    } else {
        echo json_encode(['encontrada' => false]);
    }

} catch (PDOException $e) {
    error_log('Error en api/buscar_persona.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno. Por favor, intente de nuevo.']);
}
