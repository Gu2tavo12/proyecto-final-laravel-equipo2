<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Inicio') | Bolsa de Empleo y Pasantías</title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Estilos propios -->
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body class="d-flex flex-column min-vh-100">
    {{-- [ACCESIBILIDAD] Enlace directo al contenido para usuarios con lectores de pantalla y navegacion por teclado --}}
    <a href="#contenido-principal" class="visually-hidden-focusable btn btn-light position-absolute top-0 start-0 m-2 z-3 shadow">
        Saltar al contenido principal
    </a>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary app-navbar shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-truncate me-2" href="{{ route('panel') }}" style="max-width: calc(100% - 65px);">
                <i class="bi bi-briefcase-fill me-2" aria-hidden="true"></i>Bolsa de Empleo UNICAES
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContenido" aria-controls="navbarContenido" aria-expanded="false" aria-label="Alternar navegación">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContenido">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('panel') || request()->is('/') ? 'active' : '' }}" @if (request()->routeIs('panel') || request()->is('/')) aria-current="page" @endif href="{{ route('panel') }}">
                            <i class="bi bi-house-door me-1" aria-hidden="true"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('ofertas.*') || request()->is('ofertas*') ? 'active' : '' }}" @if (request()->routeIs('ofertas.*') || request()->is('ofertas*')) aria-current="page" @endif href="{{ route('ofertas.index') }}">
                            <i class="bi bi-card-list me-1" aria-hidden="true"></i>Ofertas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('empresas.*') || request()->is('empresas*') ? 'active' : '' }}" @if (request()->routeIs('empresas.*') || request()->is('empresas*')) aria-current="page" @endif href="{{ route('empresas.index') }}">
                            <i class="bi bi-building me-1" aria-hidden="true"></i>Empresas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('postulaciones.*') || request()->is('postulaciones*') ? 'active' : '' }}" @if (request()->routeIs('postulaciones.*') || request()->is('postulaciones*')) aria-current="page" @endif href="{{ route('postulaciones.index') }}">
                            <i class="bi bi-people me-1" aria-hidden="true"></i>Postulaciones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reporte.*') || request()->is('reporte*') ? 'active' : '' }}" @if (request()->routeIs('reporte.*') || request()->is('reporte*')) aria-current="page" @endif href="{{ route('reporte.index') }}">
                            <i class="bi bi-bar-chart me-1" aria-hidden="true"></i>Reporte
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main id="contenido-principal" class="container py-3 py-md-4 flex-grow-1">
        @include('partials.alertas')
        @yield('contenido')
    </main>

    <footer class="app-footer mt-auto py-3 bg-light border-top">
        <div class="container text-center">
            <p class="mb-0 text-muted">Universidad Católica de El Salvador · Desarrollo Web con Software Libre · Caso 7 · {{ date('Y') }}</p>
        </div>
    </footer>

    {{-- [ACCESIBILIDAD] Modal comun de confirmacion para formularios DELETE --}}
    @include('partials.modal-confirmar-eliminar')

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    {{-- Script para patron comun de confirmacion DELETE accesible con teclado --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('modalConfirmarEliminar');
            if (!modalEl) return;

            let formAEjecutar = null;
            let ultimoDisparador = null;
            const mensajeEl = document.getElementById('modalConfirmarEliminarMensaje');
            const btnConfirmar = document.getElementById('btnConfirmarAccionEliminar');

            modalEl.addEventListener('show.bs.modal', function (event) {
                ultimoDisparador = event.relatedTarget;
                if (!ultimoDisparador) return;

                const formId = ultimoDisparador.getAttribute('data-form-id');
                formAEjecutar = formId ? document.getElementById(formId) : ultimoDisparador.closest('form');

                const mensaje = ultimoDisparador.getAttribute('data-mensaje') || ultimoDisparador.getAttribute('data-confirmar');
                if (mensaje && mensajeEl) {
                    mensajeEl.textContent = mensaje;
                } else if (mensajeEl) {
                    mensajeEl.textContent = '¿Está seguro de que desea eliminar este registro? Esta acción no se puede deshacer.';
                }
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                formAEjecutar = null;
                if (ultimoDisparador && typeof ultimoDisparador.focus === 'function') {
                    ultimoDisparador.focus();
                }
            });

            if (btnConfirmar) {
                btnConfirmar.addEventListener('click', function () {
                    if (formAEjecutar) {
                        formAEjecutar.submit();
                    }
                });
            }

            // Intercepta formularios DELETE con atributo data-confirmar-eliminar
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (form && form.matches('form[data-confirmar-eliminar]') && !form.dataset.confirmado) {
                    e.preventDefault();
                    formAEjecutar = form;
                    if (mensajeEl && form.dataset.confirmarEliminar) {
                        mensajeEl.textContent = form.dataset.confirmarEliminar;
                    }
                    const modalInst = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modalInst.show();
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
