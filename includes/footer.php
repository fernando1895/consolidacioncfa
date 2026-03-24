<?php
/**
 * Plantilla de pie de página compartida
 * Se incluye al final de cada página del sistema.
 */
?>
</main><!-- /contenido-principal -->

<!-- ── Pie de página ─────────────────────────────────────── -->
<footer class="pie-pagina">
    <p>
        &copy; <?= date('Y') ?> Ministerio Consolidación &amp; Ministración – Dpto. de Consolidación y Célula – CFA.
        Todos los derechos reservados.
    </p>
</footer>

<script src="<?= htmlspecialchars($raiz ?? '') ?>js/autocomplete-persona.js"></script>
</body>
</html>
