{{-- [CRUD-READ] listado paginado del catalogo de empresas --}}
@extends('layouts.app')

@section('titulo', 'Empresas')

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 mb-1">Catálogo de Empresas</h1>
            <p class="text-muted mb-0">Total de empresas registradas: {{ $empresas->total() }}</p>
        </div>
        <a href="{{ route('empresas.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Registrar empresa
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Rubro</th>
                            <th scope="col">Correo de contacto</th>
                            <th scope="col">Teléfono</th>
                            <th scope="col">Ofertas</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($empresas as $empresa)
                            <tr>
                                <td>
                                    <a href="{{ route('empresas.show', $empresa) }}" class="fw-semibold text-decoration-none text-dark">
                                        {{ $empresa->nombre }}
                                    </a>
                                </td>
                                <td>{{ $empresa->rubro }}</td>
                                <td>{{ $empresa->correo_contacto }}</td>
                                <td>{{ $empresa->telefono }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $empresa->ofertas_count }}</span></td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('empresas.show', $empresa) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1" aria-hidden="true"></i>Ver
                                    </a>
                                    <a href="{{ route('empresas.edit', $empresa) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil me-1" aria-hidden="true"></i>Editar
                                    </a>
                                    {{-- [CRUD-DELETE] [SEGURIDAD] DELETE con CSRF y confirmacion previa --}}
                                    <form action="{{ route('empresas.destroy', $empresa) }}" method="POST" class="d-inline"
                                          data-confirmar-eliminar="¿Eliminar la empresa «{{ $empresa->nombre }}»? Esta acción no se puede deshacer.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash me-1" aria-hidden="true"></i>Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No hay empresas registradas.
                                    <a href="{{ route('empresas.create') }}" class="d-block mt-2">Registrar la primera empresa</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $empresas->links() }}
    </div>
@endsection
