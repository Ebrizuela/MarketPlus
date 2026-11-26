/**
 * js/productos_validacion.js
 * Validación inmediata (frontend) de unicidad del código de producto (HU-056).
 * Complementa la validación final del backend:
 *  - Consulta al endpoint de disponibilidad mientras el usuario escribe.
 *  - Muestra el mensaje de duplicado y bloquea el botón Guardar hasta corregir.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('codigo_producto');
        var boton = document.getElementById('btn_guardar_producto');
        var aviso = document.getElementById('codigo_producto_aviso');
        if (!input || !boton || !aviso) {
            return;
        }

        var endpoint = input.dataset.endpoint || 'productos/verificar_codigo.php';
        var temporizador = null;

        function bloquear(mensaje) {
            boton.disabled = true;
            aviso.textContent = mensaje;
            aviso.style.color = '#dc2626';
        }

        function permitir(mensaje) {
            boton.disabled = false;
            aviso.textContent = mensaje || '';
            aviso.style.color = '#16a34a';
        }

        function validar() {
            var codigo = input.value.trim();
            input.setCustomValidity('');

            if (codigo === '') {
                bloquear('El código es obligatorio.');
                return;
            }

            aviso.textContent = 'Verificando disponibilidad...';
            aviso.style.color = '#64748b';

            fetch(endpoint + '?codigo=' + encodeURIComponent(codigo), {
                headers: { 'Accept': 'application/json' }
            })
                .then(function (respuesta) { return respuesta.json(); })
                .then(function (data) {
                    if (data.existe === true) {
                        input.setCustomValidity('duplicado');
                        bloquear('El código ingresado ya existe. Ingrese un código único.');
                    } else if (data.existe === false) {
                        permitir('Código disponible.');
                    } else {
                        // Sin respuesta fiable: no bloquear; el backend validará al guardar.
                        permitir('');
                    }
                })
                .catch(function () {
                    permitir('');
                });
        }

        input.addEventListener('input', function () {
            clearTimeout(temporizador);
            temporizador = setTimeout(validar, 350);
        });
        input.addEventListener('blur', validar);

        // Validación final antes de enviar: evita eludir el bloqueo con el teclado (Enter).
        if (input.form) {
            input.form.addEventListener('submit', function (evento) {
                if (boton.disabled || input.value.trim() === '') {
                    evento.preventDefault();
                }
            });
        }
    });
})();
