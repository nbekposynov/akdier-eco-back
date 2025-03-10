<?php

namespace App\Http\Controllers\Refactoring;

use App\Exports\FinalProcessingReportExport;
use App\Exports\WasteReportExport;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    private ReportService $service;

    public function __construct(ReportService $service)
    {
        $this->service = $service;
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'moderator_id' => 'nullable|integer',
            'company_id' => 'nullable|integer',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'integer|exists:users,id',
            'car_num' => 'nullable|string',
            'driv_name' => 'nullable|string',
        ]);

        $filters = $request->all();

        $report = $this->service->generateReport($filters);
        return response()->json($report);
    }

    public function generateExcel(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'moderator_id' => 'nullable|integer',
            'company_id' => 'nullable|integer',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'integer|exists:users,id',
            'car_num' => 'nullable|string',
            'driv_name' => 'nullable|string',
        ]);

        $filters = $request->all();

        // Получаем данные для отчета
        $report = $this->service->generateReport($filters);

        // Возвращаем Excel файл
        return Excel::download(new WasteReportExport($report), 'waste_report.xlsx');
    }

    public function exportFinalProcessingReport(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'nullable|integer|exists:users,id',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $filters = $request->all();
        $report = $this->service->generate($filters);

        return Excel::download(new FinalProcessingReportExport($report), 'FinalProcessingReport.xlsx');
    }

    public function generateFinalProcessingReport(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'nullable|integer|exists:users,id',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $filters = $request->all();
        $report = $this->service->generate($filters);

        return response()->json($report);
    }
}
