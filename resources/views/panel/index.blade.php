{{-- [CRUD-READ] panel de indicadores --}}
@extends('layouts.app')

@section('titulo', 'Panel')

@section('contenido')
    <div class="mb-4">
        <h1 class="h3 mb-1">Bolsa de Empleo y Pasantías</h1>
        <p class="text-muted mb-0">Resumen general de ofertas, empresas y postulaciones.</p>
    </div>

    <nav class="d-flex flex-wrap gap-2 mb-4" aria-label="Módulos de la bolsa de empleo">
        <a href="{{ route('ofertas.index') }}" class="btn btn-primary">
            <i class="bi bi-card-list me-1" aria-hidden="true"></i>Ofertas
        </a>
        <a href="{{ route('empresas.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-building me-1" aria-hidden="true"></i>Empresas
        </a>
        <a href="{{ route('postulaciones.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-people me-1" aria-hidden="true"></i>Postulaciones
        </a>
        <a href="{{ route('reporte.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-bar-chart me-1" aria-hidden="true"></i>Reporte
        </a>
    </nav>

    {{-- Indicadores principales --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1"><i class="bi bi-card-list me-1"></i>Ofertas</p>
                    <p class="h3 mb-0">{{ $totalOfertas }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1"><i class="bi bi-building me-1"></i>Empresas</p>
                    <p class="h3 mb-0">{{ $totalEmpresas }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1"><i class="bi bi-people me-1"></i>Postulaciones</p>
                    <p class="h3 mb-0">{{ $totalPostulaciones }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card shadow-sm border-0 h-100 border-start border-success border-4">
                <div class="card-body">
                    <p class="text-muted small mb-1"><i class="bi bi-check-circle me-1"></i>Ofertas activas</p>
                    <p class="h3 mb-0 text-success">{{ $ofertasActivas }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg">
            <div class="card shadow-sm border-0 h-100 border-start border-secondary border-4">
                <div class="card-body">
                    <p class="text-muted small mb-1"><i class="bi bi-x-circle me-1"></i>Ofertas cerradas</p>
                    <p class="h3 mb-0 text-secondary">{{ $ofertasCerradas }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Conteos por modalidad y tipo --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">Ofertas por modalidad y tipo</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">Modalidad</th>
                                <th scope="col" class="text-end pe-3">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (\App\Models\Oferta::MODALIDADES as $valor => $etiqueta)
                                <tr>
                                    <td class="ps-3">{{ $etiqueta }}</td>
                                    <td class="text-end pe-3">{{ $porModalidad[$valor] ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">Tipo</th>
                                <th scope="col" class="text-end pe-3">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (\App\Models\Oferta::TIPOS as $valor => $etiqueta)
                                <tr>
                                    <td class="ps-3">{{ $etiqueta }}</td>
                                    <td class="text-end pe-3">{{ $porTipo[$valor] ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Ofertas recientes --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">Ofertas más recientes</div>
                <ul class="list-group list-group-flush">
                    @forelse ($ofertasRecientes as $oferta)
                        <li class="list-group-item d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <a href="{{ route('ofertas.show', $oferta) }}" class="fw-semibold text-decoration-none">
                                    {{ $oferta->titulo }}
                                </a>
                                <div class="small text-muted">
                                    {{ $oferta->empresa?->nombre ?? 'Sin empresa' }}
                                    · {{ \App\Models\Oferta::MODALIDADES[$oferta->modalidad] ?? $oferta->modalidad }}
                                    · {{ \App\Models\Oferta::TIPOS[$oferta->tipo] ?? $oferta->tipo }}
                                </div>
                            </div>
                            <div class="text-end">
                                @if ($oferta->estaActiva())
                                    <span class="badge bg-success">Activa</span>
                                @else
                                    <span class="badge bg-secondary">Cerrada</span>
                                @endif
                                <div class="small text-muted text-nowrap">{{ $oferta->fecha_publicacion?->format('d/m/Y') }}</div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No hay ofertas registradas.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

@endsection
