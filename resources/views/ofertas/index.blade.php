{{-- [CRUD-READ] listado paginado --}}
@extends('layouts.app')

@section('titulo', 'Ofertas')

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 mb-1">Ofertas Disponibles</h1>
            <p class="text-muted mb-0">Total de ofertas registradas: {{ $ofertas->total() }}</p>
        </div>
    </div>

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
                            <th scope="col">Fecha de Cierre</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ofertas as $oferta)
                            <tr>
                                <td>
                                    <img src="{{ $oferta->imagen_url }}" class="miniatura" alt="{{ $oferta->titulo }}">
                                </td>
                                <td>
                                    <a href="{{ route('ofertas.show', $oferta) }}" class="fw-semibold text-decoration-none text-dark">
                                        {{ $oferta->titulo }}
                                    </a>
                                </td>
                                <td>{{ $oferta->empresa->nombre }}</td>
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
                                    <a href="{{ route('ofertas.show', $oferta) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    No hay ofertas registradas.
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
