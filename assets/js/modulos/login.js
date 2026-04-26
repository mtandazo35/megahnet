const formulario = document.querySelector('#formulario');
const correo = document.querySelector('#correo');
const clave = document.querySelector('#clave');

const errorCorreo = document.querySelector('#errorCorreo');
const errorClave = document.querySelector('#errorClave');
const conectar = document.querySelector('#conectar');


document.addEventListener('DOMContentLoaded', function () {
    const submitBtn = formulario.querySelector('button[type="submit"]');
    const submitOriginalHTML = submitBtn ? submitBtn.innerHTML : '';

    function setLoading(on) {
        if (!submitBtn) return;
        if (on) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Validando…';
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = submitOriginalHTML;
        }
    }

    formulario.addEventListener('submit', function (e) {
        e.preventDefault();
        errorCorreo.textContent = '';
        errorClave.textContent = '';
        if (correo.value == '') {
            errorCorreo.textContent = 'EL CORREO ES REQUERIDO';
        } else if (clave.value == '') {
            errorClave.textContent = 'LA CONTRASEÑA ES REQUERIDO';
        } else {
            setLoading(true);
            const url = base_url + 'principal/validar';
            const data = new FormData(this);
            const http = new XMLHttpRequest();
            http.open('POST', url, true);
            http.send(data);
            http.onreadystatechange = function () {
                if (this.readyState == 4) {
                    if (this.status == 200) {
                        const res = JSON.parse(this.responseText);
                        if (res.type == 'success') {
                            // Mantener loading hasta que el redirect termine
                            window.location = base_url + 'admin';
                            return;
                        } else {
                            Swal.fire({
                                toast: true,
                                position: 'top-right',
                                icon: res.type,
                                title: res.msg,
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    }
                    setLoading(false);
                }
            }
        }
    });
})