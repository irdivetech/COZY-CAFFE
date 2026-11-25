<?php

class CartController extends Controller
{
    private CartModel $cart;

    public function __construct()
    {
        $this->cart = new CartModel();
    }

    public function index(): void
    {
        AuthHelper::requireCustomer();
        $userId = (int) AuthHelper::currentUser()['id'];
        $cart = $this->cart->getCart($userId);

        $this->view('customer/cart', [
            'pageTitle' => 'My Cart',
            'cart' => $cart,
            'cartCount' => $cart['item_count'],
            'currentUser' => AuthHelper::currentUser(),
        ]);
    }

    public function add(): void
    {
        AuthHelper::requireCustomer();
        if (!$this->verifyCsrf()) {
            return;
        }

        $menuId = filter_var($_POST['menu_id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($_POST['quantity'] ?? '1', FILTER_VALIDATE_INT);
        $notes = trim($this->postString('notes'));

        if ($menuId === false || $menuId < 1
            || $quantity === false || $quantity < 1 || $quantity > 99
            || strlen($notes) > 255
        ) {
            FlashHelper::error('Menu, jumlah, atau catatan item tidak valid.');
            $this->redirect($this->appBaseUrl() . '/cart');
        }

        try {
            $this->cart->addItem(
                (int) AuthHelper::currentUser()['id'],
                (int) $menuId,
                (int) $quantity,
                $notes === '' ? null : $notes
            );
            FlashHelper::success('Menu berhasil ditambahkan ke keranjang.');
        } catch (DomainException $exception) {
            FlashHelper::error($exception->getMessage());
        }

        $this->redirect($this->appBaseUrl() . '/cart');
    }

    public function update(): void
    {
        AuthHelper::requireCustomer();
        if (!$this->verifyCsrf()) {
            return;
        }

        $itemId = filter_var($_POST['cart_item_id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);
        $notes = trim($this->postString('notes'));
        if ($itemId === false || $itemId < 1
            || $quantity === false || $quantity < 1 || $quantity > 99
            || strlen($notes) > 255
        ) {
            FlashHelper::error('Jumlah atau catatan item tidak valid.');
            $this->redirect($this->appBaseUrl() . '/cart');
        }

        try {
            $this->cart->updateItem(
                (int) AuthHelper::currentUser()['id'],
                (int) $itemId,
                (int) $quantity,
                $notes === '' ? null : $notes
            );
            FlashHelper::success('Item keranjang berhasil diperbarui.');
        } catch (DomainException $exception) {
            FlashHelper::error($exception->getMessage());
        }

        $this->redirect($this->appBaseUrl() . '/cart');
    }

    public function changeQuantity(): void
    {
        AuthHelper::requireCustomer();
        if (!$this->verifyCsrf()) {
            return;
        }

        $itemId = filter_var($_POST['cart_item_id'] ?? null, FILTER_VALIDATE_INT);
        $delta = filter_var($_POST['delta'] ?? null, FILTER_VALIDATE_INT);
        if ($itemId === false || $itemId < 1 || !in_array($delta, [-1, 1], true)) {
            FlashHelper::error('Perubahan jumlah item tidak valid.');
            $this->redirect($this->appBaseUrl() . '/cart');
        }

        try {
            $this->cart->changeQuantity(
                (int) AuthHelper::currentUser()['id'],
                (int) $itemId,
                (int) $delta
            );
            FlashHelper::success('Jumlah item berhasil diperbarui.');
        } catch (DomainException $exception) {
            FlashHelper::error($exception->getMessage());
        }

        $this->redirect($this->appBaseUrl() . '/cart');
    }

    public function remove(): void
    {
        AuthHelper::requireCustomer();
        if (!$this->verifyCsrf()) {
            return;
        }

        $itemId = filter_var($_POST['cart_item_id'] ?? null, FILTER_VALIDATE_INT);
        if ($itemId === false || $itemId < 1) {
            FlashHelper::error('Item keranjang tidak valid.');
            $this->redirect($this->appBaseUrl() . '/cart');
        }

        try {
            $this->cart->deleteItem((int) AuthHelper::currentUser()['id'], (int) $itemId);
            FlashHelper::success('Item berhasil dihapus dari keranjang.');
        } catch (DomainException $exception) {
            FlashHelper::error($exception->getMessage());
        }

        $this->redirect($this->appBaseUrl() . '/cart');
    }

    private function postString(string $key): string
    {
        return is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
    }

    private function verifyCsrf(): bool
    {
        if (SecurityHelper::verifyCsrfToken()) {
            return true;
        }

        http_response_code(400);
        echo '400 - Invalid CSRF token';
        return false;
    }
}
