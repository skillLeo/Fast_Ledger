<?php

namespace App\Http\Controllers\Report;

use App\Models\File;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

class FileOpeningBookReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.file_opening_book_report');
    }

    public function getData(Request $request)
    {
        $userClientId = Auth::user()->Client_ID;

        $query = File::where('Client_ID', $userClientId)
            ->whereBetween('File_Date', [$request->from_date, $request->to_date])
            ->orderBy('File_Date', 'desc');

        $files = $query->paginate(10);

        return response()->json([
            'data' => $files->items(),
            'pagination' => $files->links()->render()
        ]);
    }

    public function downloadPDF(Request $request)
    {
        $userClientId = Auth::user()->Client_ID;

        if (!$request->from_date || !$request->to_date) {
            return back()->with('error', 'Please provide both from and to dates.');
        }

        $files = File::where('Client_ID', $userClientId)
            ->whereBetween('File_Date', [$request->from_date, $request->to_date])
            ->orderBy('File_Date', 'desc')
            ->get(); // Fetch all records instead of paginating

        if ($files->isEmpty()) {
            return back()->with('error', 'No records found for the selected date range.');
        }

        // dd($files);

        // Generate PDF
        $pdf = PDF::loadView('admin.reports.pdf.file_report_pdf', [
            'files' => $files,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date
        ]);

        // Sanitize file names
        $safeFromDate = preg_replace('/[^A-Za-z0-9_-]/', '', $request->from_date);
        $safeToDate = preg_replace('/[^A-Za-z0-9_-]/', '', $request->to_date);

        return $pdf->download("File_Report_{$safeFromDate}_to_{$safeToDate}.pdf");
    }
    public function downloadCSV(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $data = File::whereBetween('File_Date', [$fromDate, $toDate])->get();

        $fileName = 'File_Report_' . $fromDate . '_to_' . $toDate . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ["S/No", "File Open Date", "Ledger Ref", "Matter", "Client Name", "Address", "Fee Earner", "Status", "Close Date"]);

        foreach ($data as $index => $record) {
            fputcsv($handle, [
                $index + 1,
                $record->File_Date,
                $record->Ledger_Ref,
                $record->Matter,
                $record->First_Name . ' ' . $record->Last_Name,
                $record->Address1 . ' ' . $record->Address2 . ' ' . $record->Town . ' ' . $record->Post_Code,
                $record->Fee_Earner,
                $record->Status,
                $record->File_Date
            ]);
        }

        fclose($handle);

        return Response::make('', 200, $headers);
    }
}
