<?php

class HomeController extends Controller
{
    public function index(): void
    {
        $currentUser = AuthHelper::currentUser();
        $this->view('customer/home', [
            'pageTitle' => 'A Cozy Place, A Better Taste',
            'featuredMenus' => (new Menu())->featured(6),
            'currentUser' => $currentUser,
            'cartCount' => ($currentUser['role'] ?? '') === 'customer'
                ? (new CartModel())->itemCount((int) $currentUser['id'])
                : 0,
        ]);
    }
}
