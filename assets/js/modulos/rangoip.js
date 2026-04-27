let divLoading = document.querySelector("#divLoading");
const ipv4Regex = /^(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/;
const cidrRegex = /^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\/(\d{1,2})$/;
function ip2num(ip) {
    return ip.split('.').reduce((a, b) => (a << 8) + parseInt(b, 10), 0) >>> 0;
}
function num2ip(n) {
    return [(n >>> 24) & 255, (n >>> 16) & 255, (n >>> 8) & 255, n & 255].join('.');
}
function parseCidr(cidr) {
    const m = cidrRegex.exec((cidr || '').trim());
    if (!m) return null;
    const ip = m[1], maskBits = parseInt(m[2], 10);
    if (!ipv4Regex.test(ip) || maskBits < 0 || maskBits > 32) return null;
    const ipnum = ip2num(ip);
    const mask = maskBits === 0 ? 0 : ((0xFFFFFFFF << (32 - maskBits)) >>> 0);
    const network = (ipnum & mask) >>> 0;
    const broadcast = (network | (~mask >>> 0)) >>> 0;
    if (maskBits >= 31) {
        return { network, broadcast, firstUsable: network, lastUsable: broadcast, mask: maskBits, hosts: maskBits === 32 ? 1 : 2 };
    }
    return { network, broadcast, firstUsable: network + 1, lastUsable: broadcast - 1, mask: maskBits, hosts: broadcast - network - 1 };
}
function inferCidr(redIp, finalIp) {
    if (!ipv4Regex.test(redIp) || !ipv4Regex.test(finalIp)) return '';
    const r = ip2num(redIp), f = ip2num(finalIp);
    // intenta varios masks comunes
    for (let m = 8; m <= 30; m++) {
        const p = parseCidr(redIp + '/' + m);
        if (!p) continue;
        if (p.lastUsable === f && p.network === r) return num2ip(p.network) + '/' + m;
        if (p.lastUsable === f && p.firstUsable === r) return num2ip(p.network) + '/' + m;
    }
    return '';
}


let tblIp;
const formulario = document.querySelector('#formulario');
const id = document.querySelector('#id');
const red = document.querySelector('#red');
const redCidr = document.querySelector('#redCidr');
const finalDisplay = document.querySelector('#finalDisplay');
const rangoInfo = document.querySelector('#rangoInfo');
const errorRed = document.querySelector('#errorRed');
const final = document.querySelector('#final');
const errorFinal = document.querySelector('#errorFinal');
const gateway = document.querySelector('#gateway');
const errorGateway = document.querySelector('#errorGateway');
const zona = document.querySelector('#zona');

const btnAccion = document.querySelector('#btnAccion');
const btnNuevo = document.querySelector('#btnNuevo');
document.addEventListener('DOMContentLoaded', function(){
    //cargar datos con el plugin datatables
    tblIp = $('#tblIp').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'rangoip/listar',
            dataSrc: ''
        },
        columns: [
            { data: 'id' },
            { data: 'redCidr' },
            { data: 'gateway' },
            { data: 'final' },
            { data: 'ultima' },
            { data: 'disponibles'},
            { data: 'zona'},
            { data: 'mikrotik_nombre' },
            { data: 'acciones' }
        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        order: [[0, 'asc']],
    });

    // Auto-calcular Final desde CIDR
    redCidr.addEventListener('input', function(){
        const p = parseCidr(redCidr.value);
        if (p) {
            red.value = num2ip(p.network);
            final.value = num2ip(p.lastUsable);
            finalDisplay.value = num2ip(p.lastUsable);
            rangoInfo.textContent = 'Red: ' + num2ip(p.network) + '  Broadcast: ' + num2ip(p.broadcast) + '  Hosts utiles: ' + p.hosts;
            errorRed.textContent = '';
        } else {
            red.value = '';
            final.value = '';
            finalDisplay.value = '';
            rangoInfo.textContent = '';
        }
    });

    btnNuevo.addEventListener('click', function(){
        id.value = '';
        errorRed.textContent = '';
        errorGateway.textContent = '';
        errorFinal.textContent = '';
        btnAccion.textContent = 'Registrar';
        formulario.reset();
        finalDisplay.value = '';
        redCidr.value = '';
        rangoInfo.textContent = '';
        red.value = '';
        final.value = '';
        redCidr.readOnly = false;
        gateway.disabled = false;
        zona.disabled = false;
        const aviso = document.getElementById('avisoClientes');
        if (aviso) aviso.remove();
    })
    //registrar categorias
    formulario.addEventListener('submit', function(e){
        e.preventDefault();
        errorRed.textContent = '';
        errorGateway.textContent = '';
        errorFinal.textContent = '';

        const p = parseCidr(redCidr.value);
        if (!p) { errorRed.textContent = 'CIDR invalido. Ej: 172.20.0.0/24'; return; }
        red.value = num2ip(p.network);
        final.value = num2ip(p.lastUsable);

        let ok = true;
        if (!ipv4Regex.test(gateway.value)) { errorGateway.textContent = 'IP GATEWAY invalida'; ok = false; }
        if (ok) {
            const r = ip2num(red.value), g = ip2num(gateway.value), f = ip2num(final.value);
            if (r >= f) { errorFinal.textContent = 'FINAL debe ser mayor que RED (revisa CIDR)'; ok = false; }
            else if (g < r || g > f) { errorGateway.textContent = 'GATEWAY debe estar dentro del CIDR'; ok = false; }
        }
        if (ok) {
            const url = base_url + 'rangoip/registrar';
            insertarRegistros(url, this, tblIp, btnAccion, false);
        }
    });
})

function eliminarRangoIp(idip) {
    const url = base_url + 'rangoip/eliminar/' + idip;
    eliminarRegistros(url, tblIp);
}

function editarRangoIp(idip) {
    errorRed.textContent = '';
    errorFinal.textContent='';
    const url = base_url + 'rangoip/editar/' + idip;
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
            id.value = res.id;
            red.value = res.red;
            gateway.value = res.gateway || res.red;
            final.value = res.final;
            finalDisplay.value = res.final;
            zona.value = res.id_zona;
            var sm = document.getElementById('id_mikrotik');
            if (sm) sm.value = (res.id_mikrotik !== null && res.id_mikrotik !== undefined) ? res.id_mikrotik : '';
            const inferred = inferCidr(res.red, res.final);
            redCidr.value = inferred || (res.red + '   final: ' + res.final);
            rangoInfo.textContent = inferred ? 'Mascara inferida' : 'Sin CIDR exacto (rango legacy)';

            // Bloqueo si el rango tiene clientes activos
            const hasClientes = (res.total_clientes || 0) > 0;
            redCidr.readOnly = hasClientes;
            zona.disabled = hasClientes;
            redCidr.classList.toggle('bg-light', hasClientes);
            const old = document.getElementById('avisoClientes');
            if (old) old.remove();
            if (hasClientes) {
                const div = document.createElement('div');
                div.id = 'avisoClientes';
                div.className = 'alert alert-warning mt-2 mb-0';
                div.innerHTML = '<i class=\"bx bx-info-circle\"></i> Este rango tiene <strong>' + res.total_clientes + '</strong> cliente(s) activo(s). Solo se puede modificar el <strong>Gateway</strong>.';
                formulario.appendChild(div);
            }

            btnAccion.textContent = 'Actualizar';
            if (typeof window.abrirModalRangoIp === 'function') { window.abrirModalRangoIp(); }
            else { firstTab.show(); }
        }
    }
}

