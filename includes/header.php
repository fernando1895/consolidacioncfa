<?php
/**
 * Plantilla de encabezado compartida
 * Se incluye al inicio de cada página del sistema.
 *
 * Variable esperada: $tituloPagina (string) – título de la página actual.
 */

// Determinar la página activa para resaltar el ítem de navegación
$paginaActual = basename($_SERVER['PHP_SELF']);

// Calcular la ruta relativa a la raíz según el directorio del script que incluye este archivo
$raiz = rtrim(
    str_repeat('../', substr_count(
        str_replace('\\', '/', $_SERVER['PHP_SELF']), '/'
    ) - 2),
    '/'
);
$raiz = $raiz !== '' ? $raiz . '/' : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de asistencia – Ministerio Consolidación & Ministración">
    <title><?= htmlspecialchars($tituloPagina ?? 'Consolidación CFA') ?> – Consolidación CFA</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($raiz) ?>css/styles.css">
</head>
<body>

<!-- ── Encabezado del sitio ──────────────────────────────── -->
<header class="encabezado">
    <div class="encabezado__inner">
        <!-- Logo e identificación -->
        <div class="encabezado__logo">
            <span class="encabezado__icono">✝️</span>
            <div class="encabezado__titulo">
                <h1>Consolidación &amp; Ministración</h1>
                <p>Dpto. de Consolidación y Célula – CFA</p>
            </div>
        </div>

        <!-- Menú de navegación principal -->
        <nav class="navegacion" aria-label="Menú principal">
            <ul>
                <li>
                    <a href="index.php"
                       class="<?= $paginaActual === 'index.php' ? 'activo' : '' ?>">
                        📋 Asistencia
                    </a>
                </li>
                <li>
                    <a href="registro.php"
                       class="<?= $paginaActual === 'registro.php' ? 'activo' : '' ?>">
                        ➕ Nuevo Registro
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>

<!-- ── Contenido principal ───────────────────────────────── -->
<main class="contenido-principal">
