<?php include_once 'views/templates/header.php'; 





//print_r($ord ); exit;

?>





  <form id="formulario">
    <div class="row justify-content-center pt-5 ">


      <div class="col-md-4">
        <span>BUSCADOR PERSONALIZADO</span>
        <input type="text" name="busqueda" id="busqueda"  class="form-control" autocomplete="off">

      </div>
      <div class="col-md-2 pt-4">
      <input style="width: 100%;" width="auto" type="button" name="btnAccion" id="btnAccion" class="btn btn-primary " value="BUSCAR">

      </div>
      

    </div>
  </form>


  <div class="table-responsive mt-5">
                    <table class="table table-bordered table-striped table-hover nowrap" id="tblPagos" style="width: 100%;">
                        <thead class="thead-light">
                            <tr>
                            <th>Fecha</th>
                                <th>Factura</th>                                
                                <th>Cliente</th>
                                <th>Cedula</th>
                                <th>Total</th>
                                <th>Tipo Pago</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>









<?php include_once 'views/templates/footer.php'; ?>