{{-- Parcial reutilizable con los datos del candidato (registro y edicion) --}}
{{-- [VALIDACION] old() repobla lo enviado y @error muestra el mensaje de cada campo --}}
@php
    $postulacion = $postulacion ?? new \App\Models\Postulacion();
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="nombre_candidato" class="form-label">Nombre completo <span class="required-indicator" aria-hidden="true">*</span></label>
        <input type="text" name="nombre_candidato" id="nombre_candidato" maxlength="150" required
               class="form-control @error('nombre_candidato') is-invalid @enderror"
               value="{{ old('nombre_candidato', $postulacion->nombre_candidato) }}">
        @error('nombre_candidato')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="correo" class="form-label">Correo electrónico <span class="required-indicator" aria-hidden="true">*</span></label>
        <input type="email" name="correo" id="correo" maxlength="150" required
               class="form-control @error('correo') is-invalid @enderror"
               value="{{ old('correo', $postulacion->correo) }}" placeholder="nombre@correo.com">
        <div class="form-text">Un correo solo puede postularse una vez a cada oferta.</div>
        @error('correo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="carrera" class="form-label">Carrera <span class="required-indicator" aria-hidden="true">*</span></label>
        <input type="text" name="carrera" id="carrera" maxlength="100" required
               class="form-control @error('carrera') is-invalid @enderror"
               value="{{ old('carrera', $postulacion->carrera) }}" placeholder="Ej: Ingeniería en Sistemas">
        @error('carrera')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="enlace_cv" class="form-label">Enlace del CV <span class="required-indicator" aria-hidden="true">*</span></label>
        <input type="url" name="enlace_cv" id="enlace_cv" maxlength="255" required
               class="form-control @error('enlace_cv') is-invalid @enderror"
               value="{{ old('enlace_cv', $postulacion->enlace_cv) }}" placeholder="https://...">
        <div class="form-text">URL pública que empiece con http:// o https://.</div>
        @error('enlace_cv')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
