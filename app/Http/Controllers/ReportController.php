<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Http\Exports\ProductReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function downloadReport($file){
        return response()->download(storage_path('app/reports/'. $file));
    }
}
