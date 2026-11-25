<?php

class AdminBookingController extends Controller
{
    private BookingModel $bookings;

    public function __construct()
    {
        $this->bookings = new BookingModel();
    }

    public function index(): void
    {
        AuthHelper::requireAdmin();
        $this->view('admin/bookings', [
            'pageTitle' => 'Kelola Reservasi',
            'bookings' => $this->bookings->allForAdmin(),
        ]);
    }

    public function updateStatus(): void
    {
        AuthHelper::requireAdmin();
        if (!SecurityHelper::verifyCsrfToken()) {
            http_response_code(400);
            echo '400 - Token CSRF tidak valid.';
            return;
        }

        $bookingId = filter_var($_POST['booking_id'] ?? null, FILTER_VALIDATE_INT);
        $status = is_string($_POST['status'] ?? null) ? trim($_POST['status']) : '';
        try {
            if ($bookingId === false || $bookingId < 1) {
                throw new InvalidArgumentException('Reservasi tidak valid.');
            }
            $this->bookings->updateAdminStatus((int) $bookingId, $status);
            FlashHelper::success('Status reservasi berhasil diperbarui.');
        } catch (DomainException | InvalidArgumentException $exception) {
            FlashHelper::error($exception->getMessage());
        }

        $this->redirect($this->appBaseUrl() . '/admin/bookings');
    }
}
