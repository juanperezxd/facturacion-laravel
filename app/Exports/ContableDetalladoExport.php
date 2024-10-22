<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
//use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Facturas;
use Maatwebsite\Excel\Concerns\WithTitle;//titulo hoja

//ESTILOS
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\AfterSheet;

class ContableDetalladoExport implements FromView, WithTitle, ShouldAutoSize,  WithEvents
{
    use Exportable;

    use RegistersEventListeners;

    public function __construct($coleccion, $fecha)
    {
        $this->coleccion = $coleccion;
        $this->fecha = $fecha;
    }

    public function view(): View
    {
        return view('exports.detallado', [
            'facturas' => $this->coleccion,
            'fecha' => $this->fecha
        ]);
    }

    
    public function title(): string
    {
        return 'Detalle Facturas';
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
                    $event->sheet->mergeCells('A1:K1');
                    $event->sheet->mergeCells('A2:K2');
                    //centrar
                    $event->sheet->getStyle('A1:K1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $event->sheet->getStyle('A2:K2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    //fondo
                    $event->sheet->getStyle('A1:K1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                    $event->sheet->getStyle('A1:K1')->getFill()->getStartColor()->setARGB('FFFF00');
                    $event->sheet->getStyle('A1:K1')->getFill()->getEndColor()->setARGB('FFFF00');
                    $event->sheet->getStyle('A2:K2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                    $event->sheet->getStyle('A2:K2')->getFill()->getStartColor()->setARGB('FFFF00');
                    $event->sheet->getStyle('A2:K2')->getFill()->getEndColor()->setARGB('FFFF00');
                    //font-size
                    $event->sheet->getStyle('A1:K1')->getFont()->setBold(true);
                    $event->sheet->getStyle('A2:K2')->getFont()->setBold(true);
                //TABLA
                    $event->sheet->getStyle('A4:K4')->applyFromArray($styleArray);
                    //generales
                    $event->sheet->getStyle('A4:K4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                    $event->sheet->getStyle('A4:K4')->getFont()->setBold(true);
                    //$event->sheet->getStyle('A4:AD1')->getFont()->setColor( new \PhpOffice\PhpSpreadsheet\Style\Color( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE ) );
                    $event->sheet->getStyle('A4:K4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $event->sheet->getStyle('A4:K4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    //backgrounds
                    //amarillo
                    $event->sheet->getStyle('A4:K4')->getFill()->getStartColor()->setARGB('CCCCCC');
                    $event->sheet->getStyle('A4:K4')->getFill()->getEndColor()->setARGB('CCCCCC');
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
