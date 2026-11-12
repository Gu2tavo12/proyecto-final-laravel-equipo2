@extends('layouts.app')

@section('titulo', 'Reporte')

@section('contenido')
    <h1 class="h3 mb-4">Reporte de empleo y pasantías</h1>

    <section class="mb-4" aria-labelledby="titulo-modalidades">
        <h2 id="titulo-modalidades" class="h5">Ofertas activas por modalidad</h2>
        @if ($activasPorModalidad->isEmpty())
            <p class="text-muted mb-2">No hay ofertas activas.</p>
        @endif
        <div class="row row-cols-1 row-cols-sm-3 g-3">
            @foreach (\App\Models\Oferta::MODALIDADES as $valor => $etiqueta)
                <div class="col">
                    <dl class="card card-body shadow-sm border-0 h-100 mb-0">
                        <dt class="text-muted fw-normal">{{ $etiqueta }}</dt>
                        <dd class="h3 mb-0">{{ $activasPorModalidad[$valor] ?? 0 }}</dd>
                    </dl>
                </div>
            @endforeach
        </div>
    </section>

    <div class="row g-4">
        <section class="col-lg-7" aria-labelledby="titulo-postulaciones">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h2 id="titulo-postulaciones" class="h5 mb-0">Postulaciones por oferta</h2>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0" aria-labelledby="titulo-postulaciones">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">Oferta</th>
                                <th scope="col" class="text-end pe-3">Postulaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ofertas as $oferta)
                                <tr>
                                    <th scope="row" class="ps-3 fw-normal text-break">
                                        <a href="{{ route('ofertas.show', $oferta) }}" class="fw-semibold">{{ $oferta->titulo }}</a>
                                        <span class="d-block small text-muted">{{ $oferta->empresa->nombre }}</span>
                                    </th>
                                    <td class="text-end pe-3">{{ $oferta->postulaciones_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="ps-3 text-muted">{{ $ofertas->total() ? 'No hay ofertas en esta página.' : 'No hay ofertas registradas.' }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($ofertas->hasPages())
                    <div class="card-footer bg-white">{{ $ofertas->onEachSide(1)->links() }}</div>
                @endif
            </div>
        </section>

        <section class="col-lg-5" aria-labelledby="titulo-ranking">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h2 id="titulo-ranking" class="h5 mb-1">Ranking de empresas</h2>
                    <p class="small text-muted mb-0">Suma de vacantes de las ofertas activas. Los empates se ordenan por nombre de empresa.</p>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0" aria-labelledby="titulo-ranking">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">Puesto</th>
                                <th scope="col">Empresa</th>
                                <th scope="col" class="text-end pe-3">Vacantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ranking as $empresa)
                                <tr>
                                    <td class="ps-3">{{ $ranking->firstItem() + $loop->index }}</td>
                                    <th scope="row" class="fw-normal text-break">
                                        <a href="{{ route('empresas.show', $empresa) }}" class="fw-semibold">{{ $empresa->nombre }}</a>
                                    </th>
                                    <td class="text-end pe-3">{{ $empresa->vacantes_activas }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="ps-3 text-muted">{{ $ranking->total() ? 'No hay empresas en esta página.' : 'No hay empresas con ofertas activas.' }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($ranking->hasPages())
                    <div class="card-footer bg-white">{{ $ranking->onEachSide(1)->links() }}</div>
                @endif
            </div>
        </section>
    </div>
@endsection
