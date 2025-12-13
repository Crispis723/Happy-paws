<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Imports\ProductosImport;
use Maatwebsite\Excel\Facades\Excel;


class ProductoController extends Controller
{
    public function __construct(){
        $this->middleware('can:productos_list')->only(['index']);
        $this->middleware('can:productos_create')->only(['store']);
        $this->middleware('can:productos_edit')->only(['show', 'update']);
        $this->middleware('can:productos_delete')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Producto::select(
                ['id','unidad_codigo',
                'afectacion_tipo_codigo',
                'codigo',
                'nombre',
                'precio_unitario',
                'stock',             
                'imagen'                
                ])->orderBy('id','desc');

            return DataTables::of($data)
                ->addColumn('action', function ($row) {                 
                    $editButton ='';
                    if(auth()->user()->can('productos_edit')){
                        $editButton = view('components.button-edit', ['id' => $row->id])->render();
                    }
                    $deleteButton = '';
                    if(auth()->user()->can('productos_delete')){
                        $deleteButton = view('components.button-delete', ['id' => $row->id])->render();
                    }
                    return $editButton . $deleteButton;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('productos.index');
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
        $data = $this->validateProducto($request);

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            //$filename = time() . '_' . $file->getClientOriginalName();
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/productos/'), $filename);
            $data['imagen'] = $filename;
        }

        Producto::create($data);

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
            $registro = Producto::findOrFail($id);
            return response()->json($registro);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $this->validateProducto($request, $id);
        $registro = Producto::findOrFail($id);
        
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');            
            //$filename = time() . '_' . $file->getClientOriginalName();
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/productos/'), $filename);
            $data['imagen'] = $filename;
            
            $old_image = 'uploads/productos/'.$registro->imagen;
            if (file_exists($old_image)) {
                @unlink($old_image);
            }
        }
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
            $registro = Producto::findOrFail($id);
            $old_image = 'uploads/productos/'.$registro->imagen;
            if (file_exists($old_image)) {
                @unlink($old_image);
            }

            $registro->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el registro.'
            ], 500);
        }
    }

    protected function validateProducto(Request $request)
    {
        return $request->validate([
            'unidad_codigo' => 'required|string|max:3',
            'afectacion_tipo_codigo' => 'required|string|max:2',
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'precio_unitario' => 'required|numeric|between:0,99999999.99',
            'stock' => 'required|numeric'
        ]);
    }

    public function buscar(Request $request)
    {
        $q = $request->input('q');
        return Producto::with('afectacionTipo:codigo,porcentaje')
                    ->where('nombre', 'like', "%{$q}%")
                    ->orWhere('codigo', 'like', "%{$q}%")
                    ->select('id','codigo', 'nombre', 'precio_unitario', 'afectacion_tipo_codigo')
                    ->limit(10)
                    ->get();
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new ProductosImport, $request->file('archivo'));

        return redirect()->back()->with('success', 'Productos importados correctamente');
    }


}
