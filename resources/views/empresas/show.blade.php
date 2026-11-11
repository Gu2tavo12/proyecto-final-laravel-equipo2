{{-- [CRUD-READ] ficha de la empresa con sus ofertas --}}
@extends('layouts.app')

@section('titulo', $empresa->nombre)

@section('contenido')
    <div class="mb-3">
        <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Volver al catálogo
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <h1 class="h3 fw-bold mb-1">{{ $empresa->nombre }}</h1>
                    <p class="text-muted mb-0"><i class="bi bi-briefcase me-1" aria-hidden="true"></i>{{ $empresa->rubro }}</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-sm-6">
                    <span class="d-block text-muted small">Correo de contacto</span>
                    <a href="mailto:{{ $empresa->correo_contacto }}" class="text-decoration-none">
                        <i class="bi bi-envelope me-1" aria-hidden="true"></i>{{ $empresa->correo_contacto }}
                    </a>
                </div>
                <div class="col-sm-6">
                    <span class="d-block text-muted small">Teléfono</span>
                    <span class="text-dark"><i class="bi bi-telephone me-1" aria-hidden="true"></i>{{ $empresa->telefono }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h2 class="h5 fw-bold mb-0">Ofertas publicadas ({{ $empresa->ofertas->count() }})</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Título</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Modalidad</th>
                            <th scope="col">Fecha de cierre</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($empresa->ofertas as $oferta)
                            <tr>
                                <td>
                                    <a href="{{ route('ofertas.show', $oferta) }}" class="fw-semibold text-decoration-none">
                                        {{ $oferta->titulo }}
                                    </a>
                                </td>
                                <td>{{ \App\Models\Oferta::TIPOS[$oferta->tipo] ?? $oferta->tipo }}</td>
                                <td>{{ \App\Models\Oferta::MODALIDADES[$oferta->modalidad] ?? $oferta->modalidad }}</td>
                                <td>{{ $oferta->fecha_cierre ? $oferta->fecha_cierre->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    @if ($oferta->estaActiva())
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Cerrada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Esta empresa todavía no tiene ofertas registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
