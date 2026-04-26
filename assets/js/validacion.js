function validarNumero(evt) {
    var key = evt.keyCode || evt.which;
    var tecla = String.fromCharCode(key);
    var regexp = /[0-9]/;
    if (!regexp.test(tecla)) {
        evt.preventDefault();
    }
}
function validarLetras(evt) {
    var key = evt.keyCode || evt.which;
    var tecla = String.fromCharCode(key);
    var regexp = /^[a-zA-Z\s]+$/; // Acepta letras y espacios  
    if (!regexp.test(tecla)) {
        evt.preventDefault();
    }
}

function validarNumeroYDecimal(evt) {
    var key = evt.keyCode || evt.which;
    var tecla = String.fromCharCode(key);
    var campo = evt.target.value;
    var regexp = /^[0-9.]$/;
    
    // Permitir el punto solo si no hay uno ya presente en la cadena
    if (tecla === '.') {
        if (campo.includes('.')) {
            evt.preventDefault();
        }
    }
    
    if (!regexp.test(tecla)) {
        evt.preventDefault();
    }
}