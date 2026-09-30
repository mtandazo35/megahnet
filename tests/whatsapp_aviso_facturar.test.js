/**
 * Prueba del aviso de WhatsApp al facturar un contrato.
 *
 * Simula el navegador (Swal, window, document) y ejecuta el codigo REAL de
 * assets/js/funciones.js, para comprobar que:
 *   1. Con el envio hecho (sin enlace) NO revienta y avisa "WhatsApp enviado".
 *   2. Sin telefono avisa, sin mentir diciendo que se envio.
 *   3. Si la API fallo, abre la vista previa con el enlace.
 *   4. La pagina se recarga SIEMPRE, y siempre DESPUES del aviso.
 *
 * Se ejecuta sin dependencias:  node tests/whatsapp_aviso_facturar.test.js
 */
'use strict';

const fs = require('fs');
const path = require('path');
const vm = require('vm');

const RAIZ = path.join(__dirname, '..');

// --- doble de Swal: recuerda lo que se pinto y cierra con la respuesta dada ---
function crearEntorno(respuestaModal) {
    const registro = { modales: [], recargas: 0, ventanas: [], ordenEventos: [] };

    const Swal = {
        fire(opciones) {
            registro.modales.push(opciones);
            registro.ordenEventos.push('modal:' + (opciones.title || ''));
            return Promise.resolve(respuestaModal || { isConfirmed: true, value: { msg: '', phone: '' } });
        },
        showLoading() {}
    };

    const contexto = {
        Swal,
        console,
        Promise,
        URL,
        decodeURIComponent,
        encodeURIComponent,
        String,
        window: {
            open: (u) => { registro.ventanas.push(u); },
            location: { reload: () => { registro.recargas++; registro.ordenEventos.push('recarga'); } }
        },
        document: {
            addEventListener() {},
            getElementById: () => ({ value: 'texto editado' }),
            querySelector: () => null
        },
        base_url: 'https://ejemplo.test/'
    };
    contexto.globalThis = contexto;
    vm.createContext(contexto);

    // Codigo real de produccion, sin tocar.
    const fuente = fs.readFileSync(path.join(RAIZ, 'assets', 'js', 'funciones.js'), 'utf8');
    try {
        vm.runInContext(fuente, contexto);
    } catch (e) {
        throw new Error('funciones.js no se pudo cargar en la simulacion: ' + e.message);
    }
    return { contexto, registro };
}

/**
 * Reproduce lo que hace contratos.js al recibir la respuesta del servidor:
 * avisar y recargar cuando el aviso se cierra.
 */
function trasFacturar(contexto, registro, res) {
    const recargar = () => contexto.window.location.reload();
    return contexto.notificarWhatsappFactura(res).then(recargar, recargar);
}

// ------------------------------------------------------------------ pruebas
const pruebas = [];
function prueba(nombre, fn) { pruebas.push({ nombre, fn }); }
function igual(real, esperado, que) {
    if (real !== esperado) {
        throw new Error(que + ': se esperaba ' + JSON.stringify(esperado) + ' y llego ' + JSON.stringify(real));
    }
}
function contiene(texto, trozo, que) {
    if (String(texto).indexOf(trozo) === -1) {
        throw new Error(que + ': "' + trozo + '" no aparece en ' + JSON.stringify(texto));
    }
}

prueba('el envio salio bien: avisa y recarga (este era el fallo)', async () => {
    const { contexto, registro } = crearEntorno();
    await trasFacturar(contexto, registro, {
        type: 'success', factura: 'electronica',
        whatsapp: null, whatsappEstado: 'enviado', telefonoCliente: '0997389861'
    });
    igual(registro.modales.length, 1, 'modales mostrados');
    igual(registro.modales[0].icon, 'success', 'icono del aviso');
    contiene(registro.modales[0].title, 'WhatsApp enviado', 'titulo');
    contiene(registro.modales[0].html, '+593997389861', 'telefono mostrado');
    igual(registro.recargas, 1, 'recargas');
    igual(registro.ordenEventos[registro.ordenEventos.length - 1], 'recarga', 'la recarga va al final');
});

prueba('cliente sin telefono: avisa sin afirmar que se envio', async () => {
    const { contexto, registro } = crearEntorno();
    await trasFacturar(contexto, registro, {
        type: 'success', factura: 'electronica', whatsapp: null, whatsappEstado: 'sin_telefono'
    });
    igual(registro.modales.length, 1, 'modales mostrados');
    igual(registro.modales[0].icon, 'info', 'icono');
    contiene(registro.modales[0].html, 'no tiene telefono', 'texto');
    igual(registro.recargas, 1, 'recargas');
});

prueba('la API fallo: abre la vista previa con el enlace', async () => {
    const { contexto, registro } = crearEntorno({ isConfirmed: true, value: { msg: 'hola', phone: '0999999999' } });
    await trasFacturar(contexto, registro, {
        type: 'success', factura: 'electronica', whatsappEstado: 'manual',
        whatsapp: 'https://web.whatsapp.com/send?phone=5930997389861&text=Buen%20dia'
    });
    contiene(registro.modales[0].title, 'Previsualizar WhatsApp', 'titulo de la vista previa');
    igual(registro.ventanas.length, 1, 'ventanas de WhatsApp abiertas');
    contiene(registro.ventanas[0], 'text=hola', 'el texto editado viaja en el enlace');
    igual(registro.recargas, 1, 'recargas');
    igual(registro.ordenEventos[registro.ordenEventos.length - 1], 'recarga', 'la recarga va despues del modal');
});

prueba('respuesta de un servidor viejo (sin estado): no inventa nada y recarga', async () => {
    const { contexto, registro } = crearEntorno();
    await trasFacturar(contexto, registro, { type: 'success', factura: 'electronica', whatsapp: null });
    igual(registro.modales.length, 0, 'no se muestra ningun aviso');
    igual(registro.recargas, 1, 'recargas');
});

prueba('enlace vacio directo a la vista previa: no revienta', async () => {
    const { contexto, registro } = crearEntorno();
    let fallo = null;
    try { await contexto.previsualizarYAbrirWhatsapp(null); } catch (e) { fallo = e; }
    igual(fallo, null, 'no debe lanzar excepcion');
    igual(registro.modales.length, 0, 'no abre vista previa sin enlace');
});

// ------------------------------------------------------------------ arranque
(async () => {
    let ok = 0, mal = 0;
    for (const p of pruebas) {
        try { await p.fn(); console.log('  OK   ' + p.nombre); ok++; }
        catch (e) { console.log('  FALLA ' + p.nombre + '\n         ' + e.message); mal++; }
    }
    console.log('\n' + ok + ' correctas, ' + mal + ' fallidas');
    process.exit(mal ? 1 : 0);
})();
