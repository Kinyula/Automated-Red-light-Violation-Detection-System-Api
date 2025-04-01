<?php

namespace App\Http\Controllers;

use App\Exports\DriverListExport;
use App\Imports\DriverImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelActivityController extends Controller
{
    public function export()
    {
        return Excel::download(new DriverListExport, 'driver_list.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate('file', 'required|mimes:xlsx');
        Excel::import(new DriverImport, $request->file('file'));
        return response()->json([
            'message' => 'File imported successfully'
        ]);
    }
}
