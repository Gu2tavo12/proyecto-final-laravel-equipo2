<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOfertaRequest;
use App\Http\Requests\UpdateOfertaRequest;
use App\Models\Empresa;
use App\Models\Oferta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

/**
 * CRUD de ofertas: listado con filtros, ficha, registro, edicion y eliminacion.
 * Las imagenes se guardan en el disco "public" bajo ofertas/.
 */
class OfertaController extends Controller
{
    /**
     * Longitud maxima del texto de busqueda.
     */
    private const BUSQUEDA_MAX = 100;

    /**
     * Carpeta del disco "public" donde la app guarda las imagenes de ofertas.
     */
    private const CARPETA_IMAGENES = 'ofertas';

    /**
     * Listado paginado de ofertas, las mas recientes primero, con busqueda
     * por titulo o empresa y filtros por modalidad y tipo.
     *
     * [CRUD-READ] with('empresa') carga las empresas en una sola consulta (evita N+1).
     */
    public function index(Request $request): View
    {
        $filtros = $this->filtros($request);

        $ofertas = Oferta::with('empresa')
            // [SEGURIDAD] Los valores viajan como parametros enlazados (bindings), nunca
            // concatenados en el SQL. El grupo OR va entre parentesis para que los
            // filtros de modalidad y tipo se apliquen tambien a las coincidencias por empresa.
            ->when($filtros['q'] !== '', function (Builder $query) use ($filtros) {
                $query->where(function (Builder $grupo) use ($filtros) {
                    $grupo->where('titulo', 'like', '%'.$filtros['q'].'%')
                        ->orWhereHas('empresa', fn (Builder $empresa) => $empresa->where('nombre', 'like', '%'.$filtros['q'].'%'));
                });
            })
            ->when($filtros['modalidad'] !== '', fn (Builder $query) => $query->where('modalidad', $filtros['modalidad']))
            ->when($filtros['tipo'] !== '', fn (Builder $query) => $query->where('tipo', $filtros['tipo']))
            ->latest('fecha_publicacion')
            ->latest('id')
            ->paginate(10)
            // Los enlaces de paginacion conservan q, modalidad y tipo.
            ->withQueryString();

        return view('ofertas.index', compact('ofertas', 'filtros'));
    }

    /**
     * Formulario de registro.
     *
     * [CRUD-CREATE] Si el catalogo de empresas esta vacio no se puede publicar
     * una oferta, asi que se envia al registro de empresas.
     */
    public function create(): View|RedirectResponse
    {
        $empresas = $this->empresas();

        if ($empresas->isEmpty()) {
            return redirect()
                ->route('empresas.create')
                ->with('error', 'Registra una empresa antes de publicar ofertas.');
        }

        return view('ofertas.create', compact('empresas'));
    }

    /**
     * Guarda una oferta nueva con su imagen opcional.
     *
     * [CRUD-CREATE]
     * [INYECCION-DEPENDENCIAS] Laravel inyecta StoreOfertaRequest y lo valida antes
     * de ejecutar el metodo; si falla, regresa al formulario con errores y old().
     */
    public function store(StoreOfertaRequest $request): RedirectResponse
    {
        // [SEGURIDAD] Solo datos validados; la ruta de la imagen la genera la app.
        $datos = $request->safe()->except('imagen');
        $datos['imagen'] = null;

        if ($request->hasFile('imagen')) {
            $ruta = $this->guardarImagen($request->file('imagen'));

            if ($ruta === null) {
                return back()
                    ->withInput()
                    ->withErrors(['imagen' => 'No se pudo guardar la imagen. Intenta de nuevo.']);
            }

            $datos['imagen'] = $ruta;
        }

        try {
            $oferta = Oferta::create($datos);
        } catch (Throwable $e) {
            // Si la BD falla, se borra el archivo recien subido para no dejar huerfanos.
            $this->eliminarImagen($datos['imagen']);
            report($e);

            return back()
                ->withInput()
                ->with('error', 'No se pudo registrar la oferta. Intenta de nuevo.');
        }

        return redirect()
            ->route('ofertas.show', $oferta)
            ->with('exito', 'Oferta registrada correctamente.');
    }

    /**
     * Ficha de una oferta con su empresa y sus postulaciones.
     *
     * [CRUD-READ]
     * [INYECCION-DEPENDENCIAS] Laravel inyecta la Oferta por route model binding:
     * busca el {oferta} de la URL y responde 404 si no existe.
     */
    public function show(Oferta $oferta): View
    {
        $oferta->load([
            'empresa',
            'postulaciones' => fn ($query) => $query->latest('fecha_postulacion'),
        ]);

        return view('ofertas.show', compact('oferta'));
    }

    /**
     * Formulario de edicion.
     *
     * [CRUD-UPDATE]
     * [INYECCION-DEPENDENCIAS] Route model binding: 404 si la oferta no existe.
     */
    public function edit(Oferta $oferta): View
    {
        $empresas = $this->empresas();

        return view('ofertas.edit', compact('oferta', 'empresas'));
    }

    /**
     * Actualiza la oferta y, si se adjunta, reemplaza su imagen.
     *
     * [CRUD-UPDATE] Orden seguro: 1) guardar la imagen nueva, 2) actualizar la BD,
     * 3) recien entonces borrar la anterior. Si la BD falla se borra la nueva y
     * la oferta conserva su foto vigente.
     * [INYECCION-DEPENDENCIAS] UpdateOfertaRequest (validacion) y Oferta (binding).
     */
    public function update(UpdateOfertaRequest $request, Oferta $oferta): RedirectResponse
    {
        // [SEGURIDAD] Solo datos validados; sin imagen nueva no se toca la columna.
        $datos = $request->safe()->except('imagen');
        $imagenAnterior = $oferta->imagen;
        $imagenNueva = null;

        if ($request->hasFile('imagen')) {
            $imagenNueva = $this->guardarImagen($request->file('imagen'));

            if ($imagenNueva === null) {
                return back()
                    ->withInput()
                    ->withErrors(['imagen' => 'No se pudo guardar la imagen. Intenta de nuevo.']);
            }

            $datos['imagen'] = $imagenNueva;
        }

        try {
            $oferta->update($datos);
        } catch (Throwable $e) {
            $this->eliminarImagen($imagenNueva);
            report($e);

            return back()
                ->withInput()
                ->with('error', 'No se pudo actualizar la oferta. Intenta de nuevo.');
        }

        if ($imagenNueva !== null && $imagenAnterior !== $imagenNueva) {
            $this->eliminarImagen($imagenAnterior);
        }

        return redirect()
            ->route('ofertas.show', $oferta)
            ->with('exito', 'Oferta actualizada correctamente.');
    }

    /**
     * Normaliza los filtros del GET: texto recortado y acotado, y solo valores
     * conocidos de modalidad y tipo (cualquier otro valor se ignora).
     *
     * @return array{q: string, modalidad: string, tipo: string}
     */
    private function filtros(Request $request): array
    {
        $q = $request->query('q');
        $modalidad = $request->query('modalidad');
        $tipo = $request->query('tipo');

        return [
            'q' => is_string($q) ? trim(mb_substr(trim($q), 0, self::BUSQUEDA_MAX)) : '',
            'modalidad' => is_string($modalidad) && array_key_exists($modalidad, Oferta::MODALIDADES) ? $modalidad : '',
            'tipo' => is_string($tipo) && array_key_exists($tipo, Oferta::TIPOS) ? $tipo : '',
        ];
    }

    /**
     * Empresas para el selector del formulario, ordenadas por nombre.
     *
     * @return Collection<int, Empresa>
     */
    private function empresas(): Collection
    {
        return Empresa::orderBy('nombre')->get();
    }

    /**
     * Guarda la imagen en storage/app/public/ofertas con un nombre aleatorio.
     * Devuelve la ruta relativa o null si no se pudo escribir.
     */
    private function guardarImagen(UploadedFile $imagen): ?string
    {
        $ruta = $imagen->store(self::CARPETA_IMAGENES, 'public');

        return is_string($ruta) && $ruta !== '' ? $ruta : null;
    }

    /**
     * Borra una imagen del disco "public".
     *
     * [SEGURIDAD] Solo borra archivos dentro de ofertas/ (los que gestiona la app);
     * ignora rutas vacias, absolutas o con "..".
     */
    private function eliminarImagen(?string $ruta): void
    {
        if (! $this->esImagenGestionada($ruta)) {
            return;
        }

        Storage::disk('public')->delete($ruta);
    }

    /**
     * Indica si la ruta apunta a un archivo de ofertas/ guardado por la app.
     */
    private function esImagenGestionada(?string $ruta): bool
    {
        return is_string($ruta)
            && str_starts_with($ruta, self::CARPETA_IMAGENES.'/')
            && ! str_contains($ruta, '..')
            && ! str_contains($ruta, '\\');
    }
}
