<?php

namespace App\Http\Controllers;

use App\Models\DocumentoTipo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class DocumentoTipoController extends Controller
{
    public function __construct(){
        $this->middleware('can:documento_tipos_list')->only(['index']);
        $this->middleware('can:documento_tipos_create')->only(['store']);
        $this->middleware('can:documento_tipos_edit')->only(['show', 'update']);
        $this->middleware('can:documento_tipos_delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DocumentoTipo::select(['codigo', 'descripcion']);

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $editButton ='';
                    if(auth()->user()->can('documento_tipos_edit')){
                        $editButton = view('components.button-edit', ['id' => $row->codigo])->render();
                    }
                    $deleteButton = '';
                    if(auth()->user()->can('documento_tipos_delete')){
                        $deleteButton = view('components.button-delete', ['id' => $row->codigo])->render();
                    }
                    // Combinar ambos botones en una cadena y devolverla
                    return $editButton . $deleteButton;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('documento-tipos.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        DocumentoTipo::create($data);
        
        return response()->json([
            'success'=> true,
            'message'=>'Registro creado satisfactoriamente'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $registro = DocumentoTipo::where('codigo', $id)->firstOrFail();
            return response()->json($registro);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $this->validateData($request, $id);
        $registro = DocumentoTipo::where('codigo', $id)->firstOrFail();
        $registro->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente'
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $registro = DocumentoTipo::findOrFail($id);
            $registro->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el registro'
            ], 500);
        }
    }

    protected function validateData(Request $request, $id = null)
    {
        return $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:3',
                Rule::unique('documento_tipos', 'codigo')->ignore($id, 'codigo')  // usar Rule para mayor claridad
            ],
            'descripcion' => 'required|string|max:50',
        ]);
    }

    public function select(Request $request)
    {
        $documentos = DocumentoTipo::select('codigo', 'descripcion')->get();
        return response()->json($documentos);
    }
}
