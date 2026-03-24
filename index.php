<?php
/**
 * Página principal – Listado de asistencia
 * Muestra todos los registros con opciones de búsqueda, filtro
 * y estadísticas rápidas. También permite editar y eliminar registros.
 */

require_once 'config/database.php';

// ── Configuración de paginación ──────────────────────────────
$registrosPorPagina = 15;
$paginaActual       = max(1, (int)($_GET['pagina'] ?? 1));
$offset             = ($paginaActual - 1) * $registrosPorPagina;

// ── Parámetros de búsqueda / filtro ─────────────────────────
$busqueda    = trim($_GET['busqueda'] ?? '');
$filtroCulto = $_GET['culto']        ?? '';
$filtroLinea = $_GET['linea']        ?? '';
$filtroFecha = $_GET['fecha']        ?? '';

// ── Mensaje de retroalimentación desde otras páginas ─────────
$mensaje     = $_GET['msg']  ?? '';
$tipoMensaje = $_GET['tipo'] ?? 'exito';

try {
    $pdo = obtenerConexion();

    // ── Construir cláusula WHERE dinámica ────────────────────
    $condiciones = [];
    $parametros  = [];

    if ($busqueda !== '') {
        // Buscar en nombre, apellido o líder de célula
        $condiciones[] = '(nombre LIKE :busqueda OR apellido LIKE :busqueda OR lider_celula LIKE :busqueda)';
        $parametros[':busqueda'] = '%' . $busqueda . '%';
    }
    if ($filtroCulto !== '') {
        $condiciones[] = 'culto = :culto';
        $parametros[':culto'] = $filtroCulto;
    }
    if ($filtroLinea !== '') {
        $condiciones[] = 'linea LIKE :linea';
        $parametros[':linea'] = '%' . $filtroLinea . '%';
    }
    if ($filtroFecha !== '') {
        $condiciones[] = 'fecha = :fecha';
        $parametros[':fecha'] = $filtroFecha;
    }

    $clausulaWhere = $condiciones
        ? 'WHERE ' . implode(' AND ', $condiciones)
        : '';

    // ── Contar total de registros para paginación ────────────
    $sqlConteo = "SELECT COUNT(*) FROM asistencia $clausulaWhere";
    $stmtConteo = $pdo->prepare($sqlConteo);
    $stmtConteo->execute($parametros);
    $totalRegistros  = (int)$stmtConteo->fetchColumn();
    $totalPaginas    = max(1, (int)ceil($totalRegistros / $registrosPorPagina));
    $paginaActual    = min($paginaActual, $totalPaginas);
    $offset          = ($paginaActual - 1) * $registrosPorPagina;

    // ── Obtener registros de la página actual ────────────────
    $sql = "SELECT * FROM asistencia $clausulaWhere
            ORDER BY fecha DESC, id DESC
            LIMIT :limite OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($parametros as $clave => $valor) {
        $stmt->bindValue($clave, $valor);
    }
    $stmt->bindValue(':limite', $registrosPorPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $registros = $stmt->fetchAll();

    // ── Estadísticas rápidas ─────────────────────────────────
    $statsSQL = "SELECT
                    COUNT(*) AS total,
                    SUM(devocional = 'Sí') AS con_devocional,
                    SUM(culto = 'AM') AS culto_am,
                    SUM(culto = 'PM') AS culto_pm
                 FROM asistencia";
    $stats = $pdo->query($statsSQL)->fetch();

    // ── Obtener líneas únicas para el filtro ─────────────────
    $lineas = $pdo->query("SELECT DISTINCT linea FROM asistencia ORDER BY linea")->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    // Registrar el error real en el log del servidor (no exponer detalles al usuario)
    error_log('Error de base de datos en index.php: ' . $e->getMessage());
    die('<p style="color:red;padding:2rem">No se pudo conectar a la base de datos. Por favor, contacte al administrador del sistema.</p>');
}

/**
 * Genera la clase CSS del badge según el color del equipo.
 *
 * @param string $color  Nombre del color.
 * @return string        Clase CSS correspondiente.
 */
function claseBadgeEquipo(string $color): string {
    $mapa = [
        'rojo'     => 'badge-equipo--rojo',
        'azul'     => 'badge-equipo--azul',
        'verde'    => 'badge-equipo--verde',
        'amarillo' => 'badge-equipo--amarillo',
        'naranja'  => 'badge-equipo--naranja',
        'morado'   => 'badge-equipo--morado',
        'blanco'   => 'badge-equipo--blanco',
        'negro'    => 'badge-equipo--negro',
    ];
    return $mapa[strtolower($color)] ?? 'badge-equipo--default';
}

$tituloPagina = 'Listado de Asistencia';
require_once 'includes/header.php';
?>

<?php if ($mensaje !== ''): ?>
    <!-- Mensaje de retroalimentación -->
    <div class="alerta alerta--<?= $tipoMensaje === 'error' ? 'error' : 'exito' ?>">
        <?= htmlspecialchars($mensaje) ?>
    </div>
<?php endif; ?>

<!-- ── Estadísticas rápidas ───────────────────────────────── -->
<div class="estadisticas">
    <div class="stat-tarjeta">
        <span class="stat-tarjeta__icono">📊</span>
        <div class="stat-tarjeta__info">
            <p>Total de registros</p>
            <strong><?= number_format((int)$stats['total']) ?></strong>
        </div>
    </div>
    <div class="stat-tarjeta">
        <span class="stat-tarjeta__icono">📖</span>
        <div class="stat-tarjeta__info">
            <p>Con devocional</p>
            <strong><?= number_format((int)$stats['con_devocional']) ?></strong>
        </div>
    </div>
    <div class="stat-tarjeta">
        <span class="stat-tarjeta__icono">🌅</span>
        <div class="stat-tarjeta__info">
            <p>Culto AM</p>
            <strong><?= number_format((int)$stats['culto_am']) ?></strong>
        </div>
    </div>
    <div class="stat-tarjeta">
        <span class="stat-tarjeta__icono">🌙</span>
        <div class="stat-tarjeta__info">
            <p>Culto PM</p>
            <strong><?= number_format((int)$stats['culto_pm']) ?></strong>
        </div>
    </div>
</div>

<!-- ── Tabla de registros ─────────────────────────────────── -->
<div class="tarjeta">
    <div class="tarjeta__encabezado">
        <h2 class="tarjeta__titulo">📋 Registros de Asistencia</h2>
        <a href="registro.php" class="btn btn--exito">➕ Nuevo Registro</a>
    </div>

    <!-- Formulario de búsqueda y filtros -->
    <form method="get" action="index.php">
        <div class="barra-herramientas">
            <div class="buscador">
                <input
                    type="text"
                    name="busqueda"
                    placeholder="Buscar por nombre, apellido o líder…"
                    value="<?= htmlspecialchars($busqueda) ?>"
                    aria-label="Buscar registros"
                >
                <select name="culto" aria-label="Filtrar por culto">
                    <option value="">Todos los cultos</option>
                    <option value="AM" <?= $filtroCulto === 'AM' ? 'selected' : '' ?>>AM</option>
                    <option value="PM" <?= $filtroCulto === 'PM' ? 'selected' : '' ?>>PM</option>
                </select>
                <select name="linea" aria-label="Filtrar por línea">
                    <option value="">Todas las líneas</option>
                    <?php foreach ($lineas as $linea): ?>
                        <option value="<?= htmlspecialchars($linea) ?>"
                            <?= $filtroLinea === $linea ? 'selected' : '' ?>>
                            <?= htmlspecialchars($linea) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input
                    type="date"
                    name="fecha"
                    value="<?= htmlspecialchars($filtroFecha) ?>"
                    aria-label="Filtrar por fecha"
                >
                <button type="submit" class="btn btn--primario">🔍 Filtrar</button>
                <?php if ($busqueda || $filtroCulto || $filtroLinea || $filtroFecha): ?>
                    <a href="index.php" class="btn btn--secundario">✖ Limpiar</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <?php if (empty($registros)): ?>
        <!-- Sin resultados -->
        <div class="alerta alerta--advertencia">
            No se encontraron registros con los criterios de búsqueda indicados.
        </div>
    <?php else: ?>
        <!-- Tabla de datos -->
        <div class="tabla-contenedor">
            <table class="tabla" aria-label="Registros de asistencia">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Nombre y Apellido</th>
                        <th>Devocional</th>
                        <th>Convocado para</th>
                        <th>Color Equipo</th>
                        <th>Culto</th>
                        <th>Líder de Célula</th>
                        <th>Línea</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $i => $reg): ?>
                        <tr>
                            <td><?= $offset + $i + 1 ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($reg['fecha']))) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($reg['nombre'] . ' ' . $reg['apellido']) ?></strong>
                            </td>
                            <td>
                                <span class="badge badge--<?= $reg['devocional'] === 'Sí' ? 'si' : 'no' ?>">
                                    <?= htmlspecialchars($reg['devocional']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $convClase = match($reg['convocado']) {
                                    'Consolidar' => 'consolidar',
                                    'Ministrar'  => 'ministrar',
                                    default      => 'ambos'
                                };
                                ?>
                                <span class="badge badge--<?= $convClase ?>">
                                    <?= htmlspecialchars($reg['convocado']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-equipo <?= claseBadgeEquipo($reg['color_equipo']) ?>">
                                    <?= htmlspecialchars($reg['color_equipo']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge--<?= strtolower($reg['culto']) ?>">
                                    <?= htmlspecialchars($reg['culto']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($reg['lider_celula']) ?></td>
                            <td><?= htmlspecialchars($reg['linea']) ?></td>
                            <td>
                                <div class="tabla__acciones">
                                    <a href="editar.php?id=<?= (int)$reg['id'] ?>"
                                       class="btn btn--primario btn--sm"
                                       title="Editar registro">✏️</a>
                                    <a href="eliminar.php?id=<?= (int)$reg['id'] ?>"
                                       class="btn btn--peligro btn--sm"
                                       title="Eliminar registro"
                                       onclick="return confirm('¿Confirma que desea eliminar este registro?')">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
            <nav class="paginacion" aria-label="Paginación de resultados">
                <?php
                // Construir parámetros de URL preservando filtros activos
                $params = array_filter([
                    'busqueda' => $busqueda,
                    'culto'    => $filtroCulto,
                    'linea'    => $filtroLinea,
                    'fecha'    => $filtroFecha,
                ]);

                // Botón "Anterior"
                if ($paginaActual > 1):
                    $paramsAnterior = array_merge($params, ['pagina' => $paginaActual - 1]);
                ?>
                    <a href="?<?= http_build_query($paramsAnterior) ?>">&laquo; Anterior</a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                    <?php if ($p === $paginaActual): ?>
                        <span class="activo"><?= $p ?></span>
                    <?php else: ?>
                        <a href="?<?= http_build_query(array_merge($params, ['pagina' => $p])) ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($paginaActual < $totalPaginas):
                    $paramsSiguiente = array_merge($params, ['pagina' => $paginaActual + 1]);
                ?>
                    <a href="?<?= http_build_query($paramsSiguiente) ?>">Siguiente &raquo;</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

        <p style="font-size:0.82rem;color:#7f8c8d;margin-top:0.75rem">
            Mostrando <?= count($registros) ?> de <?= $totalRegistros ?> registro(s).
        </p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
