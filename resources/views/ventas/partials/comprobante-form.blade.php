<div class="border border-primary rounded p-3">
    <div class="row">
        <div class="col-md-6">
            <label for="forma_pago" class="form-label">Forma de pago <span class="text-danger">*</span></label>
            <select name="forma_pago" id="forma_pago" class="form-select form-select-sm" required>
                <option value="contado">Contado</option>
                <option value="credito">Crédito</option>
            </select>
        </div>

        <div class="col-md-6">
            <label for="comprobante_tipo_codigo" class="form-label">Comprobante <span
                    class="text-danger">*</span></label>
            <select name="comprobante_tipo_codigo" id="comprobante_tipo_codigo" class="form-select form-select-sm" required>
                
            </select>
        </div>

        <div class="col-md-6">
            <label for="serie" class="form-label">Serie <span class="text-danger">*</span></label>
            <input type="text" id="serie" name="serie" class="form-control form-control-sm" required>
            <div class="invalid-feedback"></div>
        </div>

        <div class="col-md-6">
            <label for="correlativo" class="form-label">Correlativo <span class="text-danger">*</span></label>
            <input type="text" id="correlativo" name="correlativo" class="form-control form-control-sm" required>
            <div class="invalid-feedback"></div>
        </div>
    </div>
</div>