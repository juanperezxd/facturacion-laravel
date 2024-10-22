<?php

namespace App\Exports;

//hojas
use App\Exports\ContableCompendioFacturasExport;
use App\Exports\ContableCompendioFNotasExport;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;//se puedes descargar en el controller , sin necesidad de la fachada Excel::
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ContableCompendioFExport implements FromCollection, WithMultipleSheets
{
    use Exportable;

    public function __construct($facturas, $notas, $fecha)
    {
        $this->facturas = $facturas;
        $this->notas = $notas;
        $this->fecha = $fecha;

    }

    public function collection()
    {
        return $this->facturas;
    }

    public function sheets(): array
    {
        $sheets = [];
        //hoja 1
        $sheets[] = new ContableCompendioFacturasExport($this->facturas, 'FACTURAS', $this->fecha);
        //hoja 2
        $sheets[] = new ContableCompendioFNotasExport($this->notas, 'NOTAS', $this->fecha);
      
        return $sheets;
    }
}
