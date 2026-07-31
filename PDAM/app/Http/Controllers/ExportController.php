<?php

namespace App\Http\Controllers;

use App\Exports\LogTekananExport;
use App\Exports\LogValveExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    /**
     * Export log valve ke Excel.
     */
    public function logValve(Request $request): BinaryFileResponse
    {
        $search = $request->query('search', '');
        $filter = $request->query('filter', '');
        $startDate = $request->query('start_date', '');
        $endDate = $request->query('end_date', '');
        
        $filename = 'laporan_log_valve_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new LogValveExport($search, $filter, $startDate, $endDate), $filename);
    }

    /**
     * Export log tekanan ke Excel.
     */
    public function logTekanan(Request $request): BinaryFileResponse
    {
        $search = $request->query('search', '');
        $filter = $request->query('filter', '');
        $startDate = $request->query('start_date', '');
        $endDate = $request->query('end_date', '');
        
        $filename = 'laporan_log_tekanan_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new LogTekananExport($search, $filter, $startDate, $endDate), $filename);
    }
}
