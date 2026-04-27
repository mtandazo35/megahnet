document.addEventListener('DOMContentLoaded', function () {
  // ====== HISTORIAL ======
  const tbl = $('#tblNotif').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    order: [[0, 'desc']],
    ajax: { url: base_url + 'notificaciones/listar', dataSrc: '' },
    columns: [
      { data: 'ts', width: '140px' },
      { data: 'tipo' },
      { data: 'asunto' },
      { data: 'destino' },
      { data: 'status' },
      { data: 'cuerpo' },
      { data: null, orderable: false, render: (d, t, r) => '<button class="btn btn-sm btn-info ver-detalle" data-idx="' + r.idx + '"><i class="bx bx-show"></i></button>' }
    ],
    language: { url: base_url + 'assets/js/espanol.json' }
  });

  document.querySelector('#tblNotif').addEventListener('click', function (e) {
    const btn = e.target.closest('.ver-detalle');
    if (!btn) return;
    fetch(base_url + 'notificaciones/ver/' + btn.dataset.idx)
      .then(r => r.json())
      .then(d => {
        if (!d.ok) return;
        const r = d.data;
        document.querySelector('#detalleNotifBody').innerHTML =
          '<table class="table table-sm">'
          + '<tr><th>Fecha</th><td>' + (r.ts || '') + '</td></tr>'
          + '<tr><th>Tipo</th><td>' + (r.tipo || '') + '</td></tr>'
          + '<tr><th>Asunto</th><td>' + (r.asunto || '') + '</td></tr>'
          + '<tr><th>Destino</th><td>' + (r.destino || '-') + '</td></tr>'
          + '<tr><th>Estado</th><td>' + (r.status || '') + '</td></tr>'
          + (r.error ? '<tr><th>Error</th><td class="text-danger">' + r.error + '</td></tr>' : '')
          + '</table>'
          + '<hr><div class="border rounded p-2" style="max-height:400px;overflow:auto;">' + (r.cuerpo || '') + '</div>';
        new bootstrap.Modal('#modalDetalleNotif').show();
      });
  });

  // Abrir modal de prueba
  document.querySelector('#btnProbarAlerta').addEventListener('click', function () {
    document.querySelector('#prueba_email').value = '';
    new bootstrap.Modal('#modalProbarNotif').show();
  });

  // Enviar prueba con override
  document.querySelector('#btnEnviarPrueba').addEventListener('click', function () {
    const email = document.querySelector('#prueba_email').value.trim();
    this.disabled = true;
    fetch(base_url + 'notificaciones/probar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email })
    })
      .then(r => r.json())
      .then(d => {
        bootstrap.Modal.getInstance('#modalProbarNotif').hide();
        let html = '';
        if (d.destino_email)    html += '<div><b>Email:</b> ' + d.destino_email + '</div>';
        Swal.fire({
          icon: d.ok ? 'success' : 'error',
          title: d.ok ? 'Notificacion enviada' : 'Fallo',
          html: html || (d.msg || ''),
        });
        tbl.ajax.reload();
      })
      .finally(() => { this.disabled = false; });
  });

  document.querySelector('#btnVaciarHist').addEventListener('click', function () {
    Swal.fire({ icon: 'warning', title: 'Vaciar historial?', showCancelButton: true, confirmButtonText: 'Si, vaciar' })
      .then(res => { if (!res.isConfirmed) return;
        fetch(base_url + 'notificaciones/vaciar').then(r => r.json()).then(() => tbl.ajax.reload());
      });
  });

  // ====== CONFIG ======
  document.querySelector('#btnGuardarCfg').addEventListener('click', function () {
    const destinatarios = document.querySelector('#cfg_destinatarios').value.split(/[\n,]/).map(s => s.trim()).filter(Boolean);
    const rate_limit_segs = parseInt(document.querySelector('#cfg_rate_limit').value) || 0;
    const tipos_activos = {};
    document.querySelectorAll('.cfg-tipo').forEach(c => { tipos_activos[c.dataset.tipo] = c.checked; });
    fetch(base_url + 'notificaciones/guardarConfig', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ destinatarios, rate_limit_segs, tipos_activos })
    }).then(r => r.json()).then(d => {
      Swal.fire({ icon: d.ok ? 'success' : 'error', title: d.ok ? 'Configuracion guardada' : 'Error', text: d.msg || '' });
    });
  });

  // ====== WHATSAPP ======
  let waPollTimer = null;
  let waQrTimer   = null;

  function waSetBadge(estado) {
    const badge = document.querySelector('#wa-status-badge');
    if (!badge) return;
    const map = {
      'open':       ['bg-success', 'Conectado'],
      'connected':  ['bg-success', 'Conectado'],
      'connecting': ['bg-warning text-dark', 'Conectando...'],
      'qr':         ['bg-info text-dark', 'Esperando QR'],
      'close':      ['bg-danger', 'Desconectado'],
      'closed':     ['bg-danger', 'Desconectado'],
      'sin_sesion': ['bg-secondary', 'Sin sesion'],
      'cerrada':    ['bg-secondary', 'Cerrada'],
    };
    const [cls, txt] = map[estado] || ['bg-secondary', estado || '-'];
    badge.className = 'badge ' + cls;
    badge.textContent = txt;

    const btnVin   = document.querySelector('#btnVincularWa');
    const btnClose = document.querySelector('#btnCerrarWa');
    if (estado === 'open' || estado === 'connected') {
      btnVin.classList.add('d-none');
      btnClose.classList.remove('d-none');
    } else if (estado === 'sin_sesion' || estado === 'cerrada' || estado === 'close' || estado === 'closed') {
      btnVin.classList.remove('d-none');
      btnClose.classList.add('d-none');
    } else {
      btnVin.classList.add('d-none');
      btnClose.classList.remove('d-none');
    }
  }

  function waCheckEstado(silencioso) {
    return fetch(base_url + 'notificaciones/waEstadoSesion')
      .then(r => r.json())
      .then(d => {
        if (!d.ok) return d;
        waSetBadge(d.estado);
        document.querySelector('#wa-phone').textContent = d.phone || '-';
        if (d.estado === 'open' || d.estado === 'connected') {
          const m = bootstrap.Modal.getInstance(document.querySelector('#modalQrWa'));
          const wasModalOpen = !!m;
          if (m) m.hide();
          if (waQrTimer)   { clearInterval(waQrTimer);   waQrTimer = null; }
          if (waPollTimer) { clearInterval(waPollTimer); waPollTimer = null; }
          if (wasModalOpen || !silencioso) {
            Swal.fire({ icon:'success', title:'WhatsApp vinculado', text:'Numero ' + (d.phone||''), timer: 2500 });
          }
        }
        return d;
      });
  }

  function waCargarQr() {
    fetch(base_url + 'notificaciones/waObtenerQr')
      .then(r => r.json())
      .then(d => {
        const wrap = document.querySelector('#wa-qr-wrap');
        if (!wrap) return;
        if (d.qr_image) {
          wrap.innerHTML = '<img src="' + d.qr_image + '" alt="QR WhatsApp" style="max-width:280px;width:100%;height:auto;">';
        } else if (d.status_msg === 'connected' || d.status_msg === 'open') {
          wrap.innerHTML = '<div class="text-success"><i class="bx bx-check-circle" style="font-size:3em;"></i><div>Conectado</div></div>';
        } else {
          wrap.innerHTML = '<div class="spinner-border text-success" role="status"></div><div class="text-muted mt-2">' + (d.status_msg || 'Generando QR...') + '</div>';
        }
      });
  }

  const btnVincularWa = document.querySelector('#btnVincularWa');
  if (btnVincularWa) {
    btnVincularWa.addEventListener('click', function () {
      this.disabled = true;
      fetch(base_url + 'notificaciones/waCrearSesion', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
      })
        .then(r => r.json())
        .then(d => {
          if (!d.ok) {
            Swal.fire({ icon:'error', title:'Error creando sesion', text: (d.msg || JSON.stringify(d.data || d.raw || d.error)) });
            return;
          }
          new bootstrap.Modal('#modalQrWa').show();
          waCargarQr();
          if (waQrTimer)   clearInterval(waQrTimer);
          waQrTimer   = setInterval(waCargarQr, 7000);
          if (waPollTimer) clearInterval(waPollTimer);
          waPollTimer = setInterval(() => waCheckEstado(true), 4000);
        })
        .finally(() => { this.disabled = false; });
    });
  }

  const btnCerrarWa = document.querySelector('#btnCerrarWa');
  if (btnCerrarWa) {
    btnCerrarWa.addEventListener('click', function () {
      Swal.fire({ icon:'warning', title:'Cerrar sesion de WhatsApp?', showCancelButton:true, confirmButtonText:'Si, cerrar' })
        .then(res => {
          if (!res.isConfirmed) return;
          fetch(base_url + 'notificaciones/waCerrarSesion', { method: 'POST' })
            .then(r => r.json())
            .then(d => {
              Swal.fire({ icon: d.ok ? 'success' : 'error', title: d.ok ? 'Sesion cerrada' : 'Error' });
              if (waPollTimer) { clearInterval(waPollTimer); waPollTimer = null; }
              if (waQrTimer)   { clearInterval(waQrTimer);   waQrTimer = null; }
              waSetBadge('sin_sesion');
              document.querySelector('#wa-phone').textContent = '-';
            });
        });
    });
  }

  const btnGuardarWaCfg = document.querySelector('#btnGuardarWaCfg');
  if (btnGuardarWaCfg) {
    btnGuardarWaCfg.addEventListener('click', function () {
      const phones = document.querySelector('#wa_phones_alerta').value.split(/[\n,]/).map(s => s.trim()).filter(Boolean);
      fetch(base_url + 'notificaciones/guardarConfig', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ wa_api: { phones_alerta: phones } })
      }).then(r => r.json()).then(d => {
        Swal.fire({ icon: d.ok ? 'success' : 'error', title: d.ok ? 'Configuracion WhatsApp guardada' : 'Error', text: d.msg || '' });
      });
    });
  }

  const btnProbarWa = document.querySelector('#btnProbarWa');
  if (btnProbarWa) {
    btnProbarWa.addEventListener('click', function () {
      const inputTel = document.querySelector('#wa_test_number');
      const tel = inputTel.value.trim();
      if (!tel) { Swal.fire({icon:'warning', title:'Ingresa un numero'}); return; }
      this.disabled = true;
      fetch(base_url + 'notificaciones/waEnviarPrueba', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ number: tel, message: 'Prueba desde el sistema. Si recibes este mensaje, la integracion funciona.' })
      })
        .then(r => r.json())
        .then(d => {
          Swal.fire({ icon: d.ok ? 'success' : 'error', title: d.ok ? 'Mensaje enviado' : 'Fallo el envio', text: d.msg || (d.error || '') });
          if (d.ok) inputTel.value = '';
        })
        .finally(() => { this.disabled = false; });
    });
  }

  // Limpiar campo "Probar envio" al cargar la pagina (evita autocompletado del navegador)
  const waTestNumberInput = document.querySelector('#wa_test_number');
  if (waTestNumberInput) waTestNumberInput.value = '';

  // Estado inicial al cargar
  if (document.querySelector('#wa-status-badge')) {
    waCheckEstado(true);
    waPollTimer = setInterval(() => waCheckEstado(true), 15000);
  }

  // ====== PLANTILLAS ======
  let plantillaActual = null;
  let plantillasCache = (typeof window !== 'undefined' && window.__plantillasInitial) ? window.__plantillasInitial : {};

  function cargarPlantillas() {
    fetch(base_url + 'notificaciones/listarPlantillas')
      .then(r => r.json())
      .then(d => { plantillasCache = d || {}; });
  }
  cargarPlantillas();

  document.querySelectorAll('.plantilla-item').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.plantilla-item').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      const key = this.dataset.key;
      plantillaActual = key;
      const p = plantillasCache[key] || {};
      document.querySelector('#plantilla-key').textContent = '— ' + key;
      document.querySelector('#pl_descripcion').value = p.descripcion || '';
      document.querySelector('#pl_asunto').value = p.asunto || '';
      document.querySelector('#pl_cuerpo').value = p.cuerpo || '';
    });
  });

  // Click en placeholder lo inserta
  document.querySelectorAll('#nav-plantillas code').forEach(c => {
    c.style.cursor = 'pointer';
    c.title = 'Click para insertar';
    c.addEventListener('click', function () {
      const ta = document.querySelector('#pl_cuerpo');
      const start = ta.selectionStart, end = ta.selectionEnd;
      ta.value = ta.value.substring(0, start) + this.textContent + ta.value.substring(end);
      ta.focus();
      ta.selectionStart = ta.selectionEnd = start + this.textContent.length;
    });
  });

  document.querySelector('#btnGuardarPlantilla').addEventListener('click', function () {
    if (!plantillaActual) {
      Swal.fire({ icon: 'warning', title: 'Selecciona una plantilla' });
      return;
    }
    fetch(base_url + 'notificaciones/guardarPlantilla', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        key: plantillaActual,
        descripcion: document.querySelector('#pl_descripcion').value,
        asunto: document.querySelector('#pl_asunto').value,
        cuerpo: document.querySelector('#pl_cuerpo').value,
      })
    }).then(r => r.json()).then(d => {
      Swal.fire({ icon: d.ok ? 'success' : 'error', title: d.ok ? 'Plantilla guardada' : 'Error', text: d.msg || '' });
      if (d.ok) cargarPlantillas();
    });
  });

  document.querySelector('#btnPreviewPlantilla').addEventListener('click', function () {
    fetch(base_url + 'notificaciones/previsualizar', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        asunto: document.querySelector('#pl_asunto').value,
        cuerpo: document.querySelector('#pl_cuerpo').value,
      })
    }).then(r => r.json()).then(d => {
      if (!d.ok) return;
      document.querySelector('#prev_asunto').textContent = d.asunto;
      // Convertir formato WhatsApp simple a HTML
      let body = d.cuerpo
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/\*([^\*\n]+)\*/g, '<b>$1</b>')
        .replace(/_([^_\n]+)_/g, '<i>$1</i>');
      document.querySelector('#prev_cuerpo').innerHTML = body;
      new bootstrap.Modal('#modalPreviewPlantilla').show();
    });
  });
});


// Auto-seleccionar plantilla via query string (?plantilla=key)
(function(){
  function selectPlantillaFromQuery() {
    var params = new URLSearchParams(window.location.search);
    var key = params.get('plantilla');
    if (!key) return;
    var btn = document.querySelector('.plantilla-item[data-key="' + key + '"]');
    if (btn) {
      // Asegurar que el tab Plantillas este activo
      var tabBtn = document.querySelector('[data-bs-toggle="tab"][data-bs-target="#nav-plantillas"]');
      if (tabBtn && typeof bootstrap !== 'undefined') {
        var tab = bootstrap.Tab.getOrCreateInstance(tabBtn);
        tab.show();
      }
      // Esperar a que el cache de plantillas este poblado (es fetch async).
      // Polling cada 80ms hasta 4s.
      var startedAt = Date.now();
      var poll = function(){
        var ready = (typeof plantillasCache !== 'undefined' && plantillasCache && plantillasCache[key]);
        if (ready) {
          btn.click();
          btn.scrollIntoView({block:'nearest', behavior:'smooth'});
          btn.style.transition = 'box-shadow .8s ease';
          btn.style.boxShadow = '0 0 0 3px rgba(37,99,235,.35)';
          setTimeout(function(){ btn.style.boxShadow = ''; }, 1500);
          return;
        }
        if (Date.now() - startedAt < 4000) {
          setTimeout(poll, 80);
        } else {
          // Click igual aunque el cache no este, asi al menos marca activo
          btn.click();
        }
      };
      setTimeout(poll, 200);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', selectPlantillaFromQuery);
  } else {
    selectPlantillaFromQuery();
  }
})();
