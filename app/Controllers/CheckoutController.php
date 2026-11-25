<?php

class CheckoutController extends Controller
{
    private CartModel $cart;
    private OrderModel $orders;

    public function __construct()
    {
        $this->cart = new CartModel();
        $this->orders = new OrderModel();
    }

    public function index(): void
    {
        AuthHelper::requireCustomer();
        $this->renderCheckout();
    }

    public function placeOrder(): void
    {
        AuthHelper::requireCustomer();
        if (!$this->verifyCsrf()) {
            return;
        }

        $orderType = $this->postString('order_type');
        $bookingMode = $this->postString('booking_mode');
        $bookingId = filter_var($_POST['booking_id'] ?? null, FILTER_VALIDATE_INT);
        $newBooking = [
            'date' => $this->postString('booking_date'),
            'start_time' => $this->postString('start_time'),
            'guest_count' => filter_var($_POST['guest_count'] ?? null, FILTER_VALIDATE_INT),
            'table_id' => filter_var($_POST['table_id'] ?? null, FILTER_VALIDATE_INT),
            'notes' => trim($this->postString('booking_notes')),
        ];
        $formData = [
            'order_type' => $orderType,
            'booking_mode' => $bookingMode,
            'booking_id' => $bookingId === false ? '' : (int) $bookingId,
            'booking_date' => $newBooking['date'],
            'start_time' => $newBooking['start_time'],
            'guest_count' => $newBooking['guest_count'] === false ? '' : (int) $newBooking['guest_count'],
            'table_id' => $newBooking['table_id'] === false ? '' : (int) $newBooking['table_id'],
            'booking_notes' => $newBooking['notes'],
        ];

        if (strlen($newBooking['notes']) > 2000) {
            http_response_code(422);
            $this->renderCheckout(['Catatan reservasi maksimal 2000 byte.'], $formData);
            return;
        }

        try {
            $order = $this->orders->createFromCart(
                (int) AuthHelper::currentUser()['id'],
                $orderType,
                $bookingMode,
                $bookingId === false ? null : (int) $bookingId,
                $newBooking
            );
        } catch (DomainException | InvalidArgumentException $exception) {
            http_response_code(422);
            $this->renderCheckout([$exception->getMessage()], $formData);
            return;
        }

        FlashHelper::success('Pesanan ' . $order['order_code'] . ' berhasil dibuat. Pembayaran dilakukan tunai di kasir.');
        $this->redirect($this->appBaseUrl() . '/orders');
    }

    private function renderCheckout(array $errors = [], array $formData = []): void
    {
        $user = AuthHelper::currentUser();
        $userId = (int) $user['id'];
        $cart = $this->cart->getCart($userId);

        if (!$cart['items']) {
            FlashHelper::error('Keranjang kosong. Tambahkan menu sebelum checkout.');
            $this->redirect($this->appBaseUrl() . '/cart');
        }
        if ($cart['has_unavailable_items']) {
            FlashHelper::error('Hapus menu yang tidak tersedia sebelum checkout.');
            $this->redirect($this->appBaseUrl() . '/cart');
        }

        $this->view('customer/checkout', [
            'pageTitle' => 'Checkout Pesanan',
            'cart' => $cart,
            'bookings' => $this->orders->activeBookingsForUser($userId),
            'errors' => $errors,
            'formData' => $formData,
            'currentUser' => $user,
            'cartCount' => $cart['item_count'],
        ]);
    }

    private function postString(string $key): string
    {
        return is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
    }

    private function verifyCsrf(): bool
    {
        if (SecurityHelper::verifyCsrfToken()) {
            return true;
        }

        http_response_code(400);
        echo '400 - Token CSRF tidak valid.';
        return false;
    }
}
