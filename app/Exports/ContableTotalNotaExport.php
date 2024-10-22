<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;//mapear los datos
use Maatwebsite\Excel\Concerns\WithHeadings;//CABEZERA
use Maatwebsite\Excel\Concerns\WithTitle;//titulo hoja

//ESTILOS
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\AfterSheet;

class ContableTotalNotaExport implements FromCollection, WithMapping, WithHeadings, WithTitle, ShouldAutoSize,  WithEvents
{
    use RegistersEventListeners;

    public function __construct($coleccion, $hoja, $nombre_mes, $anio)
    {
        $this->coleccion = $coleccion;
        $this->hoja = $hoja;
        $this->nombre_mes = $nombre_mes;
        $this->anio = $anio;

    }

    public function map($coleccion): array
    {
        return [
            $coleccion->id,
            $coleccion->cliente_setNumDoc,
            $coleccion->cliente_setRznSocial,
            $coleccion->setFechaEmision,
            $coleccion->documento,
            $coleccion->setMtoImpVenta
        ];
    }

    public function headings(): array
    {
        $titulo = 'RESUMEN DE TOTALES '.$this->nombre_mes.' '.$this->anio;
        return [
            ['SOFER'],
            [$titulo],
            [''],
           ['ID',
           'RUC/DNI',
           'RAZON SOCIAL',
           'FECHA',
           'DOCUMENTO',
           'TOTAL']
        ];
    }

    public function title(): string
    {
        return $this->hoja;
    }

    public function collection()
    {
        return $this->coleccion;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                //border
                $styleArray = [
                    'borders' => [
                        'vertical' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '0000000'],
                        ],
                    ],
                ];

                //CABEZERA
                    //unir filas
                    $event->sheet->mergeCells('A1:F1');
                    $event->sheet->mergeCells('A2:F2');
                    //centrar
                    $event->sheet->getStyle('A1:F1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $event->sheet->getStyle('A2:F2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    //fondo
                    $event->sheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                    $event->sheet->getStyle('A1:F1')->getFill()->getStartColor()->setARGB('FFFF00');
                    $event->sheet->getStyle('A1:F1')->getFill()->getEndColor()->setARGB('FFFF00');
                    $event->sheet->getStyle('A2:F2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                    $event->sheet->getStyle('A2:F2')->getFill()->getStartColor()->setARGB('FFFF00');
                    $event->sheet->getStyle('A2:F2')->getFill()->getEndColor()->setARGB('FFFF00');
                    //font-size
                    $event->sheet->getStyle('A1:F1')->getFont()->setBold(true);
                    $event->sheet->getStyle('A2:F2')->getFont()->setBold(true);
                //TABLA
                    $event->sheet->getStyle('A4:F4')->applyFromArray($styleArray);
                    //generales
                    $event->sheet->getStyle('A4:F4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                    $event->sheet->getStyle('A4:F4')->getFont()->setBold(true);
                    //$event->sheet->getStyle('A4:AD1')->getFont()->setColor( new \PhpOffice\PhpSpreadsheet\Style\Color( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE ) );
                    $event->sheet->getStyle('A4:F4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $event->sheet->getStyle('A4:F4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    //backgrounds
                    //amarillo
                    $event->sheet->getStyle('A4:F4')->getFill()->getStartColor()->setARGB('CCCCCC');
                    $event->sheet->getStyle('A4:F4')->getFill()->getEndColor()->setARGB('CCCCCC');
                    //anchos
                    $event->sheet->getColumnDimension('A')->setAutoSize(true);
                    $event->sheet->getColumnDimension('B')->setAutoSize(true);
                    $event->sheet->getColumnDimension('C')->setAutoSize(true);
                    $event->sheet->getColumnDimension('D')->setAutoSize(true);
                    $event->sheet->getColumnDimension('E')->setAutoSize(true);
                    //alto fila
                    $event->sheet->getRowDimension('4')->setRowHeight(20);
            }
        ];
    }
}
