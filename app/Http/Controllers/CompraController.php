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

    public function __construct()
    {
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
        // Obtener compras paginadas (10 por página)
        // with() = cargar relaciones (evita N+1)
        // orderBy() = ordenar por más reciente primero
        $compras = Compra::with(['proveedor', 'comprobanteTipo', 'user'])
            ->orderBy('fecha', 'desc')
            ->paginate(10);

        // Pasar a la vista
        return view('compras.index', compact('compras'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener proveedores (para dropdown)
        $proveedores = \App\Models\Proveedor::all();

        // Obtener tipos de comprobante (Factura, Boleta, etc)
        $comprobanteTipos = \App\Models\ComprobanteTipo::all();

        // Obtener productos (para agregar a detalles)
        $productos = \App\Models\Producto::all();

        // Enviar a la vista
        return view('compras.create', compact('proveedores', 'comprobanteTipos', 'productos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. VALIDAR DATOS
        $data = $this->validateCompra($request);

        // 2. CREAR COMPRA EN BD
        $compra = Compra::create([
            'fecha' => $data['fecha'],
            'proveedor_id' => $data['proveedor_id'],
            'comprobante_tipo_codigo' => $data['comprobante_tipo_codigo'],
            'forma_pago' => $data['forma_pago'],
            'total' => $data['total'],
            'estado' => 'pendiente',
            'user_id' => auth()->id(),  // Usuario que la crea
        ]);

        // 3. REDIRIGIR
        return redirect()->route('compras.show', $compra->id)
            ->with('success', 'Compra creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            // Obtener compra con sus relaciones
            $compra = Compra::with(['proveedor', 'comprobanteTipo', 'user', 'detalles'])
                ->findOrFail($id);

            return view('compras.show', compact('compra'));
        } catch (\Exception $e) {
            return redirect()->route('compras.index')
                ->with('error', 'Compra no encontrada.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Obtener la compra
        $compra = Compra::findOrFail($id);

        // Obtener opciones para dropdowns
        $proveedores = \App\Models\Proveedor::all();
        $comprobanteTipos = \App\Models\ComprobanteTipo::all();

        return view('compras.edit', compact('compra', 'proveedores', 'comprobanteTipos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validar
        $data = $this->validateCompra($request, $id);

        // Obtener compra
        $compra = Compra::findOrFail($id);

        // Actualizar
        $compra->update($data);

        // Redirigir
        return redirect()->route('compras.show', $compra->id)
            ->with('success', 'Compra actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $compra = Compra::findOrFail($id);
            $compra->delete();

            return redirect()->route('compras.index')
                ->with('success', 'Compra eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('compras.index')
                ->with('error', 'Error al eliminar.');
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


    public function printTicket($id)
    {
        $compra = Compra::with(['proveedor', 'detalles.producto.afectacionTipo'])->findOrFail($id);

        $empresa = (object) [
            'razon_social' => 'Proyecto - Sena - Adso',
            'direccion' => 'Av. Carrera 14 con Calle 65-14',
            'ruc' => '1010101010'
        ];

        // La vista existente está en recursos/views/compras/partials/ticket.blade.php
        $pdf = Pdf::loadView('compras.partials.ticket', compact('compra', 'empresa'))
            ->setPaper([0, 0, 240, 800]);

        return $pdf->stream("ticket_{$compra->id}.pdf");
    }

    protected function validateCompra(Request $request, $id = null)
    {
        return $request->validate([
            'fecha' => 'required|date',
            'proveedor_id' => 'required|exists:proveedores,id',
            'comprobante_tipo_codigo' => 'required|exists:comprobante_tipos,codigo',
            'forma_pago' => 'required|in:efectivo,cheque,transferencia,tarjeta',
            'total' => 'required|numeric|min:0',
        ]);
    }
}

