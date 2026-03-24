<?php
// Existing validations and redirect behavior

// Database connection
try {
    $conn->beginTransaction(); // Start a transaction

    // Check if numero_documento exists in personas
    $stmt = $conn->prepare('SELECT COUNT(*) FROM personas WHERE numero_documento = :numero_documento');
    $stmt->execute([':numero_documento' => $numero_documento]);
    $exists = $stmt->fetchColumn();

    // If it does not exist, insert into personas
    if ($exists == 0) {
        $stmt = $conn->prepare('INSERT INTO personas (nombre, numero_documento) VALUES (:nombre, :numero_documento)');
        $stmt->execute([':nombre' => $nombre, ':numero_documento' => $numero_documento]);
    }

    // Then insert into asistencia
    $stmt = $conn->prepare('INSERT INTO asistencia (campo1, campo2, ...) VALUES (:valor1, :valor2, ...)');
    $stmt->execute([':valor1' => $valor1, ':valor2' => $valor2]);

    // Commit the transaction
    $conn->commit();
} catch (Exception $e) {
    // Rollback the transaction in case of an exception
    $conn->rollBack();
    // Handle exception (log it, show an error message, etc.)
}
?>