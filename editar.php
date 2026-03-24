<?php
/**
 * Página de edición de registro de asistencia
 * Permite modificar un registro existente identificado por su ID.
 */

require_once 'config/database.php';

// ── Validar que se recibió un ID válido ──────────────────────
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header('Location: index.php?msg=' . urlencode('ID de registro no válido.') . '&tipo=error');
    exit;
}

$errores = [];
$datos   = [];

try {
    $pdo = obtenerConexion();

    // ── Procesar formulario cuando se envía por POST ─────────
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Leer y sanitizar campos
        $datos = [
            'fecha'        => trim($_POST['fecha']        ?? ''),
            'devocional'   => trim($_POST['devocional']   ?? ''),
            'convocado'    => trim($_POST['convocado']    ?? ''),
            'color_equipo' => trim($_POST['color_equipo'] ?? ''),
            'culto'        => trim($_POST['culto']        ?? ''),
            'nombre'       => trim($_POST['nombre']       ?? ''),
            'apellido'     => trim($_POST['apellido']     ?? ''),
            'lider_celula' => trim($_POST['lider_celula'] ?? ''),
            'linea'        => trim($_POST['linea']        ?? ''),
        ];

        // ── Validaciones ─────────────────────────────────────
        if ($datos['fecha'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha'])) {
            $errores['fecha'] = 'La fecha es obligatoria y debe tener formato válido.';
        }
        if (!in_array($datos['devocional'], ['Sí', 'No'], true)) {
            $errores['devocional'] = 'Debe indicar si participó del devocional.';
        }
        if (!in_array($datos['convocado'], ['Consolidar', 'Ministrar', 'Ambos'], true)) {
            $errores['convocado'] = 'Debe seleccionar el propósito de la convocatoria.';
        }
        if ($datos['color_equipo'] === '') {
            $errores['color_equipo'] = 'El color del equipo es obligatorio.';
        }
        if (!in_array($datos['culto'], ['AM', 'PM'], true)) {
            $errores['culto'] = 'Debe seleccionar el turno del culto (AM o PM).';
        }
        if ($datos['nombre'] === '') {
            $errores['nombre'] = 'El nombre es obligatorio.';
        }
        if ($datos['apellido'] === '') {
            $errores['apellido'] = 'El apellido es obligatorio.';
        }
        if ($datos['lider_celula'] === '') {
            $errores['lider_celula'] = 'El líder de célula es obligatorio.';
        }
        if ($datos['linea'] === '') {
            $errores['linea'] = 'La línea es obligatoria.';
        }

        // ── Actualizar si no hay errores ──────────────────────
        if (empty($errores)) {
            $sql = "UPDATE asistencia SET
                        fecha        = :fecha,
                        devocional   = :devocional,
                        convocado    = :convocado,
                        color_equipo = :color_equipo,
                        culto        = :culto,
                        nombre       = :nombre,
                        apellido     = :apellido,
                        lider_celula = :lider_celula,
                        linea        = :linea
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge($datos, [':id' => $id]));

            header('Location: index.php?msg=' . urlencode('Registro actualizado exitosamente.') . '&tipo=exito');
            exit;
        }

    } else {
        // ── Cargar datos actuales del registro ────────────────
        $stmt = $pdo->prepare('SELECT * FROM asistencia WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $datos = $stmt->fetch();

        if (!$datos) {
            header('Location: index.php?msg=' . urlencode('Registro no encontrado.') . '&tipo=error');
            exit;
        }
    }

} catch (PDOException $e) {
    // Registrar el error real en el log del servidor
    error_log('Error de base de datos en editar.php: ' . $e->getMessage());
    die('<p style="color:red;padding:2rem">No se pudo procesar la solicitud. Por favor, contacte al administrador del sistema.</p>');
}

// ── Colores de equipo disponibles ────────────────────────────
$coloresEquipo = ['Verde', 'Amarillo', 'Azul', 'Naranja'];

$tituloPagina = 'Editar Registro';
require_once 'includes/header.php';
?>

<div class="tarjeta">
    <div class="tarjeta__encabezado">
        <h2 class="tarjeta__titulo">✏️ Editar Registro #<?= (int)$id ?></h2>
        <a href="index.php" class="btn btn--secundario">← Volver al listado</a>
    </div>

    <?php if (!empty($errores['bd'])): ?>
        <div class="alerta alerta--error"><?= htmlspecialchars($errores['bd']) ?></div>
    <?php endif; ?>

    <!-- Formulario de edición -->
    <form method="post" action="editar.php?id=<?= (int)$id ?>" novalidate>

        <div class="formulario__grid">

            <!-- Fecha -->
            <div class="formulario__grupo">
                <label for="fecha">📅 Fecha *</label>
                <input
                    type="date"
                    id="fecha"
                    name="fecha"
                    value="<?= htmlspecialchars($datos['fecha']) ?>"
                    required
                >
                <?php if (!empty($errores['fecha'])): ?>
                    <span style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['fecha']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Devocional -->
            <div class="formulario__grupo">
                <label for="devocional">📖 ¿Participó del devocional? *</label>
                <select id="devocional" name="devocional" required>
                    <option value="">— Seleccione —</option>
                    <option value="Sí"  <?= $datos['devocional'] === 'Sí'  ? 'selected' : '' ?>>Sí</option>
                    <option value="No"  <?= $datos['devocional'] === 'No'  ? 'selected' : '' ?>>No</option>
                </select>
                <?php if (!empty($errores['devocional'])): ?>
                    <span style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['devocional']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Convocado para -->
            <div class="formulario__grupo">
                <label for="convocado">🎯 Convocado para *</label>
                <select id="convocado" name="convocado" required>
                    <option value="">— Seleccione —</option>
                    <option value="Consolidar" <?= $datos['convocado'] === 'Consolidar' ? 'selected' : '' ?>>Consolidar</option>
                    <option value="Ministrar"  <?= $datos['convocado'] === 'Ministrar'  ? 'selected' : '' ?>>Ministrar</option>
                    <option value="Ambos"      <?= $datos['convocado'] === 'Ambos'      ? 'selected' : '' ?>>Ambos</option>
                </select>
                <?php if (!empty($errores['convocado'])): ?>
                    <span style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['convocado']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Color del equipo -->
            <div class="formulario__grupo">
                <label for="color_equipo">🎨 Color del equipo *</label>
                <select id="color_equipo" name="color_equipo" required>
                    <option value="">— Seleccione —</option>
                    <?php foreach ($coloresEquipo as $color): ?>
                        <option value="<?= htmlspecialchars($color) ?>"
                            <?= $datos['color_equipo'] === $color ? 'selected' : '' ?>>
                            <?= htmlspecialchars($color) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errores['color_equipo'])): ?>
                    <span style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['color_equipo']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Culto -->
            <div class="formulario__grupo">
                <label for="culto">⛪ Culto *</label>
                <select id="culto" name="culto" required>
                    <option value="">— Seleccione —</option>
                    <option value="AM" <?= $datos['culto'] === 'AM' ? 'selected' : '' ?>>AM (Mañana)</option>
                    <option value="PM" <?= $datos['culto'] === 'PM' ? 'selected' : '' ?>>PM (Tarde)</option>
                </select>
                <?php if (!empty($errores['culto'])): ?>
                    <span style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['culto']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Nombre -->
            <div class="formulario__grupo">
                <label for="nombre">👤 Nombre *</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="<?= htmlspecialchars($datos['nombre']) ?>"
                    maxlength="100"
                    required
                >
                <?php if (!empty($errores['nombre'])): ?>
                    <span style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['nombre']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Apellido -->
            <div class="formulario__grupo">
                <label for="apellido">👤 Apellido *</label>
                <input
                    type="text"
                    id="apellido"
                    name="apellido"
                    value="<?= htmlspecialchars($datos['apellido']) ?>"
                    maxlength="100"
                    required
                >
                <?php if (!empty($errores['apellido'])): ?>
                    <span style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['apellido']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Líder de Célula -->
            <div class="formulario__grupo">
                <label for="lider_celula">🏠 Líder de Célula *</label>
                <input
                    type="text"
                    id="lider_celula"
                    name="lider_celula"
                    value="<?= htmlspecialchars($datos['lider_celula']) ?>"
                    maxlength="150"
                    required
                >
                <?php if (!empty($errores['lider_celula'])): ?>
                    <span style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['lider_celula']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Línea -->
            <div class="formulario__grupo">
                <label for="linea">🔗 Línea a la que pertenece *</label>
                <input
                    type="text"
                    id="linea"
                    name="linea"
                    value="<?= htmlspecialchars($datos['linea']) ?>"
                    maxlength="150"
                    required
                >
                <?php if (!empty($errores['linea'])): ?>
                    <span style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['linea']) ?></span>
                <?php endif; ?>
            </div>

        </div><!-- /formulario__grid -->

        <div class="formulario__acciones">
            <button type="submit" class="btn btn--primario">💾 Actualizar Registro</button>
            <a href="index.php" class="btn btn--secundario">✖ Cancelar</a>
        </div>

    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
