<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    /**
     * Get reports based on query parameters.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:project,client,daily,weekly'],
            'client_id' => ['sometimes', 'exists:clients,id'],
            'project_id' => ['sometimes', 'exists:projects,id'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
        ]);

        $user = $request->user();
        $type = $request->query('type');
        $from = $request->query('from');
        $to = $request->query('to');

        $data = match ($type) {
            'project' => $this->reportService->getTotalHoursByProject($user, $from, $to),
            'client' => $this->reportService->getTotalHoursByClient($user, $from, $to),
            'daily' => $this->reportService->getDailyHours($user, $from, $to),
            'weekly' => $this->reportService->getWeeklyHours($user, $from, $to),
        };

        return response()->json([
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'data' => $data,
        ]);
    }

    /**
     * Get time logs summary for a specific client.
     */
    public function client(Request $request, int $clientId): JsonResponse
    {
        $request->validate([
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
        ]);

        $user = $request->user();
        $from = $request->query('from');
        $to = $request->query('to');

        // Filter by client
        $data = $this->reportService->getTotalHoursByClient($user, $from, $to)
            ->where('client_id', $clientId)
            ->first();

        if (!$data) {
            return response()->json([
                'message' => 'No data found for this client in the specified date range',
            ], 404);
        }

        return response()->json([
            'client_id' => $clientId,
            'from' => $from,
            'to' => $to,
            'data' => $data,
        ]);
    }

    /**
     * Export time logs as PDF.
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'group_by' => ['required', 'in:project,client,daily,weekly'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'client_id' => ['sometimes', 'exists:clients,id'],
            'project_id' => ['sometimes', 'exists:projects,id'],
        ]);

        $user = $request->user();
        $filters = $request->only(['group_by', 'start_date', 'end_date', 'client_id', 'project_id']);

        try {
            $pdfContent = $this->reportService->generateTimeReportPDF($user, $filters);
            
            $filename = 'time-report-' . ($filters['group_by'] ?? 'daily') . '-' . now()->format('Y-m-d-H-i-s') . '.pdf';
            
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate PDF report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user summary report.
     */
    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
        ]);

        $user = $request->user();
        $from = $request->query('from');
        $to = $request->query('to');

        $data = $this->reportService->getUserSummary($user, $from, $to);

        return response()->json([
            'from' => $from,
            'to' => $to,
            'summary' => $data,
        ]);
    }
}
