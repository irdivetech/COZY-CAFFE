<?php

class AdminOrderController extends Controller
{
    private OrderModel $orders;

    public function __construct()
    {
        $this->orders = new OrderModel();
    }

    public function index(): void
    {
        AuthHelper::requireAdmin();
        $status = is_string($_GET['status'] ?? null) ? trim($_GET['status']) : '';
        $filter = $status === '' ? null : $status;
        $errors = [];

        try {
            $orders = $this->orders->allForAdmin($filter);
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);
            $errors[] = $exception->getMessage();
            $filter = null;
            $orders = $this->orders->allForAdmin();
        }

        $this->view('admin/orders', [
            'pageTitle' => 'Kelola Pesanan',
            'orders' => $orders,
            'selectedStatus' => $filter ?? '',
            'errors' => $errors,
        ]);
    }

    public function updateStatus(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
        $status = is_string($_POST['status'] ?? null) ? trim($_POST['status']) : '';
        try {
            if ($orderId === false || $orderId < 1) {
                throw new InvalidArgumentException('Pesanan tidak valid.');
            }
            $changed = $this->orders->updateStatus((int) $orderId, $status);
            FlashHelper::success($changed ? 'Status pesanan berhasil diperbarui.' : 'Status pesanan tidak berubah.');
        } catch (DomainException | InvalidArgumentException $exception) {
            FlashHelper::error($exception->getMessage());
        }

        $this->redirect($this->appBaseUrl() . '/admin/orders');
    }

    public function confirmPayment(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
        try {
            if ($orderId === false || $orderId < 1) {
                throw new InvalidArgumentException('Pesanan tidak valid.');
            }
            $changed = $this->orders->confirmCashPayment((int) $orderId);
            FlashHelper::success(
                $changed ? 'Pembayaran tunai berhasil dikonfirmasi.' : 'Pembayaran pesanan ini sudah berstatus Paid.'
            );
        } catch (DomainException | InvalidArgumentException $exception) {
            FlashHelper::error($exception->getMessage());
        }

        $this->redirect($this->appBaseUrl() . '/admin/orders');
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
