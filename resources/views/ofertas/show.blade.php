{{-- [CRUD-READ] ficha de detalle de oferta con empresa y postulaciones asociadas --}}
{{-- [CRUD-DELETE] eliminacion con confirmacion modal accesible, @csrf y @method('DELETE') --}}
{{-- [SEGURIDAD] validacion de esquema http/https para enlaces externos de CV y escape estricto con {{ }} --}}
@extends('layouts.app')

@section('titulo', $oferta->titulo)

@section('contenido')
    {{-- Barra superior de acciones --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <a href="{{ route('ofertas.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Volver al listado
        </a>
        <div class="d-flex flex-wrap gap-2">
            @if ($oferta->estaActiva())
                <a href="{{ route('postulaciones.create', ['oferta_id' => $oferta->id]) }}" class="btn btn-success btn-sm">
                    <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Postularse a esta oferta
                </a>
            @endif
            <a href="{{ route('ofertas.edit', $oferta) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil me-1" aria-hidden="true"></i>Editar
            </a>
            <form id="form-eliminar-ficha-{{ $oferta->id }}" action="{{ route('ofertas.destroy', $oferta) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-outline-danger btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#modalConfirmarEliminar"
                        data-form-id="form-eliminar-ficha-{{ $oferta->id }}"
                        data-mensaje="¿Está seguro de que desea eliminar la oferta «{{ $oferta->titulo }}»? Esta acción eliminará permanentemente la vacante y todas las postulaciones recibidas."
                        title="Eliminar oferta">
                    <i class="bi bi-trash me-1" aria-hidden="true"></i>Eliminar
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        {{-- Columna izquierda: Detalle de la Oferta y Postulaciones --}}
        <div class="col-lg-8">
            {{-- Tarjeta de la Oferta --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="{{ $oferta->imagen_url }}" alt="{{ $oferta->titulo }}" class="imagen-oferta rounded shadow-sm">
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if ($oferta->tipo === 'pasantia')
                            <span class="badge bg-info text-dark">
                                {{ \App\Models\Oferta::TIPOS[$oferta->tipo] ?? $oferta->tipo }}
                            </span>
                        @else
                            <span class="badge bg-primary">
                                {{ \App\Models\Oferta::TIPOS[$oferta->tipo] ?? $oferta->tipo }}
                            </span>
                        @endif

                        <span class="badge bg-light text-dark border">
                            {{ \App\Models\Oferta::MODALIDADES[$oferta->modalidad] ?? $oferta->modalidad }}
                        </span>

                        @if ($oferta->estaActiva())
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1" aria-hidden="true"></i>Activa
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                <i class="bi bi-lock me-1" aria-hidden="true"></i>Cerrada
                            </span>
                        @endif
                    </div>

                    <h1 class="h3 fw-bold mb-3">{{ $oferta->titulo }}</h1>

                    {{-- Alerta informativa cuando la oferta está cerrada --}}
                    @if (! $oferta->estaActiva())
                        <div class="alert alert-warning d-flex align-items-center mb-3 py-2 px-3 small border" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-warning" aria-hidden="true"></i>
                            <div>
                                <strong>Convocatoria cerrada:</strong> La fecha límite de recepción de postulaciones fue el
                                <strong>{{ $oferta->fecha_cierre ? $oferta->fecha_cierre->format('d/m/Y') : 'recientemente' }}</strong>. Esta vacante ya no admite nuevas candidaturas.
                            </div>
                        </div>
                    @else
                        <div class="alert alert-success d-flex justify-content-between align-items-center mb-3 py-2 px-3 small border" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock-history fs-5 me-2 text-success" aria-hidden="true"></i>
                                <div>
                                    <strong>Convocatoria abierta:</strong> Se reciben postulaciones hasta el
                                    <strong>{{ $oferta->fecha_cierre ? $oferta->fecha_cierre->format('d/m/Y') : 'fin de mes' }}</strong>.
                                </div>
                            </div>
                            <a href="{{ route('postulaciones.create', ['oferta_id' => $oferta->id]) }}" class="btn btn-sm btn-success text-nowrap ms-2">
                                <i class="bi bi-send me-1" aria-hidden="true"></i>Postularse
                            </a>
                        </div>
                    @endif

                    <div class="row g-3 py-3 border-top border-bottom my-3 bg-light rounded px-2">
                        <div class="col-sm-6 col-md-3">
                            <span class="d-block text-muted small">Rango Salarial</span>
                            <span class="fw-semibold text-dark">{{ $oferta->rango_salarial }}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="d-block text-muted small">Vacantes</span>
                            <span class="fw-semibold text-dark">{{ $oferta->vacantes }}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="d-block text-muted small">Fecha Publicación</span>
                            <span class="fw-semibold text-dark">{{ $oferta->fecha_publicacion ? $oferta->fecha_publicacion->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="d-block text-muted small">Fecha Cierre</span>
                            <span class="fw-semibold text-dark">{{ $oferta->fecha_cierre ? $oferta->fecha_cierre->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                    </div>

                    <h2 class="h5 fw-bold mb-2">Descripción del puesto</h2>
                    <p class="text-secondary mb-0" style="white-space: pre-line;">{{ $oferta->descripcion }}</p>
                </div>
            </div>

            {{-- Sección Postulaciones --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h2 class="h5 fw-bold mb-0">Postulaciones recibidas ({{ $oferta->postulaciones->count() }})</h2>
                    <a href="{{ route('postulaciones.index', ['oferta_id' => $oferta->id]) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-list-ul me-1" aria-hidden="true"></i>Ver en listado de postulaciones
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Candidato</th>
                                    <th scope="col">Correo</th>
                                    <th scope="col">Carrera</th>
                                    <th scope="col">CV</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col" class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($oferta->postulaciones as $postulacion)
                                    <tr>
                                        <td>
                                            <a href="{{ route('postulaciones.show', $postulacion) }}" class="fw-semibold text-decoration-none text-dark">
                                                {{ $postulacion->nombre_candidato }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="mailto:{{ $postulacion->correo }}" class="text-decoration-none text-secondary">
                                                {{ $postulacion->correo }}
                                            </a>
                                        </td>
                                        <td>{{ $postulacion->carrera }}</td>
                                        <td>
                                            {{-- [SEGURIDAD] Solo se enlaza si tiene esquema HTTP o HTTPS valido --}}
                                            @include('postulaciones._enlace-cv', ['postulacion' => $postulacion])
                                        </td>
                                        <td>{{ $postulacion->fecha_postulacion ? $postulacion->fecha_postulacion->format('d/m/Y') : 'N/A' }}</td>
                                        <td>
                                            @include('postulaciones._estado', ['estado' => $postulacion->estado])
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('postulaciones.show', $postulacion) }}" class="btn btn-sm btn-outline-primary" title="Ver detalle de postulación">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                                <span class="d-none d-md-inline ms-1">Detalle</span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <p class="mb-1">No hay postulaciones registradas para esta oferta.</p>
                                            @if ($oferta->estaActiva())
                                                <a href="{{ route('postulaciones.create', ['oferta_id' => $oferta->id]) }}" class="btn btn-sm btn-outline-success mt-1">
                                                    <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Registrar primera postulación
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Columna derecha: Tarjeta de la Empresa --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 5rem;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h2 class="h5 fw-bold mb-0">Empresa</h2>
                    @if ($oferta->empresa)
                        <a href="{{ route('empresas.show', $oferta->empresa) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-building me-1" aria-hidden="true"></i>Ver perfil
                        </a>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if ($oferta->empresa)
                        <h3 class="h5 fw-bold text-primary mb-2">
                            <a href="{{ route('empresas.show', $oferta->empresa) }}" class="text-decoration-none">
                                {{ $oferta->empresa->nombre }}
                            </a>
                        </h3>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-briefcase me-1" aria-hidden="true"></i>{{ $oferta->empresa->rubro }}
                        </p>

                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <span class="d-block text-muted small">Correo de contacto:</span>
                                <a href="mailto:{{ $oferta->empresa->correo_contacto }}" class="text-decoration-none">
                                    <i class="bi bi-envelope me-1" aria-hidden="true"></i>{{ $oferta->empresa->correo_contacto }}
                                </a>
                            </li>
                            <li class="mb-0">
                                <span class="d-block text-muted small">Teléfono:</span>
                                <span class="text-dark">
                                    <i class="bi bi-telephone me-1" aria-hidden="true"></i>{{ $oferta->empresa->telefono ?? 'No especificado' }}
                                </span>
                            </li>
                        </ul>
                    @else
                        <p class="text-muted mb-0">Empresa no vinculada.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
