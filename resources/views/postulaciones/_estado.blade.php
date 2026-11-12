{{-- Insignia del estado de una postulacion. Recibe $estado (valor en BD). --}}
@php
    $claseEstado = match ($estado) {
        'aceptada' => 'bg-success',
        'en_revision' => 'bg-warning text-dark',
        'rechazada' => 'bg-danger',
        default => 'bg-info text-dark',
    };
@endphp
<span class="badge {{ $claseEstado }}">{{ \App\Models\Postulacion::ESTADOS[$estado] ?? $estado }}</span>
