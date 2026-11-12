{{-- [SEGURIDAD] Solo se enlaza un CV con URL HTTP/HTTPS (accessor enlace_cv_seguro);
     el texto se imprime escapado con {{ }} y el enlace abre sin acceso a window.opener. --}}
@if ($postulacion->enlace_cv_seguro)
    <a href="{{ $postulacion->enlace_cv_seguro }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>Ver CV
    </a>
@else
    <span class="text-muted small" title="{{ $postulacion->enlace_cv }}">Enlace no válido</span>
@endif
