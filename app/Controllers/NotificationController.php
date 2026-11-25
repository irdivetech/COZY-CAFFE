<?php

class NotificationController extends Controller
{
    public function index(): void
    {
        AuthHelper::requireCustomer();
        $user = AuthHelper::currentUser();
        $this->view('customer/notifications', [
            'pageTitle' => 'Notifications',
            'notifications' => NotificationHelper::forUser((int) $user['id']),
            'currentUser' => $user,
            'cartCount' => (new CartModel())->itemCount((int) $user['id']),
        ]);
    }

    public function adminIndex(): void
    {
        AuthHelper::requireAdmin();
        $this->view('admin/notifications', [
            'pageTitle' => 'Admin Notifications',
            'notifications' => NotificationHelper::forUser((int) AuthHelper::currentUser()['id']),
        ]);
    }

    public function markRead(): void
    {
        AuthHelper::requireCustomer();
        $this->markOne((int) AuthHelper::currentUser()['id'], $this->appBaseUrl() . '/notifications');
    }

    public function adminMarkRead(): void
    {
        AuthHelper::requireAdmin();
        $this->markOne((int) AuthHelper::currentUser()['id'], $this->appBaseUrl() . '/admin/notifications');
    }

    public function markAllRead(): void
    {
        AuthHelper::requireCustomer();
        $this->markAll((int) AuthHelper::currentUser()['id'], $this->appBaseUrl() . '/notifications');
    }

    public function adminMarkAllRead(): void
    {
        AuthHelper::requireAdmin();
        $this->markAll((int) AuthHelper::currentUser()['id'], $this->appBaseUrl() . '/admin/notifications');
    }

    private function markOne(int $userId, string $redirectUrl): void
    {
        if (!SecurityHelper::verifyCsrfToken()) {
            http_response_code(400);
            echo '400 - Invalid CSRF token';
            return;
        }

        $notificationId = filter_var($_POST['notification_id'] ?? null, FILTER_VALIDATE_INT);
        if ($notificationId === false || $notificationId < 1) {
            FlashHelper::error('Notifikasi tidak valid.');
            $this->redirect($redirectUrl);
        }

        if (NotificationHelper::markRead($userId, (int) $notificationId)) {
            FlashHelper::success('Notifikasi ditandai sudah dibaca.');
        } else {
            FlashHelper::error('Notifikasi tidak ditemukan atau sudah dibaca.');
        }
        $this->redirect($redirectUrl);
    }

    private function markAll(int $userId, string $redirectUrl): void
    {
        if (!SecurityHelper::verifyCsrfToken()) {
            http_response_code(400);
            echo '400 - Invalid CSRF token';
            return;
        }

        NotificationHelper::markAllRead($userId);
        FlashHelper::success('Semua notifikasi ditandai sudah dibaca.');
        $this->redirect($redirectUrl);
    }
}
