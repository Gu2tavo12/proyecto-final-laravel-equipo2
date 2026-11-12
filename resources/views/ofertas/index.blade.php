{{-- [CRUD-READ] listado paginado de ofertas con filtros por texto, modalidad y tipo --}}
{{-- [CRUD-DELETE] eliminacion segura con @csrf, @method('DELETE') y modal accesible de confirmacion --}}
@extends('layouts.app')

@section('titulo', 'Ofertas')

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 mb-1">Ofertas Disponibles</h1>
            <p class="text-muted mb-0">Total de ofertas registradas: {{ $ofertas->total() }}</p>
        </div>
        <div>
            <a href="{{ route('ofertas.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Publicar Oferta
            </a>
        </div>
    </div>

    {{-- Formulario GET de búsqueda y filtros --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('ofertas.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="filtro-q" class="form-label small text-muted mb-1">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted" aria-hidden="true"></i></span>
                        <input type="text" name="q" id="filtro-q" class="form-control"
                               placeholder="Título o empresa..."
                               value="{{ $filtros['q'] ?? request('q', '') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <label for="filtro-modalidad" class="form-label small text-muted mb-1">Modalidad</label>
                    <select name="modalidad" id="filtro-modalidad" class="form-select">
                        <option value="">Todas las modalidades</option>
                        @foreach (\App\Models\Oferta::MODALIDADES as $valor => $etiqueta)
                            <option value="{{ $valor }}" {{ ($filtros['modalidad'] ?? request('modalidad')) === $valor ? 'selected' : '' }}>
                                {{ $etiqueta }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <label for="filtro-tipo" class="form-label small text-muted mb-1">Tipo</label>
                    <select name="tipo" id="filtro-tipo" class="form-select">
                        <option value="">Todos los tipos</option>
                        @foreach (\App\Models\Oferta::TIPOS as $valor => $etiqueta)
                            <option value="{{ $valor }}" {{ ($filtros['tipo'] ?? request('tipo')) === $valor ? 'selected' : '' }}>
                                {{ $etiqueta }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1" aria-hidden="true"></i>Filtrar
                    </button>
                    @if (!empty($filtros['q']) || !empty($filtros['modalidad']) || !empty($filtros['tipo']) || request()->hasAny(['q', 'modalidad', 'tipo']))
                        <a href="{{ route('ofertas.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                            <i class="bi bi-x-circle" aria-hidden="true"></i>
                            <span class="visually-hidden">Limpiar filtros</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de Ofertas --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 80px;">Miniatura</th>
                            <th scope="col">Título</th>
                            <th scope="col">Empresa</th>
                            <th scope="col">Modalidad</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Vacantes</th>
                            <th scope="col">Rango Salarial</th>
                            <th scope="col">Fecha Cierre</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end" style="min-width: 220px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ofertas as $oferta)
                            <tr>
                                <td>
                                    <a href="{{ route('ofertas.show', $oferta) }}">
                                        <img src="{{ $oferta->imagen_url }}" class="miniatura" alt="{{ $oferta->titulo }}">
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('ofertas.show', $oferta) }}" class="fw-semibold text-decoration-none text-dark">
                                        {{ $oferta->titulo }}
                                    </a>
                                </td>
                                <td>
                                    @if ($oferta->empresa)
                                        <a href="{{ route('empresas.show', $oferta->empresa) }}" class="text-decoration-none text-secondary">
                                            {{ $oferta->empresa->nombre }}
                                        </a>
                                    @else
                                        <span class="text-muted">Sin empresa</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ \App\Models\Oferta::MODALIDADES[$oferta->modalidad] ?? $oferta->modalidad }}
                                    </span>
                                </td>
                                <td>
                                    @if ($oferta->tipo === 'pasantia')
                                        <span class="badge bg-info text-dark">
                                            {{ \App\Models\Oferta::TIPOS[$oferta->tipo] ?? $oferta->tipo }}
                                        </span>
                                    @else
                                        <span class="badge bg-primary">
                                            {{ \App\Models\Oferta::TIPOS[$oferta->tipo] ?? $oferta->tipo }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $oferta->vacantes }}</td>
                                <td>{{ $oferta->rango_salarial }}</td>
                                <td>{{ $oferta->fecha_cierre ? $oferta->fecha_cierre->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    @if ($oferta->estaActiva())
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Cerrada</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1 justify-content-end">
                                        <a href="{{ route('ofertas.show', $oferta) }}" class="btn btn-sm btn-outline-primary" title="Ver ficha">
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                            <span class="d-none d-lg-inline ms-1">Ver</span>
                                        </a>
                                        <a href="{{ route('ofertas.edit', $oferta) }}" class="btn btn-sm btn-outline-secondary" title="Editar oferta">
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                            <span class="d-none d-lg-inline ms-1">Editar</span>
                                        </a>
                                        <form id="form-eliminar-oferta-{{ $oferta->id }}" action="{{ route('ofertas.destroy', $oferta) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalConfirmarEliminar"
                                                    data-form-id="form-eliminar-oferta-{{ $oferta->id }}"
                                                    data-mensaje="¿Está seguro de que desea eliminar la oferta «{{ $oferta->titulo }}»? Esta acción eliminará permanentemente la oferta y sus postulaciones asociadas."
                                                    title="Eliminar oferta">
                                                <i class="bi bi-trash" aria-hidden="true"></i>
                                                <span class="d-none d-lg-inline ms-1">Eliminar</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <div class="py-2">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary" aria-hidden="true"></i>
                                        @if (!empty($filtros['q']) || !empty($filtros['modalidad']) || !empty($filtros['tipo']) || request()->hasAny(['q', 'modalidad', 'tipo']))
                                            <p class="mb-2">No se encontraron ofertas con los filtros aplicados.</p>
                                            <a href="{{ route('ofertas.index') }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>Limpiar filtros
                                            </a>
                                        @else
                                            <p class="mb-2">No hay ofertas registradas actualmente.</p>
                                            <a href="{{ route('ofertas.create') }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Publicar primera oferta
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $ofertas->links() }}
    </div>
@endsection
