<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;//se puedes descargar en el controller , sin necesidad de la fachada Excel::
use Maatwebsite\Excel\Concerns\WithMapping;//mapear los datos
use Maatwebsite\Excel\Concerns\WithHeadings;//CABEZERA
use Maatwebsite\Excel\Concerns\WithTitle;//titulo hoja

//ESTILOS
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\AfterSheet;

class ReportesGeneralesExport implements FromCollection, WithMapping, WithHeadings, WithTitle, ShouldAutoSize,  WithEvents
{
    use Exportable;
    use RegistersEventListeners;

    //inicializar variables
    public function __construct($reporte, $coleccion, $titulo)
    {
        $this->reporte = $reporte;
        $this->coleccion = $coleccion;
        $this->titulo = $titulo;
    }

    public function map($coleccion): array
    {
        if ($this->reporte == 'reporte_ventas') {
           
            $total = 0;
            $total = number_format(58.3, 3, ".", "");

            return [
                $coleccion->razon_social,
                $total,
                $coleccion->fecha,
                $coleccion->documentos,
                $coleccion->estado
            ];
        }else if($this->reporte == 'reporte_gastos'){

            $caja = '';
            if ($coleccion->caja == 1) {
               $caja = 'SI';
            }else {
                $caja = 'NO';
            }

            return [
                $coleccion->descripcion,
                'S/. '.$coleccion->monto,
                $coleccion->fecha,
                $caja,
                $coleccion->usuario
            ];
        }else if($this->reporte == 'reporte_cajas'){
            return [
              $coleccion->tipo_movimiento,
              $coleccion->descripcion,
              $coleccion->fecha,
              $coleccion->monto
            ];
        }else if($this->reporte=='reporte_boletas' || $this->reporte == 'reporte_facturas' || $this->reporte =='reporte_notas'){
          return[
            $coleccion->razon_social,
            $coleccion->total,
            $coleccion->fecha,
            $coleccion->documentos,
            $coleccion->estado,
          ];
        }
    }

    public function headings(): array
    {
        if ($this->reporte == 'reporte_ventas') {
            return [
                'CLIENTE',
                'TOTAL',
                'FECHA',
                'DOCUMENTO',
                'ESTADO'
            ];
        }else if($this->reporte == 'reporte_gastos'){
            return [
                'DESCRIPCION',
                'MONTO',
                'FECHA',
                'CAJA',
                'USUARIO'
            ];
        }else if($this->reporte == 'reporte_cajas'){
            return [
                'TIPO MOVIMIENTO',
                'DESCRIPCION',
                'FECHA',
                'MONTO'
            ];
        }else if($this->reporte=='reporte_boletas' || $this->reporte == 'reporte_facturas' || $this->reporte =='reporte_notas'){
          return[
            'CLIENTE',
            'TOTAL',
            'FECHA',
            'DOCUMENTO',
            'ESTADO',
          ];
        }
    }

    public function title(): string
    {
        return $this->titulo;
    }

    public function collection()
    {
        return $this->coleccion;
    }
}
