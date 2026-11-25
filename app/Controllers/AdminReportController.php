<?php

class AdminReportController extends Controller
{
    public function index(): void
    {
        AuthHelper::requireAdmin();

        $today = date('Y-m-d');
        $startDate = is_string($_GET['start_date'] ?? null)
            ? trim($_GET['start_date'])
            : date('Y-m-01');
        $endDate = is_string($_GET['end_date'] ?? null)
            ? trim($_GET['end_date'])
            : $today;
        $errors = [];
        $report = [
            'revenue' => '0.00',
            'transaction_count' => 0,
            'booking_count' => 0,
            'transactions' => [],
            'top_menus' => [],
        ];

        try {
            $report = (new AdminReport())->forPeriod($startDate, $endDate);
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);
            $errors[] = $exception->getMessage();
        }

        $this->view('admin/reports', [
            'pageTitle' => 'Laporan Penjualan & Reservasi',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'errors' => $errors,
            'report' => $report,
        ]);
    }
}
