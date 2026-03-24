<?php
/**
 * Archivo de configuración de la base de datos
 * Ministerio Consolidación & Ministración
 *
 * Modifique las constantes según su entorno de instalación.
 */

// ── Parámetros de conexión ──────────────────────────────────
define('DB_HOST',     'localhost');   // Servidor de base de datos
define('DB_PORT',     '3306');        // Puerto MySQL (por defecto 3306)
define('DB_NAME',     'consolidacion_cfa'); // Nombre de la base de datos
define('DB_USER',     'root');        // Usuario de MySQL
define('DB_PASSWORD', '');            // Contraseña de MySQL
define('DB_CHARSET',  'utf8mb4');     // Juego de caracteres

/**
 * Crea y retorna una conexión PDO a la base de datos.
 *
 * @return PDO  Objeto de conexión listo para usar.
 * @throws PDOException Si la conexión falla.
 */
function obtenerConexion(): PDO {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
    );

    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanzar excepciones en errores
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retornar arrays asociativos
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Usar consultas preparadas nativas
    ];

    return new PDO($dsn, DB_USER, DB_PASSWORD, $opciones);
}
