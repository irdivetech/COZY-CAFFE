<?php

class AdminCustomerController extends Controller
{
    public function index(): void
    {
        AuthHelper::requireAdmin();

        $this->view('admin/customers', [
            'pageTitle' => 'Kelola Pelanggan',
            'customers' => (new AdminCustomer())->all(),
        ]);
    }
}
