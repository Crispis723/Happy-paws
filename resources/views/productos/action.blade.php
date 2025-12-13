<!-- Modal -->
<div class="modal fade" id="modalUpdate" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="formUpdate" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="method_field" name="_method">
        <div class="modal-header">
          <h4 class="modal-title fs-5" id="modalTitle">Nuevo registro</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-6">
              <div class="form-group mb-3">
                <label for="unidad_codigo" class="form-label">Unidad <span class="text-danger">*</span></label>
                <select name="unidad_codigo" id="unidad_codigo" class="form-select" required></select>
                <div class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group mb-3">
                <label for="afectacion_tipo_codigo" class="form-label">Afectación tipo <span class="text-danger">*</span></label>
                <select name="afectacion_tipo_codigo" id="afectacion_tipo_codigo" class="form-select" required></select>
                <div class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group mb-3">
                <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                <input type="text" id="codigo" name="codigo" class="form-control" required>
                <div class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group mb-3">
                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" id="nombre" name="nombre" class="form-control" required>
                <div class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-lg-12">
              <div class="form-group mb-3">
                <label for="descripcion" class="form-label">Descripción </label>
                <input type="text" id="descripcion" name="descripcion" class="form-control">
                <div class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="form-group mb-3">
                <label for="precio_unitario" class="form-label">Precio unitario <span class="text-danger">*</span></label>
                <input type="text" id="precio_unitario" name="precio_unitario" class="form-control" required>
                <div class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="form-group mb-3">
                <label for="stock" class="form-label">Stock <span class="text-danger">*</span></label>
                <input type="text" id="stock" name="stock" class="form-control" required>
                <div class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group mb-3">
                <label for="imagen" class="form-label">Imagen</label>
                <input type="file" id="imagen" name="imagen" class="form-control">
                <div class="invalid-feedback"></div>
                <img src="" width="200px" alt="Imagen del producto" id="imagen_producto">
              </div>
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