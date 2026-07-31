<?php

namespace App\Exports;

use App\Models\LogValve;
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

class LogValveExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    use Exportable;

    private $search;
    private $filter;
    private $startDate;
    private $endDate;

    public function __construct(string $search = '', string $filter = '', string $startDate = '', string $endDate = '')
    {
        $this->search = $search;
        $this->filter = $filter;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        $query = LogValve::query()->with(['asetValve.lokasi']);

        if ($this->filter !== '') {
            if ($this->filter === 'cek') {
                $query->where('aksi_kerja', 'buka')->where('jumlah_putaran', 0);
            } elseif ($this->filter === 'buka') {
                $query->where('aksi_kerja', 'buka')->where('jumlah_putaran', '>', 0);
            } else {
                $query->where('aksi_kerja', $this->filter);
            }
        }

        if ($this->search !== '') {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_teknisi', 'like', $searchTerm)
                    ->orWhereHas('asetValve', fn ($q) => $q->where('nama_aset', 'like', $searchTerm))
                    ->orWhereHas('asetValve.lokasi', fn ($q) => $q->where('nama_lokasi', 'like', $searchTerm));
            });
        }

        if ($this->startDate !== '') {
            $query->whereDate('waktu_kegiatan', '>=', $this->startDate);
        }

        if ($this->endDate !== '') {
            $query->whereDate('waktu_kegiatan', '<=', $this->endDate);
        }

        return $query->orderBy('waktu_kegiatan', 'desc');
    }

    public function map($log): array
    {
        return [
            $log->waktu_kegiatan->format('d/m/Y H:i'),
            $log->nama_teknisi,
            $log->asetValve->nama_aset,
            $log->asetValve->lokasi->nama_lokasi ?? '-',
            ($log->aksi_kerja === 'buka' && (float)$log->jumlah_putaran == 0) ? 'Cek' : ucfirst($log->aksi_kerja),
            $log->jumlah_putaran,
            $log->snapshot_sisa_bukaan,
            $log->snapshot_total_tutupan,
            // $log->status_radius, // Fitur dinonaktifkan sementara
            $log->keterangan ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            // Row 1: Title
            ['LAPORAN RIWAYAT AKTIVITAS KATUP (LOG VALVE)'],
            // Row 2: Metadata
            ['Waktu Cetak: ' . now()->format('d F Y H:i')],
            // Row 3: Filter Info
            ['Filter Aksi: ' . ($this->filter ? ucfirst($this->filter) : 'Semua') . ' | Pencarian: ' . ($this->search ?: '-')],
            // Row 4: Filter Waktu
            ['Periode: ' . ($this->startDate ? \Carbon\Carbon::parse($this->startDate)->format('d M Y') : 'Awal') . ' s/d ' . ($this->endDate ? \Carbon\Carbon::parse($this->endDate)->format('d M Y') : 'Akhir')],
            // Row 5: Empty line
            [],
            // Row 6: Column Headers
            [
                'Waktu Kegiatan',
                'Nama Teknisi',
                'Nama Aset',
                'Lokasi',
                'Aksi',
                'Jumlah Putaran',
                'Sisa Bukaan',
                'Total Tutupan',
                // 'Status Radius', // Fitur dinonaktifkan sementara
                'Keterangan',
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '#,##0.00',
            'G' => '#,##0.00',
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

                // Get highest row and column
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
                    
                    // Alignment for data columns
                    $sheet->getStyle('A7:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('E7:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('F7:H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    // $sheet->getStyle('I7:I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
