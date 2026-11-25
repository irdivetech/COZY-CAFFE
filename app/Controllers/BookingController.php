<?php

class BookingController extends Controller
{
    private BookingService $bookingService;
    private BookingModel $bookings;

    public function __construct()
    {
        $this->bookingService = new BookingService();
        $this->bookings = new BookingModel();
    }

    public function reservation(): void
    {
        AuthHelper::requireCustomer();
        $this->renderReservation();
    }

    public function availability(): void
    {
        AuthHelper::requireCustomer();
        $date = $this->queryString('date');
        $time = $this->queryString('time');
        $guestCount = filter_var($_GET['guests'] ?? null, FILTER_VALIDATE_INT);

        if ($date === '' || $time === '' || !$guestCount) {
            $this->jsonResponse(['error' => 'Tanggal, jam, dan jumlah tamu harus diisi.'], 422);
        }

        try {
            $tables = $this->bookingService->availableTables($date, $time, (int) $guestCount);
        } catch (DomainException | InvalidArgumentException $exception) {
            $this->jsonResponse(['error' => $exception->getMessage()], 422);
        }

        $this->jsonResponse(['tables' => $tables]);
    }

    public function create(): void
    {
        AuthHelper::requireCustomer();
        if (!SecurityHelper::verifyCsrfToken()) {
            http_response_code(400);
            echo '400 - Invalid CSRF token';
            return;
        }

        $date = $this->postString('booking_date');
        $time = $this->postString('start_time');
        $guestCount = filter_var($_POST['guest_count'] ?? null, FILTER_VALIDATE_INT);
        $tableId = filter_var($_POST['table_id'] ?? null, FILTER_VALIDATE_INT);
        $notes = trim($this->postString('notes'));

        if (strlen($notes) > 65535) {
            http_response_code(422);
            $this->renderReservation(['Catatan maksimal 65535 byte.'], [
                'booking_date' => $date,
                'start_time' => $time,
                'guest_count' => $guestCount ?: '',
                'table_id' => $tableId ?: '',
                'notes' => '',
            ]);
            return;
        }

        try {
            $this->bookingService->create(
                (int) AuthHelper::currentUser()['id'],
                $date,
                $time,
                $guestCount === false ? 0 : (int) $guestCount,
                $tableId === false ? 0 : (int) $tableId,
                $notes === '' ? null : $notes
            );
        } catch (DomainException | InvalidArgumentException $exception) {
            http_response_code(422);
            $this->renderReservation([$exception->getMessage()], [
                'booking_date' => $date,
                'start_time' => $time,
                'guest_count' => $guestCount ?: '',
                'table_id' => $tableId ?: '',
                'notes' => $notes,
            ]);
            return;
        }

        FlashHelper::success('Reservasi berhasil dikonfirmasi.');
        $this->redirect($this->appBaseUrl() . '/bookings');
    }

    public function myBookings(): void
    {
        AuthHelper::requireCustomer();
        $user = AuthHelper::currentUser();
        $this->view('customer/my_bookings', [
            'pageTitle' => 'My Bookings',
            'bookings' => $this->bookings->forUser((int) $user['id']),
            'currentUser' => $user,
            'cartCount' => (new CartModel())->itemCount((int) $user['id']),
        ]);
    }

    private function renderReservation(array $errors = [], array $formData = []): void
    {
        $user = AuthHelper::currentUser();
        $this->view('customer/reservation', [
            'pageTitle' => 'Reserve a Table',
            'errors' => $errors,
            'formData' => $formData,
            'currentUser' => $user,
            'cartCount' => (new CartModel())->itemCount((int) $user['id']),
        ]);
    }

    private function queryString(string $key): string
    {
        return is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';
    }

    private function postString(string $key): string
    {
        return is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
    }
}
