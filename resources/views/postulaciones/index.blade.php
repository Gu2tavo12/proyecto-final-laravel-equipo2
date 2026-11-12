{{-- [CRUD-READ] listado paginado y filtrable de postulaciones --}}
@extends('layouts.app')

@section('titulo', 'Postulaciones')

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 mb-1">Postulaciones</h1>
            <p class="text-muted mb-0">Resultados: {{ $postulaciones->total() }}</p>
        </div>
        {{-- Con una oferta filtrada, el registro la preselecciona (si sigue activa) --}}
        <a href="{{ route('postulaciones.create', array_filter(['oferta_id' => $filtros['oferta_id']])) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Registrar postulación
        </a>
    </div>

    {{-- Filtros por GET: la paginacion los conserva con withQueryString() --}}
    <form method="GET" action="{{ route('postulaciones.index') }}" class="card shadow-sm border-0 mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-6">
                <label for="filtro-oferta" class="form-label">Oferta</label>
                <select name="oferta_id" id="filtro-oferta" class="form-select">
                    <option value="">Todas las ofertas</option>
                    @foreach ($ofertas as $oferta)
                        <option value="{{ $oferta->id }}" @selected($filtros['oferta_id'] === $oferta->id)>{{ $oferta->titulo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="filtro-estado" class="form-label">Estado</label>
                <select name="estado" id="filtro-estado" class="form-select">
                    <option value="">Todos los estados</option>
                    @foreach (\App\Models\Postulacion::ESTADOS as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected($filtros['estado'] === $valor)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel me-1" aria-hidden="true"></i>Filtrar
                </button>
                <a href="{{ route('postulaciones.index') }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Candidato</th>
                            <th scope="col">Oferta</th>
                            <th scope="col">Empresa</th>
                            <th scope="col">Carrera</th>
                            <th scope="col">CV</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($postulaciones as $postulacion)
                            <tr>
                                <td>
                                    <span class="fw-semibold d-block">{{ $postulacion->nombre_candidato }}</span>
                                    <span class="text-muted small">{{ $postulacion->correo }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('ofertas.show', $postulacion->oferta) }}" class="text-decoration-none">
                                        {{ $postulacion->oferta->titulo }}
                                    </a>
                                </td>
                                <td>{{ $postulacion->oferta->empresa->nombre }}</td>
                                <td>{{ $postulacion->carrera }}</td>
                                <td>@include('postulaciones._enlace-cv')</td>
                                <td>{{ $postulacion->fecha_postulacion->format('d/m/Y') }}</td>
                                <td>@include('postulaciones._estado', ['estado' => $postulacion->estado])</td>
                                <td class="text-end">
                                    <a href="{{ route('postulaciones.show', $postulacion) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1" aria-hidden="true"></i>Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    @if ($filtros['oferta_id'] !== null || $filtros['estado'] !== '')
                                        No hay postulaciones que coincidan con los filtros.
                                    @else
                                        No hay postulaciones registradas.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $postulaciones->links() }}
    </div>
@endsection
