<?php

class OrderController extends Controller
{
    public function index(): void
    {
        AuthHelper::requireCustomer();
        $user = AuthHelper::currentUser();
        $userId = (int) $user['id'];

        $this->view('customer/my_orders', [
            'pageTitle' => 'My Orders',
            'orders' => (new OrderModel())->forUser($userId),
            'currentUser' => $user,
            'cartCount' => (new CartModel())->itemCount($userId),
        ]);
    }
}
