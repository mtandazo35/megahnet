const formulario = document.querySelector('#formulario');
const correo = document.querySelector('#correo');
const clave = document.querySelector('#clave');

const errorCorreo = document.querySelector('#errorCorreo');
const errorClave = document.querySelector('#errorClave');
const conectar = document.querySelector('#conectar');


document.addEventListener('DOMContentLoaded', function () {
    formulario.addEventListener('submit', function (e) {
        e.preventDefault();
        errorCorreo.textContent = '';
        errorClave.textContent = '';
        if (correo.value == '') {
            errorCorreo.textContent = 'EL CORREO ES REQUERIDO';
        } else if (clave.value == '') {
            errorClave.textContent = 'LA CONTRASEÑA ES REQUERIDO';
        } else {
            const url = base_url + 'principal/validar';
            //crear formData
            const data = new FormData(this);
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
                    if (res.type == 'success') {
                        window.location = base_url + 'admin';
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
            }
        }
    });
    
  

})