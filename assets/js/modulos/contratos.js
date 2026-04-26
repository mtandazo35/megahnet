let divLoading = document.querySelector("#divLoading");
let tblcontratosSuspender

const tblNuevoContrato = document.querySelector('#tblNuevoContrato tbody')

const id = document.querySelector('#id')

const idCliente = document.querySelector('#idCliente')
const telefonoCliente = document.querySelector('#telefonoCliente')
const direccionCliente = document.querySelector('#direccionCliente')
const buscarCliente = document.querySelector('#buscarCliente')
const btnFacturarContratos = document.querySelector('#btnFacturarContratos')

const cargarDatosExcel = document.querySelector('#cargarDatosExcel');
const btnCargar = document.querySelector('#btnCargar');
const excel = document.querySelector('#excel');
const errorExcel = document.querySelector('#errorExcel');

var valor = 0;
const idIp = document.querySelector('#idIp')

const ipUsuario = document.querySelector('#ipUsuario')
const origenIp = document.querySelector('#origenIp')
const idIpAnuladas = document.querySelector('#idIpAnuladas')

const repetidora = document.querySelector('#repetidora')
const ap = document.querySelector('#ap')
const coordenada = document.querySelector('#coordenada')
const direccion = document.querySelector('#direccion')
const comentario = document.querySelector('#comentario')
const medio = document.querySelector('#medio')
const comparticion = document.querySelector('#comparticion')
const anchoBanda = document.querySelector('#anchoBanda')
const discapacidad = document.querySelector('#discapacidad')
const idMikrotik = document.querySelector('#idMikrotik')
const ciudad = document.querySelector('#ciudad')

const chelectronica = document.querySelector('#chelectronica')


const errorCliente = document.querySelector('#errorCliente')

const errorIpUsuario = document.querySelector('#errorIpUsuario')
const errorRepetidora = document.querySelector('#errorRepetidora')
const errorAp = document.querySelector('#errorAp')
const errorCoordenada = document.querySelector('#errorCoordenada')
const errorDireccion = document.querySelector('#errorDireccion')
const errorMedio = document.querySelector('#errorMedio')
const errorAnchoBanda = document.querySelector('#errorAnchoBanda')
const errorCiudad = document.querySelector('#errorCiudad')



const nuevoPagoFactura = document.querySelector('#nuevoPagoFactura')
const modalFacturarContratos = new bootstrap.Modal('#modalFacturarContratos')

const buscarContrato = document.querySelector('#buscarContrato')
const errorBuscarContrato = document.querySelector('#errorBuscarContrato')
const direccionContrato = document.querySelector('#direccionContrato')
const idContrato = document.querySelector('#idContrato')

const chEnero = document.querySelector('#chEnero')
const chFebrero = document.querySelector('#chFebrero')
const chMarzo = document.querySelector('#chMarzo')
const chAbril = document.querySelector('#chAbril')
const chMayo = document.querySelector('#chMayo')
const chJunio = document.querySelector('#chJunio')
const chJulio = document.querySelector('#chJulio')
const chAgosto = document.querySelector('#chAgosto')
const chSeptiembre = document.querySelector('#chSeptiembre')
const chOctubre = document.querySelector('#chOctubre')
const chNoviembre = document.querySelector('#chNoviembre')
const chDiciembre = document.querySelector('#chDiciembre')
const valorContrato = document.querySelector('#valorContrato')
const valorFacturar = document.querySelector('#valorFacturar')

const containerMeses = document.querySelector('#containerMeses')

const modalVerVista = new bootstrap.Modal('#modalVerVista')
const modalUpdateComentario = new bootstrap.Modal('#modalUpdateComentario')
const updateComentarioContrato = document.querySelector('#updateComentarioContrato')

const tipopago = document.querySelector('#tipopago');


document.addEventListener('DOMContentLoaded', function () {
  // Forzar campos vacios al cargar (incluso si el backend precargo $data['clienteNuevo']
  // o quedaron datos del ultimo contrato). El usuario debe buscar el cliente
  // siempre antes de cargar los datos tecnicos.
  // Usar querySelectorAll porque hay IDs duplicados en la vista (rama clienteNuevo / no).
  document.querySelectorAll('#buscarCliente, #telefonoCliente, #direccionCliente, #idCliente').forEach(function(el){
    if ('value' in el) el.value = '';
  });

  // cargar productos de localStorage
  mostrarProducto()

  // autocomplete clientes
  $('#buscarCliente').autocomplete({
    source: function (request, response) {
      $.ajax({
        url: base_url + 'clientes/buscar',
        dataType: 'json',
        data: {
          term: request.term
        },
        success: function (data) {
          response(data)
          if (data.length > 0) {
            errorCliente.textContent = ''
          } else {
            errorCliente.textContent = 'NO HAY CLIENTE CON ESE NOMBRE'
          }
        }
      })
    },
    minLength: 2,
    select: function (event, ui) {
      telefonoCliente.value = ui.item.telefono
      direccionCliente.innerHTML = ui.item.direccion
      idCliente.value = ui.item.id
    }
  })

  // autocomplete clientes
  $('#buscarContrato').autocomplete({
    source: function (request, response) {
      $.ajax({
        url: base_url + 'contratos/buscarContrato',
        dataType: 'json',
        data: {
          term: request.term
        },
        success: function (data) {
          response(data)
          if (data.length > 0) {
            errorBuscarContrato.textContent = ''
          } else {
            errorBuscarContrato.textContent = 'NO HAY CONTRATO CON EL CLIENTE'
          }
        }
      })
    },
    minLength: 2,
    select: function (event, ui) {
      direccionContrato.value = ui.item.direccionContrato
      idContrato.value = ui.item.id
      valorContrato.value = ui.item.total

      if (ui.item.enero == 1) {
        chEnero.checked = true;
        chEnero.value = 2;
        $("input[id='chEnero']").prop("disabled", true)

      } else {
        chEnero.checked = false;
      }


      if (ui.item.febrero == 1) {
        chFebrero.checked = true;
        chFebrero.value = 2;
        $("input[id='chFebrero']").prop("disabled", true)

      } else {
        chFebrero.checked = false;
      }

      if (ui.item.marzo == 1) {
        chMarzo.checked = true;
        chMarzo.value = 2;
        $("input[id='chMarzo']").prop("disabled", true)

      } else {
        chMarzo.checked = false;
      }
      if (ui.item.abril == 1) {
        chAbril.checked = true;
        chAbril.value = 2;
        $("input[id='chAbril']").prop("disabled", true)

      } else {
        chAbril.checked = false;
      }
      if (ui.item.mayo == 1) {
        chMayo.checked = true;
        chMayo.value = 2;
        $("input[id='chMayo']").prop("disabled", true)

      } else {
        chMayo.checked = false;
      }
      if (ui.item.junio == 1) {
        chJunio.checked = true;
        chJunio.value = 2;
        $("input[id='chJunio']").prop("disabled", true)

      } else {
        chJunio.checked = false;
      }
      if (ui.item.julio == 1) {
        chJulio.checked = true;
        chJulio.value = 2;
        $("input[id='chJulio']").prop("disabled", true)

      } else {
        chJulio.checked = false;
      }
      if (ui.item.agosto == 1) {
        chAgosto.checked = true;
        chAgosto.value = 2;
        $("input[id='chAgosto']").prop("disabled", true)

      } else {
        chAgosto.checked = false;
      }
      if (ui.item.septiembre == 1) {
        chSeptiembre.checked = true;
        chSeptiembre.value = 2;
        $("input[id='chSeptiembre']").prop("disabled", true)
      } else {
        chSeptiembre.checked = false;
      }
      if (ui.item.octubre == 1) {
        chOctubre.checked = true;
        chOctubre.value = 2;
        $("input[id='chOctubre']").prop("disabled", true)

      } else {
        chOctubre.checked = false;
      }
      if (ui.item.noviembre == 1) {
        chNoviembre.checked = true;
        chNoviembre.value = 2;
        $("input[id='chNoviembre']").prop("disabled", true)

      } else {
        chNoviembre.checked = false;
      }
      if (ui.item.diciembre == 1) {
        chDiciembre.checked = true;
        chDiciembre.value = 2;
        $("input[id='chDiciembre']").prop("disabled", true)

      } else {
        chDiciembre.checked = false;
      }
    }
  })

  //levantar modal para agregar abono
  nuevoPagoFactura.addEventListener('click', function () {
    idContrato.value = '';
    buscarContrato.value = '';
    errorBuscarContrato.value = '';
    direccionContrato.value = '';
    chEnero.checked = false;
    chFebrero.checked = false;
    chMarzo.checked = false;
    chAbril.checked = false;
    chMayo.checked = false;
    chJunio.checked = false;
    chJulio.checked = false;
    chAgosto.checked = false;
    chSeptiembre.checked = false;
    chOctubre.checked = false;
    chNoviembre.checked = false;
    chDiciembre.checked = false;
    valorContrato.value = 0;
    valorFacturar.value = 0;
    modalFacturarContratos.show();

  })

  btnFacturarContratos.addEventListener('click', function () {

    if (buscarContrato.value == '') {
      alertaPersonalizada('warning', 'NO SE AH SELECIONADO UN CONTRATO');
    } else if (idContrato.value == '') {
      alertaPersonalizada('warning', 'NO EXISTE ID CONTRATO SELECCIONADO');
    } else {
      const url = base_url + 'automaticas/facturarContrato';
      //hacer una instancia del objeto XMLHttpRequest 
      const http = new XMLHttpRequest();
      //Abrir una Conexion - POST - GET
      http.open('POST', url, true);
      //Enviar Datos
      http.send(JSON.stringify({
        idContrato: idContrato.value,
        enero: chEnero.value,
        febrero: chFebrero.value,
        marzo: chMarzo.value,
        abril: chAbril.value,
        mayo: chMayo.value,
        junio: chJunio.value,
        julio: chJulio.value,
        agosto: chAgosto.value,
        septiembre: chSeptiembre.value,
        octubre: chOctubre.value,
        noviembre: chNoviembre.value,
        diciembre: chDiciembre.value,
        total: valorFacturar.value,
        tipoPago: tipopago.value


      }));
      //verificar estados
      http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
          const res = JSON.parse(this.responseText)
          console.log(this.responseText)
          alertaPersonalizada(res.type, res.msg)
          if (res.type == 'success') {
            if (res.factura == 'ordenVenta') {
              localStorage.removeItem(nombreKey)
              setTimeout(() => {
                Swal.fire({
                  icon: 'success',
                  title: 'IMPRIMIR ORDEN VENTA?',
                  showCancelButton: true,
                  confirmButtonText: 'ENVIO POR WHATSAPP'
                }).then((result) => {
                  /* Read more about isConfirmed, isDenied below */
                  if (result.isConfirmed) {
                    const ruta = base_url + 'ordenventa/reporte/factura/' + res.idVenta;
                    //  window.open(ruta, '_blank');
                    const whatsapp = res.whatsapp;
                    window.open(whatsapp, '_blank');
                  }
                  window.location.reload()
                })
              }, 2000)
            } else {
              localStorage.removeItem(nombreKey)
              setTimeout(() => {
                Swal.fire({
                  icon: 'success',
                  title: 'IMPRIMIR FACTURA ELECTRONICA?',
                  showDenyButton: true,
                  showCancelButton: true,
                  confirmButtonText: 'Ticked',
                  denyButtonText: `Factura`
                }).then((result) => {
                  /* Read more about isConfirmed, isDenied below */
                  if (result.isConfirmed) {
                    const ruta = base_url + 'ventas/facturaTicked/' + res.ClaveAcceso + '/' + res.idVenta
                    //  window.open(ruta, '_blank')
                    const whatsapp = res.whatsapp;
                    window.open(whatsapp, '_blank');
                  } else if (result.isDenied) {
                    const ruta = base_url + 'facturaelectronica/public/archivos/ride/' + res.ClaveAcceso + '.pdf'
                    //  window.open(ruta, '_blank')
                    const whatsapp = res.whatsapp;
                    window.open(whatsapp, '_blank');
                  }
                  window.location.reload()
                })
              }, 2000)
            }
          }
        }
      }
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
    if (archivo.files[0] == null || archivo.files[0] == '') {
      alertaPersonalizada('error', 'SELECCIONE UN ARCHIVO EXCEL');

    } else if (archivo.files[0].type != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
      alertaPersonalizada('error', 'SOLO SE ADMITE FORMATO EXCEL .XLSX');

      // errorExcel.textContent = 'SELECCIONES UN ARCHIVO EXCEL';
    }
    else {
      const url = base_url + 'contratos/registrarExcel';
      insertarRegistros(url, this, tblHistorial, btnCargar, false);
    }

  })

  // completar cotizacion
  btnAccion.addEventListener('click', function () {

    if (chelectronica.checked) {
      chelectronica.value = 1;
    } else {
      chelectronica.value = 0;
    }
    let filas = document.querySelectorAll('#tblNuevoContrato tr').length
    if (filas < 2) {
      alertaPersonalizada('warning', 'CARRITO VACIO')
      return
    } else if (ipUsuario.value == '') {
      alertaPersonalizada('warning', 'LA IP USUARIO ES REQUERIDO')
      return
    } else if (repetidora.value == '') {
      alertaPersonalizada('warning', 'LA REPETIDORA ES REQUERIDO')
      return
    } else if (ap.value == '') {
      alertaPersonalizada('warning', 'LA AP ES REQUERIDO')
      return
    } else if (coordenada.value == '') {
      alertaPersonalizada('warning', 'LA COORDENADA ES REQUERIDO')
      return
    } else if (direccion.value == '') {
      alertaPersonalizada('warning', 'LA DIRECCIÓN ES REQUERIDO')
      return
    } else if (medio.value == '') {
      alertaPersonalizada('warning', 'EL MEDIO TX/RX ES REQUERIDO')
      return
    } else if (comparticion.value == '') {
      alertaPersonalizada('warning', 'LA COMPARTICIÓN ES REQUERIDO')
      return
    } else if (anchoBanda.value == '') {
      alertaPersonalizada('warning', 'EL ANCHO DE BANDA ES REQUERIDO')
      return
    }

    else if (idCliente.value == '') {
      alertaPersonalizada('warning', 'EL CLIENTE ES REQUERIDO')
      return
    } else {
      const url = base_url + 'contratos/registrarContratos'
      // hacer una instancia del objeto XMLHttpRequest 
      const http = new XMLHttpRequest()
      // Abrir una Conexion - POST - GET
      http.open('POST', url, true)
      // Enviar Datos
      http.send(JSON.stringify({
        productos: listaCarrito,
        id: id.value,
        idCliente: idCliente.value,
        idIp: idIp.value,
        idIpAnuladas: idIpAnuladas.value,
        idZona: idZonas.value,

        ipUsuario: ipUsuario.value,
        origenIp: origenIp.value,
        repetidora: repetidora.value,
        ap: ap.value,
        coordenada: coordenada.value,
        direccion: direccion.value,
        comentario: comentario.value,
        medio: medio.value,
        comparticion: comparticion.value,
        anchoBanda: anchoBanda.value,
        idMikrotik: idMikrotik.value,
        discapacidad: discapacidad.value,
        ciudad: ciudad.value,

        chelectronica: chelectronica.value,
        //tipoBanco: tipoBanco.value,
        //cuentaBancaria: cuentaBancaria.value,
      }))
      // verificar estados
      http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
          const res = JSON.parse(this.responseText)
          alertaPersonalizada(res.type, res.msg)
          if (res.type == 'success') {
            localStorage.removeItem(nombreKey)
            setTimeout(() => {
              Swal.fire({
                title: 'IMPRIMIR CONTRATO?',
                text: 'Generando',
                confirmButtonText: 'Contrato',
                icon: 'success',
                showCancelButton: true,
              }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                  const ruta = base_url + 'contratos/reporte/facturasincss/' + res.idContrato
                  window.open(ruta, '_blank')
                }
                window.location.reload()
              })
            }, 2000)
          }
        }
      }
    }
  })

  $('#tblHistorial').DataTable({
    deferRender: true,
    pageLength: 25,
    colReorder: true,
    stateSave: true,
    stateDuration: 60 * 60 * 24 * 30,
    
    processing: true,
    serverSide: true,
    ajax: {
      url: base_url + 'contratos/listar',
      type: 'POST'
    },
    columns: [
      { data: 'acciones' },
      { data: 'nombre' },
      { data: 'ipUsuario' },
      { data: 'repetidora' },
      { data: 'Ap' },
      { data: 'deudaTotal' },
      { data: 'abonosMes' },
      { data: 'total' },
      { data: 'telefonoCliente' },
      { data: 'comentario' },
      { data: 'tributario' }

    ],
    language: {
      url: base_url + 'assets/js/espanol.json'
    },
    dom,
    buttons,
    order: [[0, 'desc']]
  });




  // cargar datos con el plugin datatables
  /*tblHistorial = $('#tblHistorial').DataTable({
    deferRender: true,
    pageLength: 25,
    
    ajax: {
      url: base_url + 'contratos/listar',
      dataSrc: ''
    },
    columns: [
      { data: 'nombre' },
      { data: 'ipUsuario' },
      { data: 'repetidora' },
      { data: 'Ap' },
      { data: 'deudaTotal' },
      { data: 'total' },
      { data: 'telefonoCliente' },          
      { data: 'tributario' },
      { data: 'acciones' }
    ],
    language: {
      url: base_url + 'assets/js/espanol.json'
    },
    dom,
    buttons,
    order: [[0, 'desc']]
  })*/

  // cargar datos con el plugin datatables
  tblcontratosSuspender = $('#tblcontratosSuspender').DataTable({
    deferRender: true,
    pageLength: 25,
    processing: true,
    serverSide: true,
    ajax: {
      url: base_url + 'contratos/listarContratosSuspender',
      type: 'POST'
    },
    columns: [
      { data: 'acciones' },
      { data: 'nombre' },
      { data: 'deudaTotal' },
      { data: 'ip_usuario' },
      { data: 'telefonoCliente' }

    ],
    language: {
      url: base_url + 'assets/js/espanol.json'
    },
    dom,
    buttons,
    order: [[0, 'desc']]
  })
})

// cargar productos
function mostrarProducto() {
  if (localStorage.getItem(nombreKey) != null) {
    const url = base_url + 'productos/mostrarDatos'
    // hacer una instancia del objeto XMLHttpRequest 
    const http = new XMLHttpRequest()
    // Abrir una Conexion - POST - GET
    http.open('POST', url, true)
    // Enviar Datos
    http.send(JSON.stringify(listaCarrito))
    // verificar estados
    http.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        const res = JSON.parse(this.responseText)
        let html = ''
        if (res.productos.length > 0) {
          //          <td>${producto.precio_venta}</td>

          res.productos.forEach(producto => {
            html += `<tr>
                           <td>
                            <input style="width:800px"; type="text" class="form-control inputDescripcion" data-id="${producto.id}" value="${producto.nombre}">
                            </td>
                            <td>
                            <input style="width:125px"; type="number" class="form-control inputPrecio" data-id="${producto.id}" value="${producto.precio_venta}">
                            </td>
                            <td>${producto.subTotalVenta}</td>
                            <td><button class="btn btn-danger btnEliminar" data-id="${producto.id}" type="button"><i class="fas fa-trash"></i></button></td>
                        </tr>`
          })
          tblNuevoContrato.innerHTML = html
          totalPagar.value = res.totalVenta
          btnEliminarProducto()
          agregarCantidad()
          agregarPrecioVenta()
          agregarDescripcion()


        } else {
          tblNuevoContrato.innerHTML = ''
        }
      }
    }
  } else {
    tblNuevoContrato.innerHTML = `<tr>
            <td colspan="4" class="text-center">CARRITO VACIO</td>
        </tr>`
  }
}

function verReporte(idContrato) {
  Swal.fire({
    title: 'REEMPRIMIR CONTRATO?',
    text: 'Generando',
    icon: 'success',
    showCancelButton: true,
    confirmButtonText: 'Contrato',
  }).then((result) => {
    /* Read more about isConfirmed, isDenied below */
    if (result.isConfirmed) {
      const ruta = base_url + 'contratos/reporte/facturasincss/' + idContrato
      window.open(ruta, '_blank')
    } else if (result.isDenied) {
      const ruta = base_url + 'contratos/reporte/facturasincss/' + idContrato
      window.open(ruta, '_blank')
    }
  })
}

function eliminarContrato(idContrato) {
  const url = base_url + 'contratos/eliminar/' + idContrato;
  eliminarRegistros(url, tblHistorial);
}
function suspenderContrato(idContrato) {
  const url = base_url + 'contratos/suspender/' + idContrato;
  suspenderContratos(url, tblcontratosSuspender);
}
function Editar(idContrato) {
  //limpiarCampos();
  // editorDireccion.setData('');

  const url = base_url + 'contratos/editar/' + idContrato;
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
      agregarProducto(res.producto[0].id, res.producto[0].cantidad, res.producto[0].stockActual, res.producto[0].precio, res.producto[0].nombre, res.producto[0].id_categoria);
      //console.log(res.producto);
      id.value = res.id;
      localStorage.removeItem(nombreKey)

      //listaCarrito.value = res.productos;
      idCliente.value = res.id_cliente;
      ipUsuario.value = res.ip_usuario;
      buscarCliente.value = res.nombre;
      telefonoCliente.value = res.telefono;
      direccionCliente.value = res.direccionCliente;
      repetidora.value = res.repetidora;
      ap.value = res.ap;
      coordenada.value = res.coordenada;
      direccion.value = res.direccion;
      comentario.value = res.comentario;
      medio.value = res.medio;
      comparticion.value = res.comparticion;
      anchoBanda.value = res.ancho_banda;
      idMikrotik.value = res.id_mikrotik;
      discapacidad.value = res.discapacidad;
            ciudad.value = res.ciudad;

      if (res.factura == 1) {
        $('#chelectronica').attr('checked', true);
      } else {
        $('#chelectronica').attr('checked', false);
      }


      // editorDireccion.setData(res.direccion);
      btnAccion.textContent = 'Actualizar';
      firstTab.show()
    }
  }
}


function enviarMsm(idContrato) {
  //limpiarCampos();
  // editorDireccion.setData('');

  const url = base_url + 'contratos/enviarMsm/' + idContrato;
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

      setTimeout(() => {
        const whatsapp = res.resWhatsapp;

        // window.open(ruta, '_blank');
        window.open(whatsapp, '_blank');

      }, 2000);

    }
  }
}


function verVista(idContrato) {
  // editorDireccion.setData('');

  modalVerVista.show();
  const url = base_url + 'contratos/editar/' + idContrato;
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
      document.querySelector('#modalId').innerHTML = res.id
      document.querySelector('#modalFecha').innerHTML = res.fecha
      document.querySelector('#modalCliente').innerHTML = res.nombre
      document.querySelector('#modalServicio').innerHTML = res.producto[0]['nombre']
      document.querySelector('#modalValor').innerHTML = res.total

      document.querySelector('#modalIpUsuario').innerHTML = res.ip_usuario
      document.querySelector('#modalRepetidora').innerHTML = res.repetidora
      document.querySelector('#modalAp').innerHTML = res.ap
      document.querySelector('#modalCoordenada').innerHTML = res.coordenada
      document.querySelector('#modalDireccionContrato').innerHTML = res.direccion
      document.querySelector('#modalComentario').innerHTML = res.comentario
      document.querySelector('#modalMedio').innerHTML = res.medio
      document.querySelector('#modalComparticion').innerHTML = res.comparticion

      if (res.factura == 1) {
        document.querySelector('#modalTributario').innerHTML = 'FACTURA'

      } else {
        document.querySelector('#modalTributario').innerHTML = 'ORDEN VENTA'

      }
      document.querySelector('#modalTipoBanco').innerHTML = res.tipo_banco
      document.querySelector('#modalCuentaBanco').innerHTML = res.cuenta_banco

      document.querySelector('#modalDeudaTotal').innerHTML = '$' + res.deudaTotal

      document.querySelector('#modalDeudaContrato').innerHTML = '$' + res.deudaContrato





    }
  }
}


function updateComentario(idContrato) {
  // editorDireccion.setData('');

  modalUpdateComentario.show();
  const url = base_url + 'contratos/editar/' + idContrato;
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
      document.querySelector('#updateComentarioContrato').value = res.comentario

    }
  }


  // Obtener el botón de "Actualizar"
  const btnActualizar = document.getElementById('enviarDatos');

  // Añadir evento de clic al botón para actualizar el comentario
  btnActualizar.onclick = function () {
    const nuevoComentario = document.getElementById('updateComentarioContrato').value; // Obtener el nuevo comentario desde el input

    // Crear la solicitud para actualizar el comentario en el servidor
    const updateUrl = base_url + 'contratos/updateComentario'; // Aquí la URL de la API de actualización
    const updateHttp = new XMLHttpRequest();
    updateHttp.open('POST', updateUrl, true);
    updateHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Enviar los datos del comentario actualizado
    updateHttp.send(`idContrato=${idContrato}&comentario=${encodeURIComponent(nuevoComentario)}`);

    updateHttp.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        const res = JSON.parse(this.responseText);
        if (res.type == 'success') {
          // Si la actualización fue exitosa, cerrar el modal y mostrar un mensaje de éxito
          alertaPersonalizada(res.type, res.msg)

          modalUpdateComentario.hide(); // Cerrar el modal
          setTimeout(function () {
            location.reload();
          }, 3000); // Recarga la página después de 5 segundos (5000 milisegundos)

        } else {
          alertaPersonalizada(res.type, res.msg)
          modalUpdateComentario.hide(); // Cerrar el modal
        }
      }
    };
  };



}


function limpiarCampos() {
  // Para inputs/select se usa .value, NO .textContent (eso era el bug que dejaba
  // los datos del contrato anterior cargados en el form 'Nuevo').
  function clr(el, val) {
    if (!el) return;
    if ('value' in el) el.value = (val === undefined) ? '' : val;
    else el.textContent = '';
  }

  // Hidden id + carrito en localStorage
  clr(id);
  if (typeof nombreKey !== 'undefined') localStorage.removeItem(nombreKey);

  // Cliente
  clr(idCliente);
  clr(buscarCliente);
  clr(telefonoCliente);
  clr(direccionCliente);

  // Tecnicos del contrato
  clr(idIp);
  clr(ipUsuario);
  clr(repetidora);
  clr(ap);
  clr(coordenada);
  clr(direccion);
  clr(comentario);
  clr(ciudad);
  clr(anchoBanda);

  // Selects: dejar en "Seleccionar"
  if (medio        && medio.options       && medio.options.length)        medio.selectedIndex = 0;
  if (comparticion && comparticion.options && comparticion.options.length) comparticion.selectedIndex = 0;
  if (idMikrotik   && idMikrotik.options   && idMikrotik.options.length)   idMikrotik.selectedIndex = 0;
  if (discapacidad && discapacidad.options && discapacidad.options.length) discapacidad.selectedIndex = 0;
  // Switch facturacion electronica desactivado
  if (chelectronica) chelectronica.checked = false;

  // Errores visibles
  ['errorCliente','errorIpUsuario','errorRepetidora','errorAp','errorCoordenada',
   'errorDireccion','errorMedio','errorAnchoBanda','errorCiudad'].forEach(function(eid){
    var e = document.getElementById(eid); if (e) e.textContent = '';
  });

  // Tabla de productos del contrato y total
  if (tblNuevoContrato) tblNuevoContrato.innerHTML = '';
  var tp = document.getElementById('totalPagar');     if (tp) tp.value = '';
  var th = document.getElementById('totalPagarHidden'); if (th) th.value = '';
  var bt = document.getElementById('btnAccion'); if (bt) bt.textContent = 'Completar Contrato';
}

// Hook: al activar el tab 'Nuevo' siempre dejar los campos vacios.
// (Solo si el usuario llega al tab manualmente, NO durante una edicion
// que usa firstTab.show() despues de cargar los datos.)
document.addEventListener('DOMContentLoaded', function () {
  var tab = document.getElementById('nav-nuevo-tab');
  if (!tab) return;
  tab.addEventListener('click', function () {
    // Si btnAccion dice 'Actualizar', el usuario esta editando (no limpiar)
    var bt = document.getElementById('btnAccion');
    if (bt && bt.textContent.trim().toLowerCase().indexOf('actualizar') !== -1) return;
    if (typeof limpiarCampos === 'function') limpiarCampos();
  });
});

function calcularEnero() {
  if (chEnero.checked == true && chEnero.value != 2) {
    valor = parseFloat(valor) + parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
    document.querySelector('#chEnero').value = 1

  } else { //if (chEnero.checked == false && chEnero.value != 2)
    valor = parseFloat(valorFacturar.value) - parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')

  }
}

function calcularFebrero() {
  if (chFebrero.checked == true && chFebrero.value != 2) {
    valor = parseFloat(valor) + parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
    document.querySelector('#chFebrero').value = 1

  } else { //if (chFebrero.checked == false && chFebrero.value != 2)
    valor = parseFloat(valorFacturar.value) - parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
  }
}
function calcularMarzo() {
  if (chMarzo.checked == true && chMarzo.value != 2) {
    valor = parseFloat(valor) + parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
    document.querySelector('#chMarzo').value = 1

  } else { //if (chMarzo.checked == false && chMarzo.value != 2)
    valor = parseFloat(valorFacturar.value) - parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
  }
}
function calcularAbril() {
  if (chAbril.checked == true && chAbril.value != 2) {
    valor = parseFloat(valor) + parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
    document.querySelector('#chAbril').value = 1

  } else { //if (chAbril.checked == false && chAbril.value != 2)
    valor = parseFloat(valorFacturar.value) - parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
  }
}
function calcularMayo() {
  if (chMayo.checked == true && chMayo.value != 2) {
    valor = parseFloat(valor) + parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
    document.querySelector('#chMayo').value = 1

  } else { //if (chMayo.checked == false && chMayo.value != 2)
    valor = parseFloat(valorFacturar.value) - parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
  }
}
function calcularJunio() {
  if (chJunio.checked == true && chJunio.value != 2) {
    valor = parseFloat(valor) + parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
    document.querySelector('#chJunio').value = 1

  } else { //if (chJunio.checked == false && chJunio.value != 2)
    valor = parseFloat(valorFacturar.value) - parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
  }

}
function calcularJulio() {
  if (chJulio.checked == true && chJulio.value != 2) {
    valor = parseFloat(valor) + parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
    document.querySelector('#chJulio').value = 1

  } else { //if (chJunio.checked == false && chJunio.value != 2)
    valor = parseFloat(valorFacturar.value) - parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
  }

}
function calcularAgosto() {
  if (chAgosto.checked == true && chAgosto.value != 2) {
    valor = parseFloat(valor) + parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
    document.querySelector('#chAgosto').value = 1

  } else { //if (chJunio.checked == false && chJunio.value != 2)
    valor = parseFloat(valorFacturar.value) - parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
  }

}
function calcularSeptiembre() {
  if (chSeptiembre.checked == true && chSeptiembre.value != 2) {
    valor = parseFloat(valor) + parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
    document.querySelector('#chSeptiembre').value = 1


  } else { //if (chJunio.checked == false && chJunio.value != 2)
    valor = parseFloat(valorFacturar.value) - parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
  }


}
function calcularOctubre() {
  if (chOctubre.checked == true && chOctubre.value != 2) {
    valor = parseFloat(valor) + parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
    document.querySelector('#chOctubre').value = 1

  } else { //if (chJunio.checked == false && chJunio.value != 2)
    valor = parseFloat(valorFacturar.value) - parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
  }

}
function calcularNoviembre() {
  if (chNoviembre.checked == true && chNoviembre.value != 2) {
    valor = parseFloat(valor) + parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
    document.querySelector('#chNoviembre').value = 1

  } else { //if (chJunio.checked == false && chJunio.value != 2)
    valor = parseFloat(valorFacturar.value) - parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
  }

}
function calcularDiciembre() {
  if (chDiciembre.checked == true && chDiciembre.value != 2) {
    valor = parseFloat(valor) + parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
    document.querySelector('#chDiciembre').value = 1

  } else { //if (chJunio.checked == false && chJunio.value != 2)
    valor = parseFloat(valorFacturar.value) - parseFloat(valorContrato.value);
    document.querySelector('#valorFacturar').value = valor.toLocaleString('en-US')
  }

}

function seleccionarip() {
  var idzona = document.querySelector('#idZonas').value

  const url = base_url + 'contratos/buscarip/' + idzona;
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
      //console.log(res[0]['ip']['ip']);

      if (res[0].origen == 'NUEVA') {

        ipUsuario.value = res[0].ipUsuario;
        origenIp.value = res[0].origen;
        idIp.value = res[0]['ip'].id;
        idIpAnuladas.value = null;

      } else {
        ipUsuario.value = res[0]['ip']['ip'];
        origenIp.value = res[0].origen;
        idIp.value = res[0]['ip'].id_ip;
        idIpAnuladas.value = res[0]['ip'].id;


      }



    }
  }
}

function seleccionaripRepetidora() {
  var idRepetidora = document.querySelector('#repetidora').value



  const url = base_url + 'contratos/buscarRepetidora?valor=' + encodeURIComponent(idRepetidora);
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
      //console.log(res[0]['ip']['ip']);

      ap.value = res.ip;

    }


  }
}

function restaurarContrato(idContrato) {
  const url = base_url + 'contratos/restaurar/' + idContrato;
  restaurarRegistros(url, tblHistorial);
}