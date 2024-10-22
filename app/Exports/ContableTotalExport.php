<?php

namespace App\Exports;

//hojas
use App\Exports\ContableTotalBoletaExport;
use App\Exports\ContableTotalFacturaExport;
use App\Exports\ContableTotalNotaExport;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;//se puedes descargar en el controller , sin necesidad de la fachada Excel::
use Maatwebsite\Excel\Concerns\WithMultipleSheets;


class ContableTotalExport implements FromCollection, WithMultipleSheets
{
    use Exportable;
    
    public function __construct($boletas, $facturas, $notas, $nombre_mes, $anio)
    {
        $this->boletas = $boletas;
        $this->facturas = $facturas;
        $this->notas = $notas;
        $this->nombre_mes = $nombre_mes;
        $this->anio = $anio;
    }


    public function collection()
    {
        return $this->boletas;
    }

    public function sheets(): array
    {
        $sheets = [];
        //hoja 1
        $sheets[] = new ContableTotalBoletaExport($this->boletas, 'BOLETAS', $this->nombre_mes, $this->anio);
        //hoja 2
        $sheets[] = new ContableTotalFacturaExport($this->facturas, 'FACTURAS', $this->nombre_mes, $this->anio);
        //hoja 3
        $sheets[] = new ContableTotalNotaExport($this->notas, 'NOTAS DE CREDITO', $this->nombre_mes, $this->anio);


        return $sheets;
    }
}
