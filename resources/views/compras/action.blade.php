<!-- Modal -->
<div class="modal fade" id="modalUpdate" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content fs-7">
            <form id="formUpdate" method="post">
                @csrf
                <input type="hidden" id="method_field" name="_method">
                <div class="modal-header">
                    <h4 class="modal-title fs-5" id="modalTitle">Nuevo registro</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                          @include('compras.partials.comprobante-form')
                        </div>
                        <div class="col-lg-6">
                          @include('compras.partials.proveedor-form')
                        </div>
                        <div class="col-lg-12 mt-3">
                          <div class="border border-primary rounded p-3">
                            <div class="input-group input-group-sm">
                              <input type="text" id="producto_nombre" class="form-control" placeholder="Buscar producto" autocomplete="off">
                              <input type="hidden" id="producto_id" name="producto_id">
                              <button type="button" id="btnAgregarProducto" class="btn btn-success">Agregar</button>
                            </div>
                          </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-12">
                          @include('ventas.partials.tabla-detalles')
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnSubmit" class="btn btn-primary">Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>