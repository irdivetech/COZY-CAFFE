<?php

class HomeController extends Controller
{
    public function index(): void
    {
        $currentUser = AuthHelper::currentUser();
        $this->view('customer/home', [
            'pageTitle' => 'Beranda - CozyCafe',
            'featuredMenus' => (new Menu())->featured(6),
            'currentUser' => $currentUser,
            'cartCount' => ($currentUser['role'] ?? '') === 'customer'
                ? (new CartModel())->itemCount((int) $currentUser['id'])
                : 0,
        ]);
    }
}
