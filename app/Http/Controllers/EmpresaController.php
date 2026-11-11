<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmpresaRequest;
use App\Models\Empresa;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Catalogo de empresas que publican ofertas.
 */
class EmpresaController extends Controller
{
    /**
     * Listado paginado de empresas con su numero de ofertas.
     *
     * [CRUD-READ] withCount('ofertas') resuelve el conteo con una subconsulta
     * en la misma consulta del listado (evita N+1).
     */
    public function index(): View
    {
        $empresas = Empresa::withCount('ofertas')
            ->orderBy('nombre')
            ->paginate(10);

        return view('empresas.index', compact('empresas'));
    }

    /**
     * Ficha de una empresa con sus ofertas.
     *
     * [CRUD-READ]
     * [INYECCION-DEPENDENCIAS] Laravel inyecta la Empresa por route model binding:
     * busca el {empresa} de la URL y responde 404 si no existe.
     */
    public function show(Empresa $empresa): View
    {
        // Carga ansiosa de las ofertas en una sola consulta (evita N+1)
        $empresa->load([
            'ofertas' => fn ($query) => $query->latest('fecha_publicacion'),
        ]);

        return view('empresas.show', compact('empresa'));
    }

    /**
     * Formulario de registro.
     *
     * [CRUD-CREATE]
     */
    public function create(): View
    {
        return view('empresas.create');
    }

    /**
     * Guarda una nueva empresa.
     *
     * [CRUD-CREATE]
     * [INYECCION-DEPENDENCIAS] Laravel inyecta StoreEmpresaRequest y lo valida
     * antes de ejecutar el metodo; si falla, regresa al formulario con errores.
     * [VALIDACION] Solo se guardan los datos validados (y $fillable del modelo).
     */
    public function store(StoreEmpresaRequest $request): RedirectResponse
    {
        $empresa = Empresa::create($request->validated());

        return redirect()
            ->route('empresas.show', $empresa)
            ->with('exito', 'Empresa registrada correctamente.');
    }
}
