<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraDetalle;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Models\ComprobanteSerie;

use Barryvdh\DomPDF\Facade\Pdf;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class CompraController extends Controller
{

    public function __construct(){
        $this->middleware('can:compras_list')->only(['index']);
        $this->middleware('can:compras_create')->only(['store']);
        $this->middleware('can:compras_edit')->only(['show', 'update']);
        $this->middleware('can:compras_delete')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Compra::with(['proveedor', 'user', 'comprobanteTipo' ])
             ->select(['id', 'fecha', 'forma_pago', 'serie', 'correlativo', 'impuesto', 'total', 'estado', 
             'proveedor_id', 'user_id', 'comprobante_tipo_codigo'])->orderBy('id', 'desc');

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $editButton ='';
                    if(auth()->user()->can('compras_edit')){
                        $editButton = view('components.button-edit', ['id' => $row->id])->render();
                    }
                    $deleteButton = '';
                    if(auth()->user()->can('compras_delete')){
                        $deleteButton = view('components.button-delete', ['id' => $row->id])->render();
                    }
                    $ticketButton= '<a href="' . route('compras.imprimir', $row->id) . '" 
                        target="_blank" 
                        class="btn btn-sm btn-secondary" 
                        title="Ver Comprobante">
                        <i class="bi bi-printer"></i>
                     </a>';

                    // Combinar ambos botones en una cadena y devolverla
                    return $editButton . $deleteButton. $ticketButton;
                })
                ->addColumn('usuario', function ($row) {
                    return optional($row->user)->name;
                })
                ->editColumn('proveedor', function ($row) {
                    return optional($row->proveedor)->razon_social;
                })
                ->editColumn('tipo_comprobante', function ($row) {
                    return optional($row->comprobanteTipo)->descripcion;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('compras.index');
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

        DB::beginTransaction();
        try {
            $compraData = $this->processCompraData($data, true);
            
            $compra = Compra::create($compraData['compra']);
            $compra->detalles()->createMany($compraData['detalles']);
            Producto::updateStock(true,$compraData['detalles']);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Registro creado satisfactoriamente',
                'compra_id' => $compra->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            //$registro = Venta::with(['detalles.producto.afectacionTipo', 'cliente'])->findOrFail($id);
            $registro = Compra::with([
                'detalles.producto' => function ($query) {
                    $query->select('id', 'afectacion_tipo_codigo', 'codigo', 'nombre', 'precio_unitario');
                },
                'detalles.producto.afectacionTipo' => function ($query) {
                    $query->select('codigo', 'descripcion', 'porcentaje');
                },
                'proveedor' => function ($query) {
                    $query->select('id', 'razon_social');
                }
            ])->findOrFail($id);

            return response()->json($registro);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Compra $compra)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $compra = Compra::findOrFail($id);
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            $compraData = $this->processCompraData($data, false);
            
            $compra->update($compraData['compra']);
            Producto::updateStock(false,$compra->detalles->toArray()); 
            $compra->detalles()->delete();
            $compra->detalles()->createMany($compraData['detalles']);
            Producto::updateStock(true,$compraData['detalles']);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Registro actualizado satisfactoriamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $registro = Compra::findOrFail($id);
            Producto::updateStock(false,$registro->detalles->toArray()); 
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
    private function processCompraData(array $data, bool $isNew = true)
    {
        // Obtener productos con su tipo de afectación
        $productos = Producto::with('afectacionTipo')
            ->whereIn('id', collect($data['detalles'])->pluck('producto_id'))
            ->get()
            ->keyBy('id');

        // Inicializar totales
        $totales = [
            'op_gravada' => 0,
            'op_exonerada' => 0,
            'op_inafecta' => 0,
            'impuesto' => 0,
            'total' => 0
        ];

        $detallesCalculados = [];

        // Calcular cada detalle y acumular totales
        foreach ($data['detalles'] as $detalle) {
            $detallesCalculados[] = $this->calculateAndAddDetailTotals(
                $productos[$detalle['producto_id']],
                $detalle['cantidad'],
                $detalle['precio_unitario'],
                $totales
            );
        }

        // Armar cabecera de la venta
        $compraData = [
            'proveedor_id' => $data['proveedor_id'],
            'comprobante_tipo_codigo' => $data['comprobante_tipo_codigo'],
            'serie' => $data['serie'],
            'correlativo' => $data['correlativo'],
            'forma_pago' => $data['forma_pago'],
            'op_gravada' => round($totales['op_gravada'], 2),
            'op_exonerada' => round($totales['op_exonerada'], 2),
            'op_inafecta' => round($totales['op_inafecta'], 2),
            'impuesto' => round($totales['impuesto'], 2),
            'total' => round($totales['total'], 2),
        ];

        if ($isNew) {
            $compraData['fecha'] = now();
            $compraData['user_id'] = auth()->id();
            $compraData['estado'] = 'registrado';
        }

        return [
            'compra' => $compraData,
            'detalles' => $detallesCalculados
        ];
    }

    private function calculateAndAddDetailTotals($producto, $cantidad, $precio_unitario_input, array &$totales)
    {
        $precio_unitario = $precio_unitario_input;
        $porcentajeImpuesto = optional($producto->afectacionTipo)->porcentaje ?? 0;

        $valor_unitario = $porcentajeImpuesto > 0 
            ? $precio_unitario / (1 + $porcentajeImpuesto) 
            : $precio_unitario;

        $subtotal = $valor_unitario * $cantidad;
        $detalleImpuesto = ($precio_unitario - $valor_unitario) * $cantidad;
        $detalleTotal = $precio_unitario * $cantidad;

        // Acumular totales
        if ($producto->afectacion_tipo_codigo == '10') {
            $totales['op_gravada'] += $subtotal;
        } elseif ($producto->afectacion_tipo_codigo == '20') {
            $totales['op_exonerada'] += $subtotal;
        } elseif ($producto->afectacion_tipo_codigo == '30') {
            $totales['op_inafecta'] += $subtotal;
        }

        $totales['impuesto'] += $detalleImpuesto;
        $totales['total'] += $detalleTotal;

        // Devolver el detalle calculado listo para la BD
        return [
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'valor_unitario' => round($valor_unitario, 2),
            'porcentaje_impuesto' => $porcentajeImpuesto,
            'impuesto' => round($detalleImpuesto, 2),
            'precio_unitario' => $precio_unitario,
            'total' => round($detalleTotal, 2),
        ];
    }

    protected function validateData(Request $request, $id = null)
    {
        return $request->validate([
            // Cabecera de la venta
            'proveedor_id' => 'required|exists:proveedores,id',
            'comprobante_tipo_codigo' => 'required|exists:comprobante_tipos,codigo',
            'serie' => 'required|string',
            'correlativo' => 'required|integer|min:1',
            'forma_pago' => 'required|string|in:contado,credito',

            // Detalles
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0.01'
        ]);
    }


    public function printTicket($id){
        $compra = Compra::with(['proveedor','detalles.producto.afectacionTipo'])->findOrFail($id);

        $empresa = (object)[
            'razon_social' => 'Proyecto - Sena - Adso',
            'direccion' => 'Av. Carrera 14 con Calle 65-14',
            'ruc' => '1010101010'
        ];

        $pdf = Pdf::loadView('compras.ticket', compact('compra','empresa'))
                ->setPaper([0,0,240,800]);

        return $pdf->stream("ticket_{$compra->id}.pdf");
    }
}