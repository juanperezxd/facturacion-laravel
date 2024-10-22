<?php

namespace App\Exports;

//hojas
use App\Exports\ContableCompendioBoletasExport;
use App\Exports\ContableCompendioBNotasExport;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;//se puedes descargar en el controller , sin necesidad de la fachada Excel::
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ContableCompendioBExport implements FromCollection, WithMultipleSheets
{
    use Exportable;

    public function __construct($boletas, $notas, $fecha)
    {
        $this->boletas = $boletas;
        $this->notas = $notas;
        $this->fecha = $fecha;

    }

    public function collection()
    {
        return $this->boletas;
    }

    public function sheets(): array
    {
        $sheets = [];
        //hoja 1
        $sheets[] = new ContableCompendioBoletasExport($this->boletas, 'BOLETAS', $this->fecha);
        //hoja 2
        $sheets[] = new ContableCompendioBNotasExport($this->notas, 'NOTAS', $this->fecha);
      
        return $sheets;
    }
}
