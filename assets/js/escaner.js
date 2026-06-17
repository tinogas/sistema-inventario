/**
 * Módulo de escáner de código de barras USB HID
 * El escáner actúa como teclado ultra-rápido: emite caracteres a ~5-10ms de intervalo y termina con Enter.
 * El tecleo humano normal es ~100-200ms entre teclas.
 * Estrategia: acumular caracteres en buffer; si llega Enter con buffer ≥ 4 chars → escaneo detectado.
 */
(function (global) {
    'use strict';

    const TIMEOUT_MS      = 80;   // ms entre chars para considerar escaneo vs tecleo
    const LONGITUD_MINIMA = 4;    // códigos de menos de 4 chars son probablemente ruido

    let bufferEscaner  = '';
    let timerEscaner   = null;
    let callbackActivo = null;
    let activo         = false;

    function onKeyDown(e) {
        if (!activo) return;

        if (e.key === 'Enter') {
            if (bufferEscaner.length >= LONGITUD_MINIMA) {
                e.preventDefault();
                const codigo = bufferEscaner;
                bufferEscaner = '';
                clearTimeout(timerEscaner);
                if (callbackActivo) callbackActivo(codigo);
            }
            return;
        }

        // Solo acumular caracteres imprimibles
        if (e.key.length === 1) {
            bufferEscaner += e.key;
            clearTimeout(timerEscaner);
            timerEscaner = setTimeout(function () {
                // Si pasan 80ms sin más teclas → era tecleo manual, limpiar
                bufferEscaner = '';
            }, TIMEOUT_MS);
        }
    }

    global.EscanerHandler = {
        /**
         * Inicia la escucha del escáner.
         * @param {function} callback  Función que recibe el código leído
         */
        iniciar: function (callback) {
            callbackActivo = callback;
            activo = true;
            document.addEventListener('keydown', onKeyDown);
        },

        /**
         * Detiene la escucha
         */
        detener: function () {
            activo = false;
            document.removeEventListener('keydown', onKeyDown);
            callbackActivo = null;
            bufferEscaner = '';
            clearTimeout(timerEscaner);
        },

        /**
         * Pausa/reanuda sin desregistrar el listener
         */
        pausar: function () { activo = false; },
        reanudar: function () { activo = true; }
    };

})(window);
