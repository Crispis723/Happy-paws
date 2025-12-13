<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
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

class VentaController extends Controller
{

    public function __construct(){
        $this->middleware('can:ventas_list')->only(['index']);
        $this->middleware('can:ventas_create')->only(['store']);
        $this->middleware('can:ventas_edit')->only(['show', 'update']);
        $this->middleware('can:ventas_delete')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Venta::with(['cliente', 'user', 'comprobanteTipo' ])
             ->select(['id', 'fecha', 'forma_pago', 'serie', 'correlativo', 'impuesto', 'total', 'estado', 
             'cliente_id', 'user_id', 'comprobante_tipo_codigo'])->orderBy('id', 'desc');

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $editButton ='';
                    if(auth()->user()->can('ventas_edit')){
                        $editButton = view('components.button-edit', ['id' => $row->id])->render();
                    }
                    $deleteButton = '';
                    if(auth()->user()->can('ventas_delete')){
                        $deleteButton = view('components.button-delete', ['id' => $row->id])->render();
                    }
                    $ticketButton= '<a href="' . route('ventas.imprimir', $row->id) . '" 
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
                ->editColumn('cliente', function ($row) {
                    return optional($row->cliente)->razon_social;
                })
                ->editColumn('tipo_comprobante', function ($row) {
                    return optional($row->comprobanteTipo)->descripcion;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('ventas.index');
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
            $ventaData = $this->processVentaData($data, true);
            
            $venta = Venta::create($ventaData['venta']);
            $venta->detalles()->createMany($ventaData['detalles']);
            Producto::updateStock(false,$ventaData['detalles']);

            ComprobanteSerie::where('comprobante_tipo_codigo', $request->comprobante_tipo_codigo)
                ->where('serie', $request->serie)
                ->update([
                    'correlativo' => $request->correlativo + 1
                ]);

            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Registro creado satisfactoriamente',
                'venta_id' => $venta->id
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
            $registro = Venta::with([
                'detalles.producto' => function ($query) {
                    $query->select('id', 'afectacion_tipo_codigo', 'codigo', 'nombre', 'precio_unitario');
                },
                'detalles.producto.afectacionTipo' => function ($query) {
                    $query->select('codigo', 'descripcion', 'porcentaje');
                },
                'cliente' => function ($query) {
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
    public function edit(Venta $venta)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $venta = Venta::findOrFail($id);
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            $ventaData = $this->processVentaData($data, false);
            
            $venta->update($ventaData['venta']);
            Producto::updateStock(true,$venta->detalles->toArray());
            $venta->detalles()->delete();
            $venta->detalles()->createMany($ventaData['detalles']);
            Producto::updateStock(false,$ventaData['detalles']);
            
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
            $registro = Venta::findOrFail($id);
            Producto::updateStock(true,$registro->detalles->toArray());           
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
    private function processVentaData(array $data, bool $isNew = true)
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
                $totales
            );
        }

        // Armar cabecera de la venta
        $ventaData = [
            'cliente_id' => $data['cliente_id'],
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
            $ventaData['fecha'] = now();
            $ventaData['user_id'] = auth()->id();
            $ventaData['estado'] = 'registrado';
        }

        return [
            'venta' => $ventaData,
            'detalles' => $detallesCalculados
        ];
    }

    private function calculateAndAddDetailTotals($producto, $cantidad, array &$totales)
    {
        $precio_unitario = $producto->precio_unitario;
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
            'cliente_id' => 'required|exists:clientes,id',
            'comprobante_tipo_codigo' => 'required|exists:comprobante_tipos,codigo',
            'serie' => 'required|string',
            'correlativo' => 'required|integer|min:1',
            'forma_pago' => 'required|string|in:contado,credito',

            // Detalles
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|numeric|min:0.01'
        ]);
    }

    public function getSerie(Request $request)
    {
        $request->validate([
            'comprobante_tipo_codigo' => 'required|exists:comprobante_tipos,codigo'
        ]);

        $codigo = $request->comprobante_tipo_codigo;

        // Buscar directamente en comprobante_series
        $serieConfig = ComprobanteSerie::where('comprobante_tipo_codigo', $codigo)->first();

        // Si no existe, devolver null
        if (!$serieConfig) {
            return response()->json([
                'serie' => null,
                'numero' => null
            ]);
        }
        // Devolver los valores almacenados en la tabla
        return response()->json([
            'serie' => $serieConfig->serie,
            'numero' => $serieConfig->correlativo
        ]);
    }

    public function printTicket($id){
        $venta = Venta::with(['cliente','detalles.producto.afectacionTipo'])->findOrFail($id);

        $empresa = (object)[
            'razon_social' => 'Proyecto - Sena - Adso',
            'direccion' => 'Av. Carrera 14 con Calle 65-14',
            'ruc' => '1010101010'
        ];

        // Construir contenido QR (SUNAT) | Alt + 124
        $qrContent = implode('|', [
            $empresa->ruc,
            $venta->comprobante_tipo_codigo,
            $venta->serie,
            str_pad($venta->correlativo,8,'0',STR_PAD_LEFT),
            number_format($venta->impuesto,2,'.',''),
            number_format($venta->total,2,'.',''),
            \Carbon\Carbon::parse($venta->created_at)->format('d/m/Y'),
            $venta->cliente->documento_tipo_codigo,
            $venta->cliente->numero_documento
        ]);

        try {
            $renderer = new ImageRenderer(
                new RendererStyle(120, 1),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrSvg = $writer->writeString($qrContent);
            
            // Convertir SVG a base64 para usar en el PDF
            $qr = base64_encode($qrSvg);
            $qrDataUri = 'data:image/svg+xml;base64,' . $qr;
            
        } catch (\Exception $e) {
            // Fallback: crear QR simple usando una imagen en blanco si falla
            $qr = null;
        }

        $pdf = Pdf::loadView('ventas.ticket', compact('venta','empresa','qr','qrDataUri'))
                ->setPaper([0,0,240,800]);

        return $pdf->stream("ticket_{$venta->id}.pdf");
    }
}