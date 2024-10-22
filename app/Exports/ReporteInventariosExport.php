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

class ReporteInventariosExport implements FromCollection, WithMapping, WithHeadings, WithTitle, ShouldAutoSize,  WithEvents
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
        if ($this->reporte == 'kardex_general_fecha' || $this->reporte == 'kardex_general_producto' || $this->reporte == 'ingreso_material_fecha' || $this->reporte == 'ingreso_material_producto' || $this->reporte == 'salida_materiales_fecha' || $this->reporte == 'salida_materiales_producto' || $this->reporte == 'salida_materiales_colaborador') {
            $tipo = '';
            $saldo = '';
            if($coleccion->tipo == "IN" or $coleccion->tipo=="DE"){
                $saldo = $coleccion->saldo;
            }else{
                $saldo='('. $coleccion->saldo.')';
            }
            if($coleccion->tipo=="IN"){
                $tipo="INGRESO";
            }else if($coleccion->tipo=="RE"){
                if ($coleccion->tipo_movimiento == 'VENTA') {
                    $tipo = 'VENTA';
                }else {
                    $tipo="RETIRO";
                }
            }else if($coleccion->tipo=="DE"){
                $tipo="DEVOLUCION";
            }

            return [
                $coleccion->tipo_doc,
                $coleccion->num_doc,
                $coleccion->responsable,
                $coleccion->fecha,
                $tipo,
                $coleccion->producto,
                $coleccion->cantidad,
                $saldo
            ];
        }else if($this->reporte == 'stock_productos'){
            return [
                $coleccion->codigo,
                $coleccion->nombre,
                $coleccion->categoria,
                $coleccion->unidad,
                $coleccion->stock,
                'S/. '.$coleccion->precio
            ];
        }else if($this->reporte == 'kardex_tecnico'){
            return [
              $coleccion->colaborador,
              $coleccion->producto,
              $coleccion->saldo
            ];
        }else if($this->reporte=='descarga_contrato' || $this->reporte == 'descarga_tecnico' || $this->reporte =='descarga_fecha'){
          return[
            $coleccion->id,
            $coleccion->concepto,
            $coleccion->responsable,
            $coleccion->fecha,
            $coleccion->colaborador,
            $coleccion->observaciones,
            $coleccion->descripcion,
            $coleccion->cantidad
          ];
        }
    }

    public function headings(): array
    {
        if ($this->reporte == 'kardex_general_fecha' || $this->reporte == 'kardex_general_producto' || $this->reporte == 'ingreso_material_fecha' || $this->reporte == 'ingreso_material_producto' || $this->reporte == 'salida_materiales_fecha' || $this->reporte == 'salida_materiales_producto' || $this->reporte == 'salida_materiales_colaborador') {
            return [
                'TIPO DE DOCUMENTO',
                'NUM. DOCUMENTO',
                'RESPONSABLE',
                'FECHA DE MOVIMIENTO',
                'TIPO DE MOVIMIENTO',
                'PRODUCTO',
                'CANTIDAD',
                'SALDO'
            ];
        }else if($this->reporte == 'stock_productos'){
            return [
                'CODIGO',
                'DESCRIPCION',
                'CATEGORIA',
                'UNIDAD',
                'STOCK',
                'PRECIO'
            ];
        }else if($this->reporte == 'kardex_tecnico'){
            return [
                'COLABORADOR',
                'PRODUCTO',
                'STOCK'
            ];
        }else if($this->reporte=='descarga_contrato' || $this->reporte == 'descarga_tecnico' || $this->reporte =='descarga_fecha'){
          return[
            'ID',
            'TIPO DE DESCARGA',
            'CONTRATO',
            'FECHA',
            'COLABORADOR',
            'OBSERVACION',
            'PRODUCTO',
            'CANTIDAD'
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
