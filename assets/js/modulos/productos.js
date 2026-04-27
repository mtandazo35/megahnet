let divLoading = document.querySelector("#divLoading");

let tblProductos;
const formulario = document.querySelector('#formulario');
const btnAccion = document.querySelector('#btnAccion');
const btnNuevo = document.querySelector('#btnNuevo');

const cargarDatosExcel = document.querySelector('#cargarDatosExcel');
const btnCargar = document.querySelector('#btnCargar');
const excel = document.querySelector('#excel');
const errorExcel = document.querySelector('#errorExcel');

const id = document.querySelector('#id');
const codigo = document.querySelector('#codigo');
const nombre = document.querySelector('#nombre');
const precio_compra = document.querySelector('#precio_compra');
const precio_venta = document.querySelector('#precio_venta');
//const id_medida = document.querySelector('#id_medida');
const id_categoria = document.querySelector('#id_categoria');
const foto = document.querySelector('#foto');
const foto_actual = document.querySelector('#foto_actual');
const iva = document.querySelector('#id_iva');
const containerPreview = document.querySelector('#containerPreview');

const errorCodigo = document.querySelector('#errorCodigo');
const errorNombre = document.querySelector('#errorNombre');
const errorCompra = document.querySelector('#errorCompra');
const errorVenta = document.querySelector('#errorVenta');
const errorMedida = document.querySelector('#errorMedida');
const errorCategoria = document.querySelector('#errorCategoria');
const errorIva = document.querySelector('#errorIva');


document.addEventListener('DOMContentLoaded', function () {
    //cargar datos con el plugin datatables
    tblProductos = $('#tblProductos').DataTable({
    deferRender: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'productos/listar',
            dataSrc: ''
        },
        columns: [
		  { data: 'acciones' },
            { data: 'descripcion' },

            { data: 'codigo' },
            { data: 'precio_compra' },
            { data: 'precio_venta' },
            { data: 'cantidad' },
           // { data: 'medida' },
            { data: 'categoria' },
            { data: 'imagen' }
          
        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        order: [[1, 'asc']],
    });
    //vista Previa
    foto.addEventListener('change', function (e) {
        foto_actual.value = '';
        if (e.target.files[0].type == 'image/png' ||
            e.target.files[0].type == 'image/jpg' ||
            e.target.files[0].type == 'image/jpeg') {
            const url = e.target.files[0];
            const tmpUrl = URL.createObjectURL(url);
            containerPreview.innerHTML = `<img class="img-thumbnail"  src="${tmpUrl}" width="50%">
            <button class="btn btn-danger" style="width: 50%; margin-top: 5px;" type="button" onclick="deleteImg()"><i class="fas fa-trash"></i></button>`;
        } else {
            foto.value = '';
            alertaPersonalizada('warning', 'SOLO SE PERMITEN IMG DE TIPO PNG-JPG-JPEG');
        }
    })

    //limpiar campos
    btnNuevo.addEventListener('click', function () {
        id.value = '';
        btnAccion.textContent = 'Registrar';
        formulario.reset();
        deleteImg();
        limpiarCampos();

    })
    //registrar Productos
    formulario.addEventListener('submit', function (e) {
        e.preventDefault();
        limpiarCampos();
        if (codigo.value == '') {
            errorCodigo.textContent = 'EL CODIGO ES REQUERIDO';
        } else if (nombre.value == '') {
            errorNombre.textContent = 'EL NOMBRE ES REQUERIDO';
        } else if (iva.value == '') {
            errorIva.textContent = 'EL VALOR TRIBUTARIO ES REQUERIDO';
        } else if (precio_venta.value == '') {
            errorVenta.textContent = 'EL PRECIO VENTA ES REQUERIDO';
        }else if (id_categoria.value == '') {
            errorCategoria.textContent = 'SELECCIONA LA CATEGORIA';
        } else {
            const url = base_url + 'productos/registrar';
            insertarRegistros(url, this, tblProductos, btnAccion, false);
            //limpiarCampos();
        }
    })
    cargarDatosExcel.addEventListener('submit', function (e) {
        e.preventDefault();
       // limpiarCampos();
       // editorDireccion.setData('');
       var archivo = document.getElementById('excel');
	   // var archivoRuta = archivo.value;
       // console.log(archivo.files[0].type);

       //alert(archivo.files);
       if(archivo.files[0] == null || archivo.files[0] == ''){
        alertaPersonalizada('error', 'SELECCIONE UN ARCHIVO EXCEL');

    }else if (archivo.files[0].type != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            alertaPersonalizada('error', 'SOLO SE ADMITE FORMATO EXCEL .XLSX');

           // errorExcel.textContent = 'SELECCIONES UN ARCHIVO EXCEL';
        }     
        else {
            const url = base_url + 'productos/registrarExcel';
            insertarRegistros(url, this, tblProductos, btnCargar, false);
        }

    })
})

function deleteImg() {
    foto.value = '';
    containerPreview.innerHTML = '';
    foto_actual.value = '';
}

function eliminarProducto(idProducto) {
    const url = base_url + 'productos/eliminar/' + idProducto;
    eliminarRegistros(url, tblProductos);
}

function editarProducto(idProducto) {
    limpiarCampos();

    const url = base_url + 'productos/editar/' + idProducto;
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
            codigo.value = res.codigo;
            nombre.value = res.descripcion;
            precio_compra.value = res.precio_compra;
            precio_venta.value = res.precio_venta;
            //id_medida.value = res.id_medida;
            id_categoria.value = res.id_categoria;
            foto_actual.value = res.foto;
            iva.value = res.iva;

            let fotopre = (res.foto == null) ? base_url + 'assets/images/productos/default.png' : base_url + res.foto;


            containerPreview.innerHTML = `<img class="img-thumbnail" src="${fotopre}" width="50%">
            <button style="width: 50%; margin-top: 5px;" class="btn btn-danger" type="button" onclick="deleteImg()"><i class="fas fa-trash"></i></button>`;
            btnAccion.textContent = 'Actualizar';
            if (typeof window.abrirModalProducto === 'function') { window.abrirModalProducto(); }
            else { firstTab.show(); }
        }
    }
}



function limpiarCampos() {
    errorCodigo.textContent = '';
    errorNombre.textContent = '';
    errorCompra.textContent = '';
    errorVenta.textContent = '';
    //errorMedida.textContent = '';
    errorCategoria.textContent = '';
}