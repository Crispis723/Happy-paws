<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{

    public function __construct(){
        $this->middleware('can:proveedores_list')->only(['index']);
        $this->middleware('can:proveedores_create')->only(['store']);
        $this->middleware('can:proveedores_edit')->only(['show', 'update']);
        $this->middleware('can:proveedores_delete')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Proveedor::select(['id', 'documento_tipo_codigo', 'numero_documento','razon_social',
                'direccion', 'telefono', 'email']);

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $editButton ='';
                    if(auth()->user()->can('proveedores_edit')){
                        $editButton = view('components.button-edit', ['id' => $row->id])->render();
                    }
                    $deleteButton = '';
                    if(auth()->user()->can('proveedores_delete')){
                        $deleteButton = view('components.button-delete', ['id' => $row->id])->render();
                    }
                    // Combinar ambos botones en una cadena y devolverla
                    return $editButton . $deleteButton;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('proveedores.index');
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
        $registro = Proveedor::create($data);
        
        return response()->json([
            'success'=> true,
            'message'=>'Registro creado satisfactoriamente',
            'proveedor'=> $registro
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $registro = Proveedor::findOrFail($id);
            return response()->json($registro);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Proveedor $proveedor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $this->validateData($request, $id);
        $registro = Proveedor::findOrFail($id);
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
            $registro = Proveedor::findOrFail($id);
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
            'documento_tipo_codigo' => [
                'required',
                'exists:documento_tipos,codigo',
            ],
            'numero_documento' => [
                'required',
                'string',
                'max:20',
                // única combinación por tipo de documento (excepto en update)
                Rule::unique('proveedores')
                    ->where(function ($query) use ($request) {
                        return $query->where('documento_tipo_codigo', $request->documento_tipo_codigo);
                    })
                    ->ignore($id),
            ],
            'razon_social' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ]);
    }
    public function buscar(Request $request)
    {
        $q = $request->input('q');
        return Proveedor::where('razon_social', 'like', "%{$q}%")
                    ->orWhere('numero_documento', 'like', "%{$q}%")
                    ->select('id', 'numero_documento', 'razon_social')
                    ->limit(10)
                    ->get();
    }
}