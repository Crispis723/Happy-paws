<?php

namespace App\Exports;

use App\Models\Venta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;

class VentasExport implements FromCollection, WithHeadings, WithMapping
{

    protected $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Venta::with(['user', 'cliente']);

        if (!empty($this->filtros['fecha_inicio'])) {
            $query->whereDate('created_at', '>=', $this->filtros['fecha_inicio']);
        }

        if (!empty($this->filtros['fecha_fin'])) {
            $query->whereDate('created_at', '<=', $this->filtros['fecha_fin']);
        }

        if (!empty($this->filtros['user_id'])) {
            $query->where('user_id', $this->filtros['user_id']);
        }

        return $query->get();
    }

    public function map($venta): array
    {
        return [
            $venta->created_at->format('Y-m-d H:i:s'),
            $venta->comprobanteTipo->descripcion,
            $venta->serie,
            $venta->correlativo,
            optional($venta->cliente)->razon_social ?? 'Sin cliente',
            optional($venta->user)->name,
            $venta->total,
        ];
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Tipo Comprobante',
            'Serie',
            'Correlativo',
            'Cliente',
            'Vendedor',
            'Total',
        ];
    }

}
