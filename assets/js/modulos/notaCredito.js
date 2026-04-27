let divLoading = document.querySelector("#divLoading");

const tblNuevaNotaCredito = document.querySelector(
  "#tblNuevaNotaCredito tbody"
);

const buscarFacturaNC = document.querySelector("#buscarFacturaNC");
const clienteNC = document.querySelector("#clienteNC");
const serieFactura = document.querySelector("#serieFactura");
const identificacionNC = document.querySelector("#identificacionNC");
const fechaFacturaNC = document.querySelector("#fechaFacturaNC");
const motivoNotaCredito = document.querySelector("#motivoNotaCredito");

const totalNotaCredito = document.querySelector("#totalNotaCredito");
const orden_no = document.querySelector("#orden_no");
const idCliente = document.querySelector("#idCliente");

const errorFactura = document.querySelector("#errorFactura");
//const btnBuscarFacturaNC = document.querySelector('#btnBuscarFacturaNC');

document.addEventListener("DOMContentLoaded", function () {
  // autocomplete clientes
  mostrarProducto();

  document
    .querySelector("#btnBuscarFacturaNC")
    .addEventListener("click", function () {
      const term = document.querySelector("#buscarFacturaNC").value.trim();
      const errorFactura = document.querySelector("#errorFactura");

      if (term === "") {
        errorFactura.textContent = "Ingrese un valor para buscar";
        return;
      }
      const url = base_url + "notaCredito/buscar";

      const http = new XMLHttpRequest();
      http.open("POST", url, true);
      http.setRequestHeader(
        "Content-Type",
        "application/x-www-form-urlencoded"
      );
      http.send("term=" + encodeURIComponent(term));

      http.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
          const data = JSON.parse(this.responseText);
          if (data.length > 0) {
            errorFactura.textContent = "";

            // Tomamos la primera coincidencia
            const factura = data[0];
            buscarFacturaNC.value = factura.claveAcceso;
            document.querySelector("#clienteNC").value = factura.cliente;
            document.querySelector("#fechaFacturaNC").value =
              factura.fechaFactura;
            document.querySelector("#identificacionNC").value =
              factura.rucCliente;
            orden_no.value = factura.id;
            serieFactura.value = factura.serieFactura;
            idCliente.value = factura.idCliente;
            // Llenar detalles de la factura en la tabla de Nota de Crédito
            factura.detalle.forEach((item) => {
              agregarNotaCredito(
                item.id_producto,
                item.codproducto,
                item.orden_no,
                item.item,
                item.cantidad,
                item.cantidad,
                item.precio_u,
                item.total,
                item.descuento,
                item.iva,
                item.precio_pvp,
                item.por_descuento
              );
            });
          } else {
            errorFactura.textContent = "NO EXISTE FACTURA ELECTRÓNICA";
          }
        }
      };
    });

  // completar venta
  btnAccion.addEventListener("click", function () {
    let filas = document.querySelectorAll("#tblNuevaNotaCredito tr").length;
    if (filas < 2) {
      alertaPersonalizada("warning", "CARRITO VACIO");
      return;
    } else if (idCliente.value == "") {
      alertaPersonalizada("warning", "EL CLIENTE ES REQUERIDO");
      return;
    } else {
      divLoading.style.display = "flex";

      const url = base_url + "notaCredito/registrarNotaCredito";
      // hacer una instancia del objeto XMLHttpRequest
      const http = new XMLHttpRequest();
      // Abrir una Conexion - POST - GET
      http.open("POST", url, true);
      // Enviar Datos
      http.send(
        JSON.stringify({
          productos: listaCarrito,
          idCliente: idCliente.value,
          no_factura: orden_no.value,
          claveAccesoFactura: buscarFacturaNC.value,
          fechaFactura: fechaFacturaNC.value,
          serieFactura: serieFactura.value,
          motivoNotaCredito: motivoNotaCredito.value,
        })
      );
      // verificar estados
      http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
          const res = JSON.parse(this.responseText);
          if (typeof res.url !== "undefined" && res.url) {
            window.location.href = res.url;
            return;
          }
          // console.log(this.responseText)
          alertaPersonalizada(res.type, res.msg);
          if (res.type == "success") {
            if (res.factura == "fisica") {
              localStorage.removeItem(nombreKey);
              setTimeout(() => {
                Swal.fire({
                  icon: "success",
                  title: "IMPRIMIR FACTURA FISICA?",
                  showDenyButton: true,
                  showCancelButton: true,
                  confirmButtonText: "Ticked",
                  denyButtonText: `Ride`,
                }).then((result) => {
                  divLoading.style.display = "none";

                  /* Read more about isConfirmed, isDenied below */
                  if (result.isConfirmed) {
                    const ruta =
                      base_url + "notaCredito/reporte/ticked/" + res.idVenta;
                    window.open(ruta, "_blank");
                  } else if (result.isDenied) {
                    const ruta =
                      base_url +
                      "notaCredito/reporte/facturaImp/" +
                      res.idVenta;
                    window.open(ruta, "_blank");
                  }
                  window.location.reload();
                });
              }, 2000);
            } else {
              localStorage.removeItem(nombreKey);
              setTimeout(() => {
                Swal.fire({
                  icon: "success",
                  title: "IMPRIMIR NOTA CREDITO ELECTRONICA?",
                  showDenyButton: true,
                  showCancelButton: true,
                  confirmButtonText: "Ticked",
                  denyButtonText: `Ride`,
                }).then((result) => {
                  divLoading.style.display = "none";

                  /* Read more about isConfirmed, isDenied below */
                  if (result.isConfirmed) {
                    const ruta =
                      base_url +
                      "notaCredito/facturaTicked/" +
                      res.ClaveAcceso +
                      "/" +
                      res.idVenta;
                    window.open(ruta, "_blank");
                  } else if (result.isDenied) {
                    const ruta =
                      base_url +
                      "facturaelectronica/public/archivos/notaCreditos/ride/" + res.ClaveAcces + ".pdf"o +
                      ".pdf";
                    window.open(ruta, "_blank");
                  }
                  window.location.reload();
                });
              }, 2000);
            }
          } else {
            divLoading.style.display = "none";
          }
        } else {
          divLoading.style.display = "none";
        }
      };
    }
  });

  tblHistorial = $("#tblHistorial").DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
    ajax: {
      url: base_url + "notaCredito/listarElectronica",
      dataSrc: function (res) {
        // Si viene con redirección
        if (typeof res.url !== "undefined" && res.url) {
          window.location.href = res.url;
          return [];
        }

        // Caso normal: devolver los datos
        return res;
      },
    },
    columns: [
      { data: "cliente" },
      { data: "secuencial" },
      { data: "fecha" },
      { data: "claveacceso" },
      { data: "estado" },
      { data: "total_modificar" },
      { data: "autorizacion" },
      { data: "acciones" },
    ],
    language: {
      url: base_url + "assets/js/espanol.json",
    },
    dom,
    buttons,
    responsive: true,
    order: [[1, "desc"]],
  });
});

// cargar productos
function mostrarProducto() {
  //const input = document.getElementById('jsnombre');
  if (localStorage.getItem(nombreKey) != null) {
    const url = base_url + "notaCredito/mostrarDatos";
    // hacer una instancia del objeto XMLHttpRequest
    const http = new XMLHttpRequest();
    // Abrir una Conexion - POST - GET
    http.open("POST", url, true);
    // Enviar Datos
    http.send(JSON.stringify(listaCarrito));
    // verificar estados
    http.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        const res = JSON.parse(this.responseText);
        if (typeof res.url !== "undefined" && res.url) {
          window.location.href = res.url;
          return;
        }
        let html = "";
        if (res.productos.length > 0) {
          res.productos.forEach((producto) => {
            // <td>${producto.precio_venta}</td> //reemplazar por el inpuit del precio venta! si desea que el precio no sea modificable
            // <td>${producto.nombre}</td> reemplazar por el input del nombre del producto para no modificar
            html += `<tr>
            <td>
			
			         <input style="width:500px"; type="text" class="form-control inputDescripcion" disabled data-id="${producto.id}" value="${producto.nombre}">
                           </td>
                            <td>
                            <input style="width:115px"; type="number" class="form-control inputPrecio" disabled data-id="${producto.id}" value="${producto.precio_venta}">
                            </td>
                            <td>
                            <input style="width:90px"; type="number" class="form-control inputCantidad" data-id="${producto.id}" data-orden_no="${producto.orden_no}" value="${producto.cantidad}">
                            </td>
                              <td>
                            <input style="width:100px"; type="number" class="form-control inputDescuento" disabled data-id="${producto.id}" value="${producto.descuento}">
                            </td>
                            <td>${producto.subTotalVenta}</td>
                            <td><button class="btn btn-danger btnEliminar" data-id="${producto.id}" type="button"><i class="fas fa-trash"></i></button></td>
                        </tr>`;
          });
          tblNuevaNotaCredito.innerHTML = html;
          // Aplicar contador a todos los inputs generados

          totalNotaCredito.value = res.totalVenta;
          //  totalPagarHidden.value = res.totalVentaHidden;
          btnEliminarProducto();
          agregarCantidadNC();
        } else {
          tblNuevaNotaCredito.innerHTML = "";
          // Aplicar contador a todos los inputs generados
        }
      }
    };
  } else {
    tblNuevaNotaCredito.innerHTML = `<tr>
            <td colspan="5" class="text-center">CARRITO VACIO</td>
        </tr>`;
  }
}

function envioSriElectronica(idVenta) {
  Swal.fire({
    title: "Esta seguro de reenviar la Nota Credito Electronica al SRI ?",
    text: "Reenviar Credito Electronica al SRI!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si, Enviar!",
  }).then((result) => {
    if (result.isConfirmed) {
      divLoading.style.display = "flex";

      const url = base_url + "notaCredito/envioSriElectronica/" + idVenta;
      // hacer una instancia del objeto XMLHttpRequest
      const http = new XMLHttpRequest();
      // Abrir una Conexion - POST - GET
      http.open("GET", url, true);
      // Enviar Datos
      http.send();
      // verificar estados
      http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
          const res = JSON.parse(this.responseText);
          if (typeof res.url !== "undefined" && res.url) {
            window.location.href = res.url;
            return;
          }
          alertaPersonalizada(res.type, res.msg);
          if (res.type == "success") {
            // location.reload()
            divLoading.style.display = "none";

            tblHistorial.ajax.reload();
            //  window.location.reload()
          } else {
            divLoading.style.display = "none";

            tblHistorial.ajax.reload();
          }
        } else {
          divLoading.style.display = "none";
        }
      };
    }
  });
}
