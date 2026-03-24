/**
 * autocomplete-persona.js
 * Autocompletado de campos a partir del número de documento.
 *
 * Funciona en registro.php y editar.php.
 * Busca el input#numero_documento y, con debounce de 300 ms,
 * consulta api/buscar_persona.php y rellena los campos destino.
 */

(function () {
    'use strict';

    /** Devuelve la ruta base hasta la raíz del proyecto */
    function getApiBase() {
        // El script puede cargarse desde la raíz o desde un subdirectorio.
        // Usamos la URL del propio script para calcular la ruta al API.
        var scripts = document.querySelectorAll('script[src]');
        for (var i = 0; i < scripts.length; i++) {
            var src = scripts[i].getAttribute('src');
            if (src && src.indexOf('autocomplete-persona.js') !== -1) {
                // Eliminar "js/autocomplete-persona.js" y quedarnos con la raíz
                return src.replace(/js\/autocomplete-persona\.js.*$/, '');
            }
        }
        return '';
    }

    /**
     * Crea una función debounce.
     * @param {Function} fn   - Función a ejecutar.
     * @param {number}   wait - Milisegundos de espera.
     */
    function debounce(fn, wait) {
        var timer;
        return function () {
            var args = arguments;
            var ctx  = this;
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(ctx, args);
            }, wait);
        };
    }

    /** Muestra u oculta el indicador de estado junto al campo documento */
    function setEstado(el, estado) {
        var hint = document.getElementById('doc-hint');
        if (!hint) return;
        hint.className = 'doc-hint doc-hint--' + estado;
        switch (estado) {
            case 'buscando':
                hint.textContent = '🔍 Buscando…';
                break;
            case 'encontrado':
                hint.textContent = '✅ Persona encontrada. Campos completados.';
                break;
            case 'no-encontrado':
                hint.textContent = '⚠️ Documento no registrado. Complete los campos manualmente.';
                break;
            case 'error':
                hint.textContent = '❌ Error al consultar. Intente de nuevo.';
                break;
            case 'vacio':
            default:
                hint.textContent = '';
        }
    }

    /**
     * Rellena un campo del formulario (input o select).
     * @param {string} id    - ID del elemento.
     * @param {string} valor - Valor a asignar.
     */
    function rellenarCampo(id, valor) {
        var el = document.getElementById(id);
        if (!el) return;
        el.value = valor;
        // Disparar 'change' para frameworks/listeners que lo escuchen
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function init() {
        var docInput = document.getElementById('numero_documento');
        if (!docInput) return; // No estamos en una página con este campo

        var apiBase = getApiBase();

        var buscar = debounce(function () {
            var doc = docInput.value.trim();
            if (doc === '') {
                setEstado(docInput, 'vacio');
                return;
            }

            setEstado(docInput, 'buscando');

            fetch(apiBase + 'api/buscar_persona.php?documento=' + encodeURIComponent(doc))
                .then(function (resp) {
                    if (!resp.ok) throw new Error('HTTP ' + resp.status);
                    return resp.json();
                })
                .then(function (data) {
                    if (data.found && data.persona) {
                        var p = data.persona;
                        rellenarCampo('nombre',       p.nombre       || '');
                        rellenarCampo('apellido',     p.apellido     || '');
                        rellenarCampo('color_equipo', p.color_equipo || '');
                        rellenarCampo('lider_celula', p.lider_celula || '');
                        rellenarCampo('linea',        p.linea        || '');
                        setEstado(docInput, 'encontrado');
                    } else {
                        setEstado(docInput, 'no-encontrado');
                    }
                })
                .catch(function () {
                    setEstado(docInput, 'error');
                });
        }, 300);

        docInput.addEventListener('input', buscar);
        docInput.addEventListener('blur',  buscar);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
