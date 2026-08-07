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
    private $startDate;
    private $endDate;
    private $rowNumber = 0;

    public function __construct(string $search = '', string $filter = '', string $startDate = '', string $endDate = '')
    {
        $this->search = $search;
        $this->filter = $filter;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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

        if ($this->startDate !== '') {
            $query->whereDate('waktu_pengecekan', '>=', $this->startDate);
        }

        if ($this->endDate !== '') {
            $query->whereDate('waktu_pengecekan', '<=', $this->endDate);
        }

        return $query->orderBy('waktu_pengecekan', 'desc');
    }

    public function map($log): array
    {
        $this->rowNumber++;
        $koordinat = ($log->lokasi->latitude ?? '') && ($log->lokasi->longitude ?? '') 
            ? $log->lokasi->latitude . ', ' . $log->lokasi->longitude 
            : '-';

        return [
            $this->rowNumber,
            $log->waktu_pengecekan->format('d/m/Y H:i'),
            $log->lokasi->no_sr ?? '-',
            $log->lokasi->nama_pelanggan ?? '-',
            $log->lokasi->alamat ?? '-',
            $log->lokasi->desa ?? '-',
            $koordinat,
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
            // Row 4: Filter Waktu
            ['Periode: ' . ($this->startDate ? \Carbon\Carbon::parse($this->startDate)->format('d M Y') : 'Awal') . ' s/d ' . ($this->endDate ? \Carbon\Carbon::parse($this->endDate)->format('d M Y') : 'Akhir')],
            // Row 5: Empty line
            [],
            // Row 6: Column Headers
            [
                'No.',
                'Waktu Pengecekan',
                'No. SR',
                'Nama Pelanggan',
                'Alamat',
                'Desa',
                'Koordinat (Lat, Lng)',
                'Nilai Tekanan (Bar)',
                'Status',
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => '#,##0.00',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style for Column Headers
            6    => [
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
            4 => ['font' => ['italic' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Merge title cells
                $sheet->mergeCells('A1:I1');
                $sheet->mergeCells('A2:I2');
                $sheet->mergeCells('A3:I3');
                $sheet->mergeCells('A4:I4');
                
                $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Get highest row
                $highestRow = $sheet->getHighestRow();
                
                if ($highestRow >= 6) {
                    $cellRange = 'A6:I' . $highestRow;
                    
                    // Apply borders
                    $sheet->getStyle($cellRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FFD1D5DB'], // Slate-300
                            ],
                        ],
                    ]);
                    
                    // Alignment for data columns (No, Waktu, No SR)
                    $sheet->getStyle('A7:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    // Nilai Tekanan, Koordinat, Status
                    $sheet->getStyle('G7:I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Add color coding to Status column based on GD
                    for ($row = 7; $row <= $highestRow; $row++) {
                        $status = $sheet->getCell('I' . $row)->getValue();
                        
                        $color = 'FF64748B'; // Default Slate
                        if ($status === 'Normal') {
                            $color = 'FF10B981'; // Green
                        } elseif ($status === 'Rendah') {
                            $color = 'FFF59E0B'; // Amber
                        } elseif ($status === 'Kritis') {
                            $color = 'FFEF4444'; // Red
                        }
                        
                        $sheet->getStyle('I' . $row)->getFont()->setBold(true)->getColor()->setARGB($color);
                    }
                }
            },
        ];
    }
}
