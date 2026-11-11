{{-- Parcial reutilizable de campos para registrar y editar empresas --}}
{{-- [VALIDACION] old() repobla lo enviado y @error muestra el mensaje de cada campo --}}
@php
    $empresa = $empresa ?? new \App\Models\Empresa();
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre <span class="required-indicator" aria-hidden="true">*</span></label>
        <input type="text" name="nombre" id="nombre" maxlength="150" required
               class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $empresa->nombre) }}">
        @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="rubro" class="form-label">Rubro <span class="required-indicator" aria-hidden="true">*</span></label>
        <input type="text" name="rubro" id="rubro" maxlength="100" required
               class="form-control @error('rubro') is-invalid @enderror"
               value="{{ old('rubro', $empresa->rubro) }}" placeholder="Ej: Tecnología">
        @error('rubro')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="correo_contacto" class="form-label">Correo de contacto <span class="required-indicator" aria-hidden="true">*</span></label>
        <input type="email" name="correo_contacto" id="correo_contacto" maxlength="150" required
               class="form-control @error('correo_contacto') is-invalid @enderror"
               value="{{ old('correo_contacto', $empresa->correo_contacto) }}">
        @error('correo_contacto')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="telefono" class="form-label">Teléfono <span class="required-indicator" aria-hidden="true">*</span></label>
        <input type="text" name="telefono" id="telefono" maxlength="9" required pattern="\d{4}-\d{4}"
               class="form-control @error('telefono') is-invalid @enderror"
               value="{{ old('telefono', $empresa->telefono) }}" placeholder="0000-0000">
        <div class="form-text">Formato: 0000-0000.</div>
        @error('telefono')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
