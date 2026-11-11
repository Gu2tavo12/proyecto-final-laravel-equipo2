<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmpresaRequest;
use App\Http\Requests\UpdateEmpresaRequest;
use App\Models\Empresa;
use Illuminate\Database\QueryException;
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

    /**
     * Formulario de edicion con los valores actuales precargados.
     *
     * [CRUD-UPDATE]
     */
    public function edit(Empresa $empresa): View
    {
        return view('empresas.edit', compact('empresa'));
    }

    /**
     * Actualiza la empresa.
     *
     * [CRUD-UPDATE]
     * [INYECCION-DEPENDENCIAS] Se inyectan el Form Request y la Empresa (binding).
     * [VALIDACION] La unicidad ignora a la propia empresa (Rule::unique()->ignore()).
     */
    public function update(UpdateEmpresaRequest $request, Empresa $empresa): RedirectResponse
    {
        $empresa->update($request->validated());

        return redirect()
            ->route('empresas.show', $empresa)
            ->with('exito', 'Empresa actualizada correctamente.');
    }

    /**
     * Elimina una empresa solo si no tiene ofertas.
     *
     * [CRUD-DELETE] La FK ofertas.empresa_id es restrictOnDelete: la BD impide
     * borrar una empresa con ofertas. Se comprueba antes para informar el motivo
     * y, si otra peticion crea una oferta entre la comprobacion y el borrado,
     * la violacion de FK se traduce a un mensaje comprensible.
     */
    public function destroy(Empresa $empresa): RedirectResponse
    {
        $mensajeConOfertas = 'No se puede eliminar «'.$empresa->nombre.'» porque tiene ofertas registradas. '
            .'Elimine o reasigne primero sus ofertas.';

        if ($empresa->ofertas()->exists()) {
            return redirect()->route('empresas.show', $empresa)->with('error', $mensajeConOfertas);
        }

        try {
            $empresa->delete();
        } catch (QueryException $e) {
            if (! $this->esViolacionDeLlaveForanea($e)) {
                throw $e; // Otros errores se propagan
            }

            return redirect()->route('empresas.show', $empresa)->with('error', $mensajeConOfertas);
        }

        return redirect()
            ->route('empresas.index')
            ->with('exito', 'Empresa eliminada correctamente.');
    }

    /**
     * MySQL: codigo 1451 (fila referenciada). SQLite: "FOREIGN KEY constraint failed".
     */
    private function esViolacionDeLlaveForanea(QueryException $e): bool
    {
        $codigo = $e->errorInfo[1] ?? null;

        return $codigo === 1451
            || str_contains($e->getMessage(), 'FOREIGN KEY constraint failed');
    }
}
