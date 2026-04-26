const inputBuscarCodigo = document.querySelector('#buscarProductoCodigo')
const inputBuscarNombre = document.querySelector('#buscarProductoNombre')
const inputBuscarNombreTipoPago = document.querySelector('#buscarTipoPagoNombre')
const inputBuscarRenta = document.querySelector('#buscarRenta')

const barcode = document.querySelector('#barcode')
const nombre = document.querySelector('#nombre')
const nombreTipoPago = document.querySelector('#nombreTipoPago')
const renta = document.querySelector('#renta')

const containerCodigo = document.querySelector('#containerCodigo')
const containerNombre = document.querySelector('#containerNombre')
const containerNombreTipoPago = document.querySelector('#containerNombreTipoPago')
const containerRenta = document.querySelector('#containerRenta')

const btnAccion = document.querySelector('#btnAccion')
const totalPagar = document.querySelector('#totalPagar')
const totalPagarHidden = document.querySelector('#totalPagarHidden')

// para filtro por rango de fechas
const desde = document.querySelector('#desde')
const hasta = document.querySelector('#hasta')

let listaCarrito, tblHistorial

document.addEventListener('DOMContentLoaded', function () {
  // Helpers null-safe (algunos modulos no tienen todos los toggles)
  function showCont(el)  { if (el) el.classList.remove('d-none'); }
  function hideCont(el)  { if (el) el.classList.add('d-none'); }
  function clearAndFocus(el) { if (el) { el.value = ''; el.focus(); } }
  function on(el, evt, fn) { if (el) el.addEventListener(evt, fn); }

  showCont(containerNombreTipoPago);

  // comprobar productos en localStorage
  if (localStorage.getItem(nombreKey) != null) {
    listaCarrito = JSON.parse(localStorage.getItem(nombreKey))
  }

  // mostrar input para la busqueda por nombre
  on(nombre, 'click', function () {
    hideCont(containerNombreTipoPago);
    hideCont(containerCodigo);
    hideCont(containerRenta);
    showCont(containerNombre);
    clearAndFocus(inputBuscarNombre);
  });

  // mostrar input para la busqueda por codigo
  on(barcode, 'click', function () {
    hideCont(containerNombreTipoPago);
    hideCont(containerNombre);
    showCont(containerCodigo);
    hideCont(containerRenta);
    clearAndFocus(inputBuscarCodigo);
  });

  // mostrar input para la busqueda por tipo de pago
  on(nombreTipoPago, 'click', function () {
    hideCont(containerCodigo);
    hideCont(containerNombre);
    hideCont(containerRenta);
    showCont(containerNombreTipoPago);
    clearAndFocus(inputBuscarNombreTipoPago);
  });

  // mostrar input para la busqueda de renta
  on(renta, 'click', function () {
    hideCont(containerCodigo);
    hideCont(containerNombre);
    hideCont(containerNombreTipoPago);
    showCont(containerRenta);
    clearAndFocus(inputBuscarRenta);
  });


  /* inputBuscarCodigo.addEventListener('keyup', function (e) {
     if (e.keyCode === 13) {
       buscarProducto(e.target.value)
     }
     return
   })*/
  // autocomplete productos
  $('#buscarProductoNombre').autocomplete({
    source: function (request, response) {
      $.ajax({
        url: base_url + 'productos/buscarPorNombre',
        dataType: 'json',
        data: {
          term: request.term
        },
        success: function (data) {
          response(data)
        }
      })
    },
    minLength: 2,
    select: function (event, ui) {
      let precio = nombreKey == 'posCompra' ? ui.item.precio_compra : ui.item.precio_venta
      agregarProducto(ui.item.id, 1, ui.item.stock, precio, ui.item.label, ui.item.id_categoria)
      inputBuscarNombre.value = ''
      inputBuscarNombre.focus()
      return false
    }
  })

   $("#buscarProductoNombreNC").autocomplete({
    source: function (request, response) {
      $.ajax({
        url: base_url + "notaCredito/buscarPorNombre",
        dataType: "json",
        data: {
          term: request.term,
          orden_no: orden_no.value
        },
        success: function (data) {
          response(data);
        },
      });
    },
    minLength: 2,
    select: function (event, ui) {
      let precio =
        nombreKey == "posCompra" ? ui.item.precio_compra : ui.item.precio_venta;

      agregarNotaCredito(
        ui.item.id_producto,
        ui.item.codproducto,
        ui.item.orden_no,
        ui.item.item,
        1,
        ui.item.cantidad,
        ui.item.precio_u,
        ui.item.total,
        ui.item.descuento,
        ui.item.iva,
        ui.item.precio_pvp,
        ui.item.por_descuento
      );



      inputBuscarNombreNC.value = "";
      inputBuscarNombreNC.focus();
      return false;
    },
  });

  // autocomplete productos
  $('#buscarProductoCodigo').autocomplete({
    source: function (request, response) {
      $.ajax({
        url: base_url + 'productos/buscarPorCodigo',
        dataType: 'json',
        data: {
          term: request.term
        },
        success: function (data) {
          response(data)
        }
      })
    },
    minLength: 2,
    select: function (event, ui) {
      let precio = nombreKey == 'posCompra' ? ui.item.precio_compra : ui.item.precio_venta
      agregarProducto(ui.item.id, 1, ui.item.stock, precio, ui.item.label, ui.item.id_categoria)
      inputBuscarCodigo.value = ''
      inputBuscarCodigo.focus()
      return false
    }
  })



  $('#buscarTipoPagoNombre').autocomplete({
    source: function (request, response) {
      $.ajax({
        url: base_url + 'productos/buscarPorNombreTipoPago',
        dataType: 'json',
        data: {
          term: request.term
        },
        success: function (data) {
          response(data)
        }
      })
    },
    minLength: 2,
    select: function (event, ui) {
     // let precio = nombreKey == 'posCompra' ? ui.item.precio_compra : ui.item.precio_venta
      agregarTipoPago(ui.item.id, ui.item.label,ui.item.precio,ui.item.codigoComprobante)
      inputBuscarNombreTipoPago.value = ''
      inputBuscarNombreTipoPago.focus()
      return false
    }
  })


  $('#buscarRenta').autocomplete({
    source: function (request, response) {
      $.ajax({
        url: base_url + 'productos/buscarRetencion',
        dataType: 'json',
        data: {
          term: request.term
        },
        success: function (data) {
          response(data)
        }
      })
    },
    minLength: 2,
    select: function (event, ui) {

      if(totalFactura.value == '' ||  totalFactura.value == 0 ){
        alertaPersonalizada('warning', 'DEBE AGREGAR EL VALOR DE LA FACTURA')
        return

      }
     // let precio = nombreKey == 'posCompra' ? ui.item.precio_compra : ui.item.precio_venta
      agregarRetencion(ui.item.id, ui.item.tipo, ui.item.codigo,'','')
      inputBuscarRenta.value = ''
      inputBuscarRenta.focus()
      return false
    }
  })


  // filtro rango de fechas
  on(desde, 'change', function () { if (typeof tblHistorial !== 'undefined' && tblHistorial) tblHistorial.draw(); });
  on(hasta, 'change', function () { if (typeof tblHistorial !== 'undefined' && tblHistorial) tblHistorial.draw(); });

  if (desde && hasta) {
    $.fn.dataTable.ext.search.push(
      function (settings, data, dataIndex) {
        var FilterStart = desde.value
        var FilterEnd = hasta.value
        var DataTableStart = data[2].trim()
        var DataTableEnd = data[2].trim()
        if (FilterStart == '' || FilterEnd == '') {
          return true
        }
        if (DataTableStart >= FilterStart && DataTableEnd <= FilterEnd) {
          return true
        } else {
          return false
        }
      });
  }
})

function buscarProducto(valor) {
  const url = base_url + 'productos/buscarPorCodigo/' + valor
  // hacer una instancia del objeto XMLHttpRequest 
  const http = new XMLHttpRequest()
  // Abrir una Conexion - POST - GET
  http.open('GET', url, true)
  // Enviar Datos
  http.send()
  // verificar estados
  http.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      const res = JSON.parse(this.responseText)
      if (res.estado) {
        let precio = nombreKey == 'posCompra' ? res.datos.precio_compra : res.datos.precio_venta

        agregarProducto(res.datos.id, 1, res.datos.cantidad, precio, res.datos.descripcion,)
      } else {
        alertaPersonalizada('warning', 'CODIGO NO EXISTE')
      }
      inputBuscarCodigo.value = ''
      inputBuscarCodigo.focus()
    }
  }
}

// agregar productos a localStorage
function agregarProducto(idProducto, cantidad, stockActual, precio, nombre, idCategoria) {
  if (localStorage.getItem(nombreKey) == null) {
    listaCarrito = []
  } else {
    if (nombreKey === 'posVenta' || nombreKey === 'posApartados') {
      let cantidadAgregado = 0
      for (let i = 0; i < listaCarrito.length; i++) {
        if (listaCarrito[i]['id'] == idProducto) {
          cantidadAgregado = parseInt(listaCarrito[i]['cantidad']) + parseInt(cantidad)
        }
      }
      if (parseInt(idCategoria) == 2) {
        if (parseInt(cantidadAgregado) > parseInt(stockActual) || parseInt(stockActual) == 0) {
          alertaPersonalizada('warning', 'STOCK NO DISPONIBLE')
          return
        }

      }




    }
    for (let i = 0; i < listaCarrito.length; i++) {
      if (listaCarrito[i]['id'] == idProducto) {
        listaCarrito[i]['cantidad'] = parseInt(listaCarrito[i]['cantidad']) + 1
        localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
        alertaPersonalizada('success', 'PRODUCTO AGREGADO')
        mostrarProducto()
        return
      }
    }
  }
  // si lista carito no existe


  if (nombreKey === 'posVenta' || nombreKey === 'posApartados') {

    if (parseInt(idCategoria) == 2) {
      if (stockActual <= 0) {
        alertaPersonalizada('warning', 'STOCK NO DISPONIBLE')
        return
      }
    }
  }

  listaCarrito.push({
    id: idProducto,
    cantidad: cantidad,
    precio: precio,
    nombre: nombre
  })
  localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
  alertaPersonalizada('success', 'PRODUCTO AGREGADO')
  mostrarProducto()
}
// agregar productos a localStorage
function agregarNotaCredito(
  idProducto,
  codProducto,
  orden_no,
  item,
  cantidad,
  stockActual,
  precio_u,
  total,
  descuento,
  iva,
  precio_pvp,
  por_descuento
) {
  if (localStorage.getItem(nombreKey) == null) {
    listaCarrito = [];
  } else {
    if (nombreKey === "posNotaCredito") {
      // NC no valida stock: es una devolucion, suma de vuelta al inventario
    }
    for (let i = 0; i < listaCarrito.length; i++) {
      if (listaCarrito[i]["id"] == idProducto) {
        listaCarrito[i]["cantidad"] = parseInt(listaCarrito[i]["cantidad"]) + 1;
        localStorage.setItem(nombreKey, JSON.stringify(listaCarrito));
        alertaPersonalizada("success", "PRODUCTO AGREGADO");
        mostrarProducto();
        return;
      }
    }
  }
  // si lista carito no existe

  if (nombreKey === "posVenta" || nombreKey === "posApartados") {
    // if (parseInt(idCategoria) == 2) {
    /*   if (stockActual <= 0) {
        alertaPersonalizada('warning', 'STOCK NO DISPONIBLE')
        return
      }*/
    // }
  }

  listaCarrito.push({
    id: idProducto,
    codProducto: codProducto,
    orden_no: orden_no,
    item: item,
    cantidad: cantidad,
    precio_u: precio_u,
    total: total,
    descuento: descuento,
    iva: iva,
    precio_pvp: precio_pvp,
    por_descuento: por_descuento,
  });
  localStorage.setItem(nombreKey, JSON.stringify(listaCarrito));
  alertaPersonalizada("success", "PRODUCTO AGREGADO");
  mostrarProducto();
}
// agregar productos a localStorage
function agregarTipoPago( idTipoPago, nombre, valor, codigoComprobante) {
  if (localStorage.getItem(nombreKey) == null) {
    listaCarrito = []
  } else {

    for (let i = 0; i < listaCarrito.length; i++) {
      if (listaCarrito[i]['id'] == idTipoPago) {
        localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
        alertaPersonalizada('success', 'PRODUCTO AGREGADO')
        mostrarProductoTipoPago()
        return
      }
    }
  }
  // si lista carito no existe


  listaCarrito.push({
    id: idTipoPago,
    precio: valor,
    nombre: nombre,
    codigoComprobante: codigoComprobante

  })
  localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
  alertaPersonalizada('success', 'PRODUCTO AGREGADO')
  mostrarProductoTipoPago()
}

// agregar productos a localStorage
function agregarRetencion(idTipo, tipo,codigo, baseImponible, valorRetenido) {
  if (localStorage.getItem(nombreKey) == null) {
    listaCarrito = []
  } else {

    for (let i = 0; i < listaCarrito.length; i++) {
      if (listaCarrito[i]['id'] == idTipo) {
        localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
        alertaPersonalizada('success', 'RETENCION AGREGADO')
        mostrarRetenciones()
        return
      }
    }
  }
  // si lista carito no existe


  listaCarrito.push({
    id: idTipo,
    tipo: tipo,
    codigo: codigo,
    baseImponible: baseImponible,
    valorRetenido: valorRetenido
  })
  localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
  alertaPersonalizada('success', 'RETENCION AGREGADO')
  mostrarRetenciones()
}


// agregar evento click para eliminar
function btnEliminarProducto() {
  let lista = document.querySelectorAll('.btnEliminar')
  for (let i = 0; i < lista.length; i++) {
    lista[i].addEventListener('click', function () {
      let idProducto = lista[i].getAttribute('data-id')
      //console.log(idProducto)
      eliminarProducto(idProducto)
    })
  }
}
// eliminar productos del table
function eliminarProducto(idProducto) {
  for (let i = 0; i < listaCarrito.length; i++) {
    if (listaCarrito[i]['id'] == idProducto) {
      listaCarrito.splice(i, 1)
    }
  }
  localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
  alertaPersonalizada('success', 'PRODUCTO ELIMINADO')
  mostrarProducto()
}



// agregar evento click para eliminar
function btnEliminarTipoPago() {
  let lista = document.querySelectorAll('.btnEliminar')
  for (let i = 0; i < lista.length; i++) {
    lista[i].addEventListener('click', function () {
      let idTipoPago = lista[i].getAttribute('data-id')
      //console.log(idProducto)
      eliminarTipoPago(idTipoPago)
    })
  }
}
// agregar evento click para eliminar
function btnEliminarRetencion() {
  let lista = document.querySelectorAll('.btnEliminar')
  for (let i = 0; i < lista.length; i++) {
    lista[i].addEventListener('click', function () {
      let idRetencion = lista[i].getAttribute('data-id')
      //console.log(idProducto)
      eliminarRetencion(idRetencion)
    })
  }
}
// eliminar productos del table
function eliminarTipoPago(idTipoPago) {
  for (let i = 0; i < listaCarrito.length; i++) {
    if (listaCarrito[i]['id'] == idTipoPago) {
      listaCarrito.splice(i, 1)
    }
  }
  localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
  alertaPersonalizada('success', 'TIPO PAGO ELIMINADO')
  mostrarProductoTipoPago()
}

function eliminarRetencion(idRetencion) {
  for (let i = 0; i < listaCarrito.length; i++) {
    if (listaCarrito[i]['id'] == idRetencion) {
      listaCarrito.splice(i, 1)
    }
  }
  localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
  alertaPersonalizada('success', 'RETENCION ELIMINADO')
  mostrarRetenciones()
}
function agregarCantidadNC() {
  let lista = document.querySelectorAll(".inputCantidad");
  //const orden_no = document.querySelector("#orden_no").value;
  for (let i = 0; i < lista.length; i++) {
    lista[i].addEventListener("change", function () {
      let idProducto = lista[i].getAttribute("data-id");
      let orden_no = lista[i].getAttribute("data-orden_no");

      let cantidad = lista[i].value;
      cambiarCantidadNC(idProducto, cantidad, orden_no);
    });
  }
}
function cambiarCantidadNC(idProducto, cantidad, orden_no) {
  if (nombreKey === "posNotaCredito") {
    const url = base_url + "notaCredito/verificarStock/" + idProducto + "/" + orden_no;
    // hacer una instancia del objeto XMLHttpRequest
    // console.log(url)
    const http = new XMLHttpRequest();
    // Abrir una Conexion - POST - GET
    http.open("GET", url, true);
    // Enviar Datos
    http.send();
    // verificar estados
    http.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        const res = JSON.parse(this.responseText);

        if (parseInt(res.cantidad) >= cantidad) {
          for (let i = 0; i < listaCarrito.length; i++) {
            if (listaCarrito[i]["id"] == idProducto) {
              listaCarrito[i]["cantidad"] = cantidad;
            }
          }
          localStorage.setItem(nombreKey, JSON.stringify(listaCarrito));
        } else {
          alertaPersonalizada("warning", "STOCK NO DISPONIBLE");
        }
        mostrarProducto();
        return;
      }
    };
  } else {
    for (let i = 0; i < listaCarrito.length; i++) {
      if (listaCarrito[i]["id"] == idProducto) {
        listaCarrito[i]["cantidad"] = cantidad;
      }
    }
    localStorage.setItem(nombreKey, JSON.stringify(listaCarrito));
    mostrarProducto();
  }
}
// agregar eventa change para cambiar la cantidad
function agregarCantidad() {
  let lista = document.querySelectorAll('.inputCantidad')
  for (let i = 0; i < lista.length; i++) {
    lista[i].addEventListener('change', function () {
      let idProducto = lista[i].getAttribute('data-id')
      let cantidad = lista[i].value
      cambiarCantidad(idProducto, cantidad)
    })
  }
}

function cambiarCantidad(idProducto, cantidad) {
  if (nombreKey === 'posVenta' || nombreKey === 'posApartados') {
    const url = base_url + 'ventas/verificarStock/' + idProducto
    // hacer una instancia del objeto XMLHttpRequest 
    const http = new XMLHttpRequest()
    // Abrir una Conexion - POST - GET
    http.open('GET', url, true)
    // Enviar Datos
    http.send()
    // verificar estados
    http.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        const res = JSON.parse(this.responseText)
        if (parseInt(res.id_categoria == 2)) {

          if (parseInt(res.cantidad) >= cantidad) {
            for (let i = 0; i < listaCarrito.length; i++) {
              if (listaCarrito[i]['id'] == idProducto) {
                listaCarrito[i]['cantidad'] = cantidad
              }
            }
            localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
          } else {
            alertaPersonalizada('warning', 'STOCK NO DISPONIBLE')
          }
        }else{
          for (let i = 0; i < listaCarrito.length; i++) {
            if (listaCarrito[i]['id'] == idProducto) {
              listaCarrito[i]['cantidad'] = cantidad
            }
          }
          localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))


        }


        mostrarProducto()
        return
      }
    }
  } else {
    for (let i = 0; i < listaCarrito.length; i++) {
      if (listaCarrito[i]['id'] == idProducto) {
        listaCarrito[i]['cantidad'] = cantidad
      }
    }
    localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
    mostrarProducto()
  }
}

// agregar precio editable
function agregarPrecioVenta() {
  let lista = document.querySelectorAll('.inputPrecio')
  for (let i = 0; i < lista.length; i++) {
    lista[i].addEventListener('change', function () {
      let idProducto = lista[i].getAttribute('data-id')
      let precio = lista[i].value
      cambiarPrecio(idProducto, precio)
    })
  }
}

// agregar precio editable
function agregarBaseImponible() {
  let lista = document.querySelectorAll('.inputBaseImponible')
  for (let i = 0; i < lista.length; i++) {
    lista[i].addEventListener('change', function x() {
      let idRetencion = lista[i].getAttribute('data-id')
      let baseImponible = lista[i].value
      cambiarBaseImponible(idRetencion, baseImponible)
    })
  }
}

function cambiarBaseImponible(idRetencion, baseImponible) {
  for (let i = 0; i < listaCarrito.length; i++) {
    if (listaCarrito[i]['id'] == idRetencion) {
      listaCarrito[i]['baseImponible'] = baseImponible
      //let valorRetenido = (baseImponible * 100)/100
      //listaCarrito[i]['valorRetenido'] = valorRetenido

    }
  }
  localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
  mostrarRetenciones()
}

function cambiarPrecio(idProducto, precio) {
  for (let i = 0; i < listaCarrito.length; i++) {
    if (listaCarrito[i]['id'] == idProducto) {
      listaCarrito[i]['precio'] = precio
    }
  }
  localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
  mostrarProducto()
}

// agregar descripcion nueva
function agregarDescripcion() {
  let lista = document.querySelectorAll('.inputDescripcion')
  for (let i = 0; i < lista.length; i++) {
    lista[i].addEventListener('change', function () {
      let idProducto = lista[i].getAttribute('data-id')
      let descripcion = lista[i].value
      cambiarDescripcion(idProducto, descripcion)
    })
  }
}

function cambiarDescripcion(idProducto, descripcion) {
  for (let i = 0; i < listaCarrito.length; i++) {
    if (listaCarrito[i]['id'] == idProducto) {
      listaCarrito[i]['nombre'] = descripcion //en nombre es igual al controlador para no tener problema con nombre fijo o cambiable
    }
  }
  localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
  mostrarProducto()
}


function agregarCodigoComprobante() {
  let lista = document.querySelectorAll('.inputCodigoComprobante')
  for (let i = 0; i < lista.length; i++) {
    lista[i].addEventListener('change', function () {
      let idTipoPago = lista[i].getAttribute('data-id')
      let tipoPago = lista[i].value
      cambiarCodigoComprobante(idTipoPago, tipoPago)
    })
  }
}

function cambiarCodigoComprobante(idTipoPago, tipoPago) {
  for (let i = 0; i < listaCarrito.length; i++) {
    if (listaCarrito[i]['id'] == idTipoPago) {
      listaCarrito[i]['codigoComprobante'] = tipoPago //en nombre es igual al controlador para no tener problema con nombre fijo o cambiable
    }
  }
  localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
  mostrarProductoTipoPago()
}

// agregar precio editable
function agregarPrecio() {
  let lista = document.querySelectorAll('.inputPrecio')
  for (let i = 0; i < lista.length; i++) {
    lista[i].addEventListener('change', function () {
      let idTipoPago = lista[i].getAttribute('data-id')
      let precio = lista[i].value
      cambiarValor(idTipoPago, precio)
    })
  }
}

function cambiarValor(idTipoPago, precio) {
  for (let i = 0; i < listaCarrito.length; i++) {
    if (listaCarrito[i]['id'] == idTipoPago) {
      listaCarrito[i]['precio'] = precio
    }
  }
  localStorage.setItem(nombreKey, JSON.stringify(listaCarrito))
  mostrarProductoTipoPago()
}