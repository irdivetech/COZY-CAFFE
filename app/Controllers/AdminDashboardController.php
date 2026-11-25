<?php

class AdminDashboardController extends Controller
{
    public function index(): void
    {
        AuthHelper::requireAdmin();
        $dashboard = new AdminDashboard();

        $this->view('admin/dashboard', [
            'pageTitle' => 'Dashboard',
            'summary' => $dashboard->summary(),
            'recentOrders' => $dashboard->recentOrders(),
            'recentBookings' => $dashboard->recentBookings(),
        ]);
    }
}
