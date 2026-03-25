<?php
/**
 * Página de registro de nueva asistencia
 * Permite agregar un nuevo registro con todos los campos
 * requeridos por el departamento de Consolidación y Célula.
 */

require_once 'config/database.php';

// ── Valores por defecto del formulario ──────────────────────
$errores = [];
$datos = [
    'numero_documento' => '',
    'fecha'        => date('Y-m-d'),   // Fecha de hoy por defecto
    'devocional'   => '',
    'convocado'    => '',
    'color_equipo' => '',
    'culto'        => '',
    'nombre'       => '',
    'apellido'     => '',
    'lider_celula' => '',
    'linea'        => '',
];

// ── Procesar formulario cuando se envía por POST ─────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Leer y sanitizar cada campo
    $datos['numero_documento'] = trim($_POST['numero_documento'] ?? '');
    $datos['fecha']        = trim($_POST['fecha']        ?? '');
    $datos['devocional']   = trim($_POST['devocional']   ?? '');
    $datos['convocado']    = trim($_POST['convocado']    ?? '');
    $datos['color_equipo'] = trim($_POST['color_equipo'] ?? '');
    $datos['culto']        = trim($_POST['culto']        ?? '');
    $datos['nombre']       = trim($_POST['nombre']       ?? '');
    $datos['apellido']     = trim($_POST['apellido']     ?? '');
    $datos['lider_celula'] = trim($_POST['lider_celula'] ?? '');
    $datos['linea']        = trim($_POST['linea']        ?? '');

    // ── Validaciones ─────────────────────────────────────────
    if ($datos['numero_documento'] === '' || !preg_match('/^\d{1,20}$/', $datos['numero_documento'])) {
        $errores['numero_documento'] = 'El número de documento es obligatorio y debe contener solo dígitos.';
    }
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

    // ── Guardar si no hay errores ─────────────────────────────
    if (empty($errores)) {
        try {
            $pdo = obtenerConexion();

            // ── Insertar en asistencia ────────────────────────────────
            $sqlAsistencia = "INSERT INTO asistencia
                        (numero_documento, fecha, devocional, convocado, color_equipo, culto,
                         nombre, apellido, lider_celula, linea)
                    VALUES
                        (:numero_documento, :fecha, :devocional, :convocado, :color_equipo, :culto,
                         :nombre, :apellido, :lider_celula, :linea)";

            $stmt = $pdo->prepare($sqlAsistencia);
            $stmt->execute([
                ':numero_documento' => $datos['numero_documento'],
                ':fecha'        => $datos['fecha'],
                ':devocional'   => $datos['devocional'],
                ':convocado'    => $datos['convocado'],
                ':color_equipo' => $datos['color_equipo'],
                ':culto'        => $datos['culto'],
                ':nombre'       => $datos['nombre'],
                ':apellido'     => $datos['apellido'],
                ':lider_celula' => $datos['lider_celula'],
                ':linea'        => $datos['linea'],
            ]);

            // ── Upsert en personas (crear o actualizar catálogo) ─────────
            $sqlPersona = "INSERT INTO personas
                        (numero_documento, nombre, apellido, color_equipo, lider_celula, linea)
                    VALUES
                        (:numero_documento, :nombre, :apellido, :color_equipo, :lider_celula, :linea)
                    ON DUPLICATE KEY UPDATE
                        nombre       = VALUES(nombre),
                        apellido     = VALUES(apellido),
                        color_equipo = VALUES(color_equipo),
                        lider_celula = VALUES(lider_celula),
                        linea        = VALUES(linea)";

            $stmtPersona = $pdo->prepare($sqlPersona);
            $stmtPersona->execute([
                ':numero_documento' => $datos['numero_documento'],
                ':nombre'       => $datos['nombre'],
                ':apellido'     => $datos['apellido'],
                ':color_equipo' => $datos['color_equipo'],
                ':lider_celula' => $datos['lider_celula'],
                ':linea'        => $datos['linea'],
            ]);

            // Redirigir al listado con mensaje de éxito
            header('Location: index.php?msg=' . urlencode('Registro guardado exitosamente.') . '&tipo=exito');
            exit;

        } catch (PDOException $e) {
            // Registrar el error real en el log del servidor
            error_log('Error al guardar registro en registro.php: ' . $e->getMessage());
            $errores['bd'] = 'Ocurrió un error al guardar el registro. Por favor, intente de nuevo o contacte al administrador.';
        }
    }
}

// ── Colores de equipo disponibles ────────────────────────────
$coloresEquipo = ['Verde', 'Amarillo', 'Azul', 'Naranja'];

$tituloPagina = 'Nuevo Registro';
require_once 'includes/header.php';
?>

<div class="tarjeta">
    <div class="tarjeta__encabezado">
        <h2 class="tarjeta__titulo">➕ Registrar Asistencia</h2>
        <a href="index.php" class="btn btn--secundario">← Volver al listado</a>
    </div>

    <?php if (!empty($errores['bd'])): ?>
        <div class="alerta alerta--error"><?= htmlspecialchars($errores['bd']) ?></div>
    <?php endif; ?>

    <!-- Formulario de registro -->
    <form method="post" action="registro.php" novalidate>

        <div class="formulario__grid">

            <!-- Número de documento -->
            <div class="formulario__grupo formulario__grupo--documento">
                <label for="numero_documento">🪺 Número de documento *</label>
                <input
                    type="text"
                    id="numero_documento"
                    name="numero_documento"
                    value="<?= htmlspecialchars($datos['numero_documento']) ?>"
                    placeholder="Ingrese el número de documento"
                    maxlength="20"
                    inputmode="numeric"
                    pattern="[0-9]+"
                    required
                    autocomplete="off"
                    aria-describedby="error-numero_documento doc-estado"
                >
                <span id="doc-estado" class="doc-estado" aria-live="polite"></span>
                <?php if (!empty($errores['numero_documento'])): ?>
                    <span id="error-numero_documento" style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['numero_documento']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Fecha -->
            <div class="formulario__grupo">
                <label for="fecha">📅 Fecha *</label>
                <input
                    type="date"
                    id="fecha"
                    name="fecha"
                    value="<?= htmlspecialchars($datos['fecha']) ?>"
                    required
                    aria-describedby="error-fecha"
                >
                <?php if (!empty($errores['fecha'])): ?>
                    <span id="error-fecha" style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['fecha']) ?></span>
                <?php endif; ?>
            </div>

            <!-- ¿Participó del devocional? -->
            <div class="formulario__grupo">
                <label for="devocional">📖 ¿Participó del devocional? *</label>
                <select id="devocional" name="devocional" required aria-describedby="error-devocional">
                    <option value="">— Seleccione —</option>
                    <option value="Sí"  <?= $datos['devocional'] === 'Sí'  ? 'selected' : '' ?>>Sí</option>
                    <option value="No"  <?= $datos['devocional'] === 'No'  ? 'selected' : '' ?>>No</option>
                </select>
                <?php if (!empty($errores['devocional'])): ?>
                    <span id="error-devocional" style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['devocional']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Convocado para -->
            <div class="formulario__grupo">
                <label for="convocado">🎯 Convocado para *</label>
                <select id="convocado" name="convocado" required aria-describedby="error-convocado">
                    <option value="">— Seleccione —</option>
                    <option value="Consolidar" <?= $datos['convocado'] === 'Consolidar' ? 'selected' : '' ?>>Consolidar</option>
                    <option value="Ministrar"  <?= $datos['convocado'] === 'Ministrar'  ? 'selected' : '' ?>>Ministrar</option>
                    <option value="Ambos"      <?= $datos['convocado'] === 'Ambos'      ? 'selected' : '' ?>>Ambos</option>
                </select>
                <?php if (!empty($errores['convocado'])): ?>
                    <span id="error-convocado" style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['convocado']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Color del equipo -->
            <div class="formulario__grupo">
                <label for="color_equipo">🎨 Color del equipo *</label>
                <select id="color_equipo" name="color_equipo" required aria-describedby="error-color_equipo" data-autofill="true">
                    <option value="">— Seleccione —</option>
                    <?php foreach ($coloresEquipo as $color): ?>
                        <option value="<?= htmlspecialchars($color) ?>"
                            <?= $datos['color_equipo'] === $color ? 'selected' : '' ?>>
                            <?= htmlspecialchars($color) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errores['color_equipo'])): ?>
                    <span id="error-color_equipo" style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['color_equipo']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Culto AM / PM -->
            <div class="formulario__grupo">
                <label for="culto">⛪ Culto *</label>
                <select id="culto" name="culto" required aria-describedby="error-culto">
                    <option value="">— Seleccione —</option>
                    <option value="AM" <?= $datos['culto'] === 'AM' ? 'selected' : '' ?>>AM (Mañana)</option>
                    <option value="PM" <?= $datos['culto'] === 'PM' ? 'selected' : '' ?>>PM (Tarde)</option>
                </select>
                <?php if (!empty($errores['culto'])): ?>
                    <span id="error-culto" style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['culto']) ?></span>
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
                    placeholder="Ingrese el nombre"
                    maxlength="100"
                    required
                    data-autofill="true"
                    aria-describedby="error-nombre"
                >
                <?php if (!empty($errores['nombre'])): ?>
                    <span id="error-nombre" style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['nombre']) ?></span>
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
                    placeholder="Ingrese el apellido"
                    maxlength="100"
                    required
                    data-autofill="true"
                    aria-describedby="error-apellido"
                >
                <?php if (!empty($errores['apellido'])): ?>
                    <span id="error-apellido" style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['apellido']) ?></span>
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
                    placeholder="Nombre del líder de célula"
                    maxlength="150"
                    required
                    data-autofill="true"
                    aria-describedby="error-lider_celula"
                >
                <?php if (!empty($errores['lider_celula'])): ?>
                    <span id="error-lider_celula" style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['lider_celula']) ?></span>
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
                    placeholder="Ej: Línea 1, Línea 2…"
                    maxlength="150"
                    required
                    data-autofill="true"
                    aria-describedby="error-linea"
                >
                <?php if (!empty($errores['linea'])): ?>
                    <span id="error-linea" style="color:#e74c3c;font-size:0.8rem"><?= htmlspecialchars($errores['linea']) ?></span>
                <?php endif; ?>
            </div>

        </div><!-- /formulario__grid -->

        <div class="formulario__acciones">
            <button type="submit" class="btn btn--exito">💾 Guardar Registro</button>
            <a href="index.php" class="btn btn--secundario">✖ Cancelar</a>
        </div>

    </form>
</div>

<script>
(function () {
    'use strict';

    const docInput    = document.getElementById('numero_documento');
    const docEstado   = document.getElementById('doc-estado');
    const autofillEls = document.querySelectorAll('[data-autofill="true"]');

    if (!docInput) return;

    // Helpers para bloquear / desbloquear campos auto-rellenables
    function bloquearCampos() {
        autofillEls.forEach(el => {
            el.setAttribute('readonly', 'readonly');
            el.setAttribute('tabindex', '-1');
            el.style.backgroundColor = '#f0f0f0';
            el.style.cursor = 'not-allowed';
            // Para <select>, readonly no funciona nativamente; usar disabled + hidden input
            if (el.tagName === 'SELECT') {
                el.setAttribute('disabled', 'disabled');
                // Asegurar que el valor se envíe igual: usamos un input hidden hermano
                let hidden = el.parentElement.querySelector('input[type=hidden][name="' + el.name + '"]');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type  = 'hidden';
                    hidden.name  = el.name;
                    el.parentElement.appendChild(hidden);
                }
                hidden.value = el.value;
            }
        });
    }

    function desbloquearCampos() {
        autofillEls.forEach(el => {
            el.removeAttribute('readonly');
            el.removeAttribute('tabindex');
            el.removeAttribute('disabled');
            el.style.backgroundColor = '';
            el.style.cursor = '';
            if (el.tagName === 'SELECT') {
                const hidden = el.parentElement.querySelector('input[type=hidden][name="' + el.name + '"]');
                if (hidden) hidden.remove();
            }
        });
    }

    function limpiarCampos() {
        autofillEls.forEach(el => {
            if (el.tagName === 'SELECT') {
                el.value = '';
            } else {
                el.value = '';
            }
        });
    }

    function mostrarEstado(tipo, texto) {
        docEstado.className = 'doc-estado doc-estado--' + tipo;
        docEstado.textContent = texto;
    }

    function limpiarEstado() {
        docEstado.className = 'doc-estado';
        docEstado.textContent = '';
    }

    // Rellenar campos con datos recibidos
    function rellenarCampos(datos) {
        const mapa = {
            nombre:       'nombre',
            apellido:     'apellido',
            color_equipo: 'color_equipo',
            lider_celula: 'lider_celula',
            linea:        'linea',
        };
        Object.entries(mapa).forEach(([clave, id]) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.value = datos[clave] ?? '';
            // Actualizar hidden sibling si existe (para <select>)
            if (el.tagName === 'SELECT') {
                const hidden = el.parentElement.querySelector('input[type=hidden][name="' + el.name + '"]');
                if (hidden) hidden.value = el.value;
            }
        });
    }

    // Evento principal: blur en el campo de documento
    docInput.addEventListener('blur', function () {
        const doc = this.value.trim();

        // Si es válido del lado del cliente: solo dígitos, no vacío
        if (doc === '' || !/^\d{1,20}$/.test(doc)) {
            limpiarEstado();
            desbloquearCampos();
            limpiarCampos();
            return;
        }

        mostrarEstado('buscando', '\u23F3 Buscando...');

        fetch('api/buscar_persona.php?doc=' + encodeURIComponent(doc))
            .then(function (res) {
                if (!res.ok) throw new Error('Error HTTP ' + res.status);
                return res.json();
            })
            .then(function (json) {
                if (json.encontrada) {
                    rellenarCampos(json.datos);
                    bloquearCampos();
                    mostrarEstado('encontrada', '\u2714 Persona registrada — ' + json.datos.nombre + ' ' + json.datos.apellido);
                } else {
                    desbloquearCampos();
                    limpiarCampos();
                    mostrarEstado('nueva', '\u271A Nueva persona — complete los datos');
                }
            })
            .catch(function () {
                desbloquearCampos();
                mostrarEstado('error', '\u26A0 No se pudo verificar el documento. Complete los datos manualmente.');
            });
    });

    // Si el campo ya tiene valor al cargar (ej. tras error de validación PHP),
    // disparar la verificación automáticamente
    if (docInput.value.trim() !== '' && /^\d{1,20}$/.test(docInput.value.trim())) {
        docInput.dispatchEvent(new Event('blur'));
    }
}());
</script>

<?php require_once 'includes/footer.php'; ?>
