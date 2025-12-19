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
     * Mostrar listado de productos (index)
     * 
     * ¿QUÉ HACE?
     * - Obtiene todos los productos de la BD
     * - Los ordena por más nuevo primero
     * - Los pagina (10 por página)
     * - Los envía a la vista para mostrar en tabla
     */
    public function index(Request $request)
    {
        // Obtener productos paginados (10 por página)
        // orderBy('id', 'desc') = Ordenar por ID descendente (más nuevo primero)
        $productos = Producto::with(['unidad', 'afectacionTipo'])  // Cargar relaciones
                              ->orderBy('id', 'desc')
                              ->paginate(10);
        
        // Pasar los productos a la vista
        return view('productos.index', compact('productos'));
    }

    /**
     * Mostrar formulario para crear nuevo producto
     * 
     * ¿QUÉ HACE?
     * - Obtiene las unidades disponibles (KG, L, UND, etc)
     * - Obtiene los tipos de afectación (IGV, no afectado, etc)
     * - Envía ambas listas a la vista para los dropdowns del formulario
     */
    public function create()
    {
        // Obtener unidades para el dropdown
        $unidades = \App\Models\Unidad::all();  // ALL = Todos los registros
        
        // Obtener tipos de afectación para el dropdown
        $afectacionTipos = \App\Models\AfectacionTipo::all();
        
        // Enviar ambas listas a la vista
        return view('productos.create', compact('unidades', 'afectacionTipos'));
    }

    /**
     * Guardar nuevo producto en BD
     * 
     * ¿QUÉ HACE?
     * 1. Valida los datos del formulario
     * 2. Si hay imagen, la guarda en carpeta uploads/
     * 3. Crea el producto en la BD
     * 4. Redirige al listado con mensaje de éxito
     */
    public function store(Request $request)
    {
        // PASO 1: VALIDAR DATOS
        // validateProducto() = Método que define las reglas de validación
        $data = $this->validateProducto($request);

        // PASO 2: MANEJAR IMAGEN (si existe)
        if ($request->hasFile('imagen')) {
            // hasFile() = ¿Hay un archivo llamado "imagen"?
            
            $file = $request->file('imagen');  // Obtener el archivo
            
            // Crear nombre único: timestamp + números aleatorios + extensión
            // Ej: 1703009987_Abc3Xyz5.jpg
            $filename = time() . '_' . \Illuminate\Support\Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            // Mover archivo a carpeta pública
            // public_path() = Ruta a carpeta /public
            $file->move(public_path('uploads/productos/'), $filename);
            
            // Guardar nombre del archivo en los datos a insertar
            $data['imagen'] = $filename;
        }

        // PASO 3: CREAR PRODUCTO
        // Producto::create() = Insertar nueva fila en tabla "productos"
        Producto::create($data);

        // PASO 4: REDIRIGIR
        return redirect()->route('productos.index')
                        ->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Mostrar detalle de un producto
     * 
     * ¿QUÉ HACE?
     * - Busca el producto por ID
     * - Si no existe, devuelve error 404
     * - Si existe, envía sus datos a la vista de detalle
     */
    public function show($id)
    {
        try {
            // findOrFail() = Buscar por ID, si NO existe → ERROR
            $producto = Producto::with(['unidad', 'afectacionTipo'])->findOrFail($id);
            
            // Enviar producto a la vista
            return view('productos.show', compact('producto'));
        } catch (\Exception $e) {
            // Si no existe, redirigir al listado
            return redirect()->route('productos.index')
                            ->with('error', 'Producto no encontrado.');
        }
    }

    /**
     * Mostrar formulario de edición de producto
     * 
     * ¿QUÉ HACE?
     * - Obtiene el producto por ID
     * - Obtiene unidades y afectación tipos (para los dropdowns)
     * - Envía todo a la vista de edición
     */
    public function edit($id)
    {
        // Buscar el producto
        $producto = Producto::findOrFail($id);
        
        // Obtener opciones para dropdowns
        $unidades = \App\Models\Unidad::all();
        $afectacionTipos = \App\Models\AfectacionTipo::all();
        
        // Enviar a la vista
        return view('productos.edit', compact('producto', 'unidades', 'afectacionTipos'));
    }

    /**
     * Actualizar producto en BD
     * 
     * ¿QUÉ HACE?
     * 1. Valida los datos nuevos
     * 2. Si hay imagen nueva, guarda y elimina la vieja
     * 3. Actualiza el registro en BD
     * 4. Redirige al detalle con mensaje
     */
    public function update(Request $request, $id)
    {
        // PASO 1: VALIDAR datos nuevos
        // El segundo parámetro $id ignora validación única para ESTE registro
        $data = $this->validateProducto($request, $id);
        
        // PASO 2: OBTENER producto actual de BD
        $producto = Producto::findOrFail($id);
        
        // PASO 3: MANEJAR imagen nueva (si existe)
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            
            // Crear nombre único
            $filename = time() . '_' . \Illuminate\Support\Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            // Guardar nueva imagen
            $file->move(public_path('uploads/productos/'), $filename);
            $data['imagen'] = $filename;
            
            // Eliminar imagen vieja si existe
            $oldImage = 'uploads/productos/' . $producto->imagen;
            if (file_exists($oldImage)) {
                @unlink($oldImage);  // @ = ignorar si hay error
            }
        }
        
        // PASO 4: ACTUALIZAR en BD
        $producto->update($data);
        
        // PASO 5: REDIRIGIR
        return redirect()->route('productos.show', $producto->id)
                        ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Eliminar producto
     * 
     * ¿QUÉ HACE?
     * 1. Busca el producto por ID
     * 2. Elimina su imagen de la carpeta uploads/
     * 3. Elimina el registro de la BD
     * 4. Redirige al listado
     */
    public function destroy($id)
    {
        try {
            // Obtener producto
            $producto = Producto::findOrFail($id);
            
            // Eliminar imagen de la carpeta pública
            if ($producto->imagen) {
                $imagePath = 'uploads/productos/' . $producto->imagen;
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }

            // Eliminar registro de BD
            $producto->delete();

            // Redirigir al listado
            return redirect()->route('productos.index')
                            ->with('success', 'Producto eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('productos.index')
                            ->with('error', 'Error al eliminar el producto.');
        }
    }

    /**
     * Validar datos del producto
     * 
     * ¿QUÉ HACE?
     * Define las reglas de validación para que un producto sea válido
     * Estos son las "reglas del juego" que deben cumplir los datos
     */
    protected function validateProducto(Request $request, $id = null)
    {
        return $request->validate([
            // UNIDAD CÓDIGO
            'unidad_codigo' => 'required|exists:unidades,codigo',
            // required = obligatorio
            // exists:unidades,codigo = DEBE existir en tabla unidades, columna codigo
            
            // AFECTACIÓN TIPO CÓDIGO
            'afectacion_tipo_codigo' => 'required|exists:afectacion_tipos,codigo',
            // Debe existir en tabla afectacion_tipos
            
            // CÓDIGO DEL PRODUCTO
            'codigo' => [
                'required',
                'string',
                'max:50',
                // unique = no puede repetirse
                // where = PERO SOLO contar como único en estos registros
                // ignore = EXCEPTO si es el mismo producto (en edición)
                \Illuminate\Validation\Rule::unique('productos')
                    ->ignore($id),
            ],
            
            // NOMBRE
            'nombre' => 'required|string|max:100',
            // max:100 = máximo 100 caracteres
            
            // DESCRIPCIÓN (opcional)
            'descripcion' => 'nullable|string|max:500',
            // nullable = puede estar vacío
            
            // IMAGEN (opcional)
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            // image = debe ser una imagen
            // mimes = solo estos formatos
            // max:2048 = máximo 2MB (en kilobytes)
            
            // PRECIO
            'precio_unitario' => 'required|numeric|min:0|max:999999.99',
            // numeric = número (puede tener decimales)
            // min:0 = no puede ser negativo
            // max = cantidad máxima permitida
            
            // STOCK
            'stock' => 'required|numeric|min:0',
            // min:0 = no puede ser negativo
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
