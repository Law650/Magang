<?php

namespace App\Exports;

use App\Models\LogTekanan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class LogTekananExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    use Exportable;

    private $search;
    private $filter;

    public function __construct(string $search = '', string $filter = '')
    {
        $this->search = $search;
        $this->filter = $filter;
    }

    public function query()
    {
        $query = LogTekanan::query()->with('lokasi');

        if ($this->filter !== '') {
            $query->where('status', $this->filter);
        }

        if ($this->search !== '') {
            $searchTerm = '%' . $this->search . '%';
            $query->whereHas('lokasi', fn ($q) => $q->where('nama_lokasi', 'like', $searchTerm));
        }

        return $query->orderBy('waktu_pengecekan', 'desc');
    }

    public function map($log): array
    {
        return [
            $log->waktu_pengecekan->format('d/m/Y H:i'),
            $log->lokasi->nama_lokasi ?? '-',
            $log->nilai_tekanan,
            ucfirst($log->status),
        ];
    }

    public function headings(): array
    {
        return [
            // Row 1: Title
            ['LAPORAN MONITORING TEKANAN AIR'],
            // Row 2: Metadata
            ['Waktu Cetak: ' . now()->format('d F Y H:i')],
            // Row 3: Filter Info
            ['Filter Status: ' . ($this->filter ? ucfirst($this->filter) : 'Semua') . ' | Pencarian: ' . ($this->search ?: '-')],
            // Row 4: Empty line
            [],
            // Row 5: Column Headers
            [
                'Waktu Pengecekan',
                'Lokasi',
                'Nilai Tekanan (Bar)',
                'Status',
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => '#,##0.00',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style for Column Headers
            5    => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0F172A'], // Slate-900
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Title styles
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['italic' => true]],
            3 => ['font' => ['italic' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Merge title cells
                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');
                $sheet->mergeCells('A3:D3');
                
                $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Get highest row
                $highestRow = $sheet->getHighestRow();
                
                if ($highestRow >= 5) {
                    $cellRange = 'A5:D' . $highestRow;
                    
                    // Apply borders
                    $sheet->getStyle($cellRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FFD1D5DB'], // Slate-300
                            ],
                        ],
                    ]);
                    
                    // Alignment for data columns
                    $sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('C6:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Add color coding to Status column based on GD
                    for ($row = 6; $row <= $highestRow; $row++) {
                        $status = $sheet->getCell('D' . $row)->getValue();
                        
                        $color = 'FF64748B'; // Default Slate
                        if ($status === 'Normal') {
                            $color = 'FF10B981'; // Green
                        } elseif ($status === 'Rendah') {
                            $color = 'FFF59E0B'; // Amber
                        } elseif ($status === 'Kritis') {
                            $color = 'FFEF4444'; // Red
                        }
                        
                        $sheet->getStyle('D' . $row)->getFont()->setBold(true)->getColor()->setARGB($color);
                    }
                }
            },
        ];
    }
}
