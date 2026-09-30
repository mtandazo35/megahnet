// Tab helpers null-safe: si la vista NO tiene #nav-tab (ej. usa modales),
// firstTab/primerTab quedan como no-ops para no romper modulos legacy.
const firstTabEl = document.querySelector('#nav-tab button:last-child');
const firstTab = firstTabEl ? new bootstrap.Tab(firstTabEl) : { show: function(){} };

const primerTabEl = document.querySelector('#nav-tab button:first-child');
const primerTab = primerTabEl ? new bootstrap.Tab(primerTabEl) : { show: function(){} };


function insertarRegistros(url, idFormulario, tbl, idButton, accion) {
    //crear formData
    const data = new FormData(idFormulario);
    //hacer una instancia del objeto XMLHttpRequest
    const http = new XMLHttpRequest();
    //Abrir una Conexion - POST - GET
    http.open('POST', url, true);
    //Enviar Datos
    http.send(data);
    //verificar estados
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            Swal.fire({
                toast: true,
                position: 'top-right',
                icon: res.type,
                title: res.msg,
                showConfirmButton: false,
                timer: 2000
            })
            if (res.type == 'success') {
                if (accion) {
                    clave.removeAttribute('readonly');
                }
                if (tbl != null) {
                    document.querySelector('#id').value = '';
                    idButton.textContent = 'Registrar';
                    idFormulario.reset();
                    tbl.ajax.reload();
                    primerTab.show();
                }
                // Evento custom para que vistas con modales cierren el modal tras exito.
                try {
                    idFormulario.dispatchEvent(new CustomEvent('mhn:registroOk', {
                        detail: res, bubbles: true
                    }));
                } catch (e) { /* ignore */ }
            }
        }
    }
}

function eliminarRegistros(url, tbl) {
    Swal.fire({
        title: 'Esta seguro de eliminar?',
        text: "El registro no se eliminará de forma permanente, solo cambiará el estado!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, Eliminar!'
    }).then((result) => {
        if (result.isConfirmed) {
            //hacer una instancia del objeto XMLHttpRequest 
            const http = new XMLHttpRequest();
            //Abrir una Conexion - POST - GET
            http.open('GET', url, true);
            //Enviar Datos
            http.send();
            //verificar estados
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    Swal.fire({
                        toast: true,
                        position: 'top-right',
                        icon: res.type,
                        title: res.msg,
                        showConfirmButton: false,
                        timer: 2000
                    })
                    if (res.type == 'success') {
                        tbl.ajax.reload();
                    }
                }
            }
        }
    })
}

function eliminarRegistros2(url, tbl, tbl2) {
    Swal.fire({
        title: 'Esta seguro de eliminar?',
        text: "El registro se eliminará de forma permanente!!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, Eliminar!'
    }).then((result) => {
        if (result.isConfirmed) {
            //hacer una instancia del objeto XMLHttpRequest 
            const http = new XMLHttpRequest();
            //Abrir una Conexion - POST - GET
            http.open('GET', url, true);
            //Enviar Datos
            http.send();
            //verificar estados
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    Swal.fire({
                        toast: true,
                        position: 'top-right',
                        icon: res.type,
                        title: res.msg,
                        showConfirmButton: false,
                        timer: 2000
                    })
                    if (res.type == 'success') {
                        tbl.ajax.reload();
                        tbl2.ajax.reload();

                    }
                }
            }
        }
    })
}

function suspenderRegistros(url, tbl) {
    Swal.fire({
        title: 'Esta seguro de suspender?',
        text: "El registro no se eliminará de forma permanente, solo cambiará el estado!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, Suspender!'
    }).then((result) => {
        if (result.isConfirmed) {
            //hacer una instancia del objeto XMLHttpRequest 
            const http = new XMLHttpRequest();
            //Abrir una Conexion - POST - GET
            http.open('GET', url, true);
            //Enviar Datos
            http.send();
            //verificar estados
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    Swal.fire({
                        toast: true,
                        position: 'top-right',
                        icon: res.type,
                        title: res.msg,
                        showConfirmButton: false,
                        timer: 2000
                    })
                    if (res.type == 'success') {
                        tbl.ajax.reload();
                    }
                }
            }
        }
    })
}

function suspenderContratos(url, tbl) {
    Swal.fire({
        title: 'Esta seguro de suspender?',
        text: "El registro no se eliminará de forma permanente, solo cambiará el estado!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, Suspender!'
    }).then((result) => {
        if (result.isConfirmed) {
            //hacer una instancia del objeto XMLHttpRequest 
            const http = new XMLHttpRequest();
            //Abrir una Conexion - POST - GET
            http.open('GET', url, true);
            //Enviar Datos
            http.send();
            //verificar estados
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    Swal.fire({
                        toast: true,
                        position: 'top-right',
                        icon: res.type,
                        title: res.msg,
                        showConfirmButton: false,
                        timer: 2000
                    })
                    if (res.type == 'success') {
                        // Si el backend dice que no envio por API, ofrecer fallback wa.me manual.
                        // Si si envio (whatsapp_sent=true) o no hay URL, no abrir nada.
                        if (res.whatsapp && !res.whatsapp_sent) {
                            window.open(res.whatsapp, '_blank');
                        }
                        tbl.ajax.reload();
                        location.reload();
                    }
                }
            }
        }
    })
}

function restaurarRegistros(url, tbl) {
    Swal.fire({
        title: 'Esta seguro de restaurar?',
        text: "El registro esta por restaurarse, solo cambiara al estado activo!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, Restaurar!'
    }).then((result) => {
        if (result.isConfirmed) {
            //hacer una instancia del objeto XMLHttpRequest 
            const http = new XMLHttpRequest();
            //Abrir una Conexion - POST - GET
            http.open('GET', url, true);
            //Enviar Datos
            http.send();
            //verificar estados
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    Swal.fire({
                        toast: true,
                        position: 'top-right',
                        icon: res.type,
                        title: res.msg,
                        showConfirmButton: false,
                        timer: 2000
                    })
                    if (res.type == 'success') {
                        // Solo abrir whatsapp si el backend lo devuelve (no todos los modulos lo hacen).
                        if (res.whatsapp) {
                            window.open(res.whatsapp, '_blank');
                        }
                        if (tbl) {
                            tbl.ajax.reload();
                        }
                        // Evento para que la vista principal (con modal de inactivos)
                        // recargue tambien su tabla de activos. Si no hay listener
                        // y tampoco tabla pasada, recarga la pagina como fallback.
                        try {
                            document.dispatchEvent(new CustomEvent('mhn:restauradoOk', {
                                detail: { url: url, res: res }
                            }));
                        } catch (e) { /* ignore */ }
                        if (!tbl) {
                            location.reload();
                        }
                    }
                }
            }
        }
    })
}

function alertaPersonalizada(type, msg) {
    Swal.fire({
        toast: true,
        position: 'top-right',
        icon: type,
        title: msg,
        showConfirmButton: false,
        timer: 5000
    })

}

// Previsualiza un mensaje WhatsApp (URL tipo wa.me / web.whatsapp.com/send?text=...)
// antes de abrirlo. El usuario puede editar el mensaje en el modal y los cambios
// se reflejan en la URL final que se abre.
/**
 * Avisa del resultado del WhatsApp que acompana a una factura y devuelve SIEMPRE
 * una promesa, para que quien llame pueda recargar la pagina despues (y no a la
 * vez, que era lo que borraba el aviso antes de verse).
 *   enviado      -> la API lo mando sola, solo se confirma
 *   sin_telefono -> el cliente no tiene numero: se avisa, no es un error
 *   manual       -> la API fallo; se abre la vista previa para mandarlo a mano
 */
function notificarWhatsappFactura(res) {
    res = res || {};
    var estado = res.whatsappEstado || (res.whatsapp ? 'manual' : '');

    if (estado === 'manual' || (!estado && res.whatsapp)) {
        return previsualizarYAbrirWhatsapp(res.whatsapp);
    }
    if (estado === 'enviado') {
        var tel = res.telefonoCliente ? (' a +593' + String(res.telefonoCliente).replace(/^0/, '')) : '';
        return Swal.fire({
            icon: 'success',
            title: 'WhatsApp enviado',
            html: '<span style="font-size:.9rem;color:#374151;">Se confirmo el pago al cliente' + tel + '.</span>',
            timer: 2600,
            timerProgressBar: true,
            showConfirmButton: false
        });
    }
    if (estado === 'sin_telefono') {
        return Swal.fire({
            icon: 'info',
            title: 'Sin WhatsApp',
            html: '<span style="font-size:.9rem;color:#374151;">El cliente no tiene telefono registrado, no se envio la confirmacion.</span>',
            confirmButtonText: 'Entendido'
        });
    }
    // Respuesta de una version anterior del servidor: no se inventa nada.
    return Promise.resolve();
}

function previsualizarYAbrirWhatsapp(url) {
    var text = '';
    var phone = '';
    // Sin enlace no hay nada que previsualizar: antes se intentaba igual y la
    // funcion moria con "Cannot read properties of null", dejando la pantalla
    // colgada justo cuando el envio habia salido bien.
    if (!url) { return Promise.resolve(); }
    var sUrl = String(url);
    try {
        var u = new URL(sUrl);
        text  = u.searchParams.get('text')  || '';
        phone = u.searchParams.get('phone') || '';
    } catch (e) {
        // Fallback regex si URL no parsea (ej. URLs con espacios sin codificar)
        var m = sUrl.match(/[?&]text=([^&]*)/);   if (m) { try { text  = decodeURIComponent(m[1].replace(/\+/g,' ')); } catch(_){ text  = m[1]; } }
        var p = sUrl.match(/[?&]phone=([^&]*)/);  if (p) { try { phone = decodeURIComponent(p[1]); } catch(_){ phone = p[1]; } }
    }

    return Swal.fire({
        title: '<i class="bx bxl-whatsapp" style="color:#16a34a;font-size:24px;vertical-align:-4px;"></i> Previsualizar WhatsApp',
        html:
            '<div class="text-start">' +
              '<label class="form-label small fw-semibold text-muted mb-1">Destinatario</label>' +
              '<input id="waPrevPhone" class="form-control form-control-sm mb-2" value="' + (phone || '').replace(/"/g,'&quot;') + '" />' +
              '<label class="form-label small fw-semibold text-muted mb-1">Mensaje</label>' +
              '<textarea id="waPrevMsg" class="form-control form-control-sm" rows="6" style="font-size:.9rem;">' + (text || '').replace(/</g,'&lt;') + '</textarea>' +
              '<small class="text-muted d-block mt-1" style="font-size:.7rem;">Edita libremente antes de enviar. Se abrira WhatsApp con el mensaje.</small>' +
            '</div>',
        showCancelButton: true,
        confirmButtonText: '<i class="bx bxl-whatsapp"></i> Enviar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#16a34a',
        focusConfirm: false,
        width: 540,
        preConfirm: function() {
            var msg = document.getElementById('waPrevMsg').value || '';
            var ph  = document.getElementById('waPrevPhone').value || '';
            return { msg: msg, phone: ph };
        }
    }).then(function(result) {
        if (!result.isConfirmed) return;
        var msg = result.value.msg;
        var ph  = (result.value.phone || '').replace(/[^0-9+]/g, '');
        var finalUrl;
        try {
            var u2 = new URL(sUrl);
            u2.searchParams.set('text', msg);
            if (ph) {
                u2.searchParams.set('phone', ph);
                u2.searchParams.set('abid',  ph);
            }
            finalUrl = u2.toString();
        } catch (e) {
            finalUrl = 'https://web.whatsapp.com/send?text=' + encodeURIComponent(msg) +
                       (ph ? '&phone=' + encodeURIComponent(ph) + '&abid=' + encodeURIComponent(ph) : '');
        }
        window.open(finalUrl, '_blank');
    });
}

// Fix: los dropdowns de acciones dentro de <div class="table-responsive"> se
// RECORTAN por el overflow del contenedor (overflow-x:auto => overflow-y:auto).
// Con pocas filas la tabla es baja y el menu que abre hacia abajo pierde sus
// ultimos items (p.ej. "Eliminar contrato"). Mientras el dropdown esta abierto
// dejamos el contenedor en overflow:visible y lo restauramos al cerrar.
document.addEventListener('show.bs.dropdown', function (e) {
    var cont = e.target && e.target.closest ? e.target.closest('.table-responsive') : null;
    if (cont) {
        cont.dataset.prevOverflow = cont.style.overflow || '';
        cont.style.overflow = 'visible';
    }
});
document.addEventListener('hide.bs.dropdown', function (e) {
    var cont = e.target && e.target.closest ? e.target.closest('.table-responsive') : null;
    if (cont) {
        cont.style.overflow = cont.dataset.prevOverflow || '';
        delete cont.dataset.prevOverflow;
    }
});