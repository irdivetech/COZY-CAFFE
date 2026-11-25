<?php

class AdminCustomerController extends Controller
{
    public function index(): void
    {
        AuthHelper::requireAdmin();

        $this->view('admin/customers', [
            'pageTitle' => 'Manage Customers',
            'customers' => (new AdminCustomer())->all(),
        ]);
    }
}
