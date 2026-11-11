{{-- [ENCAPSULAMIENTO] Parcial reutilizable de campos para creacion y edicion de ofertas --}}
{{-- [VALIDACION] Integracion con old() para repoblacion y directivas @error para mensajes de validacion --}}
@php
    $oferta = $oferta ?? new \App\Models\Oferta();
@endphp

<div class="row g-3">
    {{-- Empresa --}}
    <div class="col-md-6">
        <label for="empresa_id" class="form-label">
            Empresa <span class="required-indicator" aria-hidden="true">*</span>
        </label>
        <select name="empresa_id" id="empresa_id" class="form-select @error('empresa_id') is-invalid @enderror" required>
            <option value="">Seleccione una empresa...</option>
            @foreach ($empresas as $empresa)
                <option value="{{ $empresa->id }}" {{ (string) old('empresa_id', $oferta->empresa_id) === (string) $empresa->id ? 'selected' : '' }}>
                    {{ $empresa->nombre }}
                </option>
            @endforeach
        </select>
        @error('empresa_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Título de la Oferta --}}
    <div class="col-md-6">
        <label for="titulo" class="form-label">
            Título de la Oferta <span class="required-indicator" aria-hidden="true">*</span>
        </label>
        <input type="text" name="titulo" id="titulo" class="form-control @error('titulo') is-invalid @enderror"
               value="{{ old('titulo', $oferta->titulo) }}" required maxlength="255"
               placeholder="Ej: Desarrollador Backend PHP">
        @error('titulo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Tipo de Oferta --}}
    <div class="col-md-4">
        <label for="tipo" class="form-label">
            Tipo <span class="required-indicator" aria-hidden="true">*</span>
        </label>
        <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
            <option value="">Seleccione un tipo...</option>
            @foreach (\App\Models\Oferta::TIPOS as $valor => $etiqueta)
                <option value="{{ $valor }}" {{ old('tipo', $oferta->tipo) === $valor ? 'selected' : '' }}>
                    {{ $etiqueta }}
                </option>
            @endforeach
        </select>
        @error('tipo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Modalidad --}}
    <div class="col-md-4">
        <label for="modalidad" class="form-label">
            Modalidad <span class="required-indicator" aria-hidden="true">*</span>
        </label>
        <select name="modalidad" id="modalidad" class="form-select @error('modalidad') is-invalid @enderror" required>
            <option value="">Seleccione modalidad...</option>
            @foreach (\App\Models\Oferta::MODALIDADES as $valor => $etiqueta)
                <option value="{{ $valor }}" {{ old('modalidad', $oferta->modalidad) === $valor ? 'selected' : '' }}>
                    {{ $etiqueta }}
                </option>
            @endforeach
        </select>
        @error('modalidad')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Vacantes --}}
    <div class="col-md-4">
        <label for="vacantes" class="form-label">
            Vacantes disponibles <span class="required-indicator" aria-hidden="true">*</span>
        </label>
        <input type="number" name="vacantes" id="vacantes" min="1" max="999" class="form-control @error('vacantes') is-invalid @enderror"
               value="{{ old('vacantes', $oferta->vacantes ?? 1) }}" required>
        @error('vacantes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Salario Mínimo y Máximo (Opcionales en pasantías) --}}
    <div class="col-md-6">
        <label for="salario_minimo" class="form-label">Salario Mínimo (USD)</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" min="0" max="999999.99" name="salario_minimo" id="salario_minimo"
                   class="form-control @error('salario_minimo') is-invalid @enderror"
                   value="{{ old('salario_minimo', $oferta->salario_minimo) }}" placeholder="0.00">
            @error('salario_minimo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <label for="salario_maximo" class="form-label">Salario Máximo (USD)</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" min="0" max="999999.99" name="salario_maximo" id="salario_maximo"
                   class="form-control @error('salario_maximo') is-invalid @enderror"
                   value="{{ old('salario_maximo', $oferta->salario_maximo) }}" placeholder="0.00">
            @error('salario_maximo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Explicación de salarios para pasantías --}}
    <div class="col-12">
        <div class="alert alert-light border d-flex align-items-center mb-0 py-2 px-3 small text-muted">
            <i class="bi bi-info-circle-fill text-primary me-2 fs-6" aria-hidden="true"></i>
            <div>
                <strong>Aclaración de salarios:</strong> Para ofertas de tipo <em>Pasantía</em>, la remuneración es opcional (si se deja en blanco figurará como «A convenir»). Para ofertas de <em>Empleo</em>, se sugiere establecer el rango salarial correspondiente.
            </div>
        </div>
    </div>

    {{-- Fecha de Publicación --}}
    <div class="col-md-6">
        <label for="fecha_publicacion" class="form-label">
            Fecha de Publicación <span class="required-indicator" aria-hidden="true">*</span>
        </label>
        <input type="date" name="fecha_publicacion" id="fecha_publicacion"
               class="form-control @error('fecha_publicacion') is-invalid @enderror"
               value="{{ old('fecha_publicacion', $oferta->fecha_publicacion ? $oferta->fecha_publicacion->format('Y-m-d') : date('Y-m-d')) }}"
               required>
        @error('fecha_publicacion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Fecha de Cierre --}}
    <div class="col-md-6">
        <label for="fecha_cierre" class="form-label">
            Fecha de Cierre <span class="required-indicator" aria-hidden="true">*</span>
        </label>
        <input type="date" name="fecha_cierre" id="fecha_cierre"
               class="form-control @error('fecha_cierre') is-invalid @enderror"
               value="{{ old('fecha_cierre', $oferta->fecha_cierre ? $oferta->fecha_cierre->format('Y-m-d') : '') }}"
               required>
        <div class="form-text">
            Las ofertas con fecha de cierre igual a hoy todavía admiten postulaciones.
        </div>
        @error('fecha_cierre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Descripción --}}
    <div class="col-12">
        <label for="descripcion" class="form-label">
            Descripción y Requisitos <span class="required-indicator" aria-hidden="true">*</span>
        </label>
        <textarea name="descripcion" id="descripcion" rows="5"
                  class="form-control @error('descripcion') is-invalid @enderror"
                  required placeholder="Detalle las funciones principales, competencias técnicas y requisitos del puesto...">{{ old('descripcion', $oferta->descripcion) }}</textarea>
        @error('descripcion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Imagen de la Oferta --}}
    <div class="col-12">
        <label for="imagen" class="form-label">Imagen representativa de la oferta</label>

        @if ($oferta->exists && $oferta->imagen)
            <div class="mb-2 p-2 bg-light border rounded d-flex align-items-center gap-3">
                <img src="{{ $oferta->imagen_url }}" alt="Imagen actual de {{ $oferta->titulo }}" class="miniatura border rounded">
                <div>
                    <span class="d-block fw-semibold text-dark small">Imagen actual cargada</span>
                    <span class="text-muted small">Seleccione un nuevo archivo únicamente si desea reemplazarla.</span>
                </div>
            </div>
        @elseif ($oferta->exists)
            <div class="mb-2 p-2 bg-light border rounded d-flex align-items-center gap-3">
                <img src="{{ $oferta->imagen_url }}" alt="Imagen por defecto" class="miniatura border rounded">
                <div>
                    <span class="d-block fw-semibold text-muted small">Sin imagen personalizada</span>
                    <span class="text-muted small">Actualmente se muestra la imagen por defecto.</span>
                </div>
            </div>
        @endif

        <input type="file" name="imagen" id="imagen" accept="image/jpeg,image/png,image/webp,image/jpg"
               class="form-control @error('imagen') is-invalid @enderror">
        <div class="form-text">
            Formatos aceptados: JPG, JPEG, PNG o WEBP. Tamaño máximo permitido: <strong>2 MB</strong> (2048 KB).
            <span class="text-secondary d-block">Nota: Si se produce algún error de validación en el formulario, deberá seleccionar nuevamente el archivo por políticas de seguridad del navegador.</span>
        </div>
        @error('imagen')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
