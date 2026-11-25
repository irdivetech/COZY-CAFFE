<?php

class AdminClosureController extends Controller
{
    private ReservationClosure $closures;

    public function __construct()
    {
        $this->closures = new ReservationClosure();
    }

    public function index(): void
    {
        AuthHelper::requireAdmin();
        $this->render();
    }

    public function edit(): void
    {
        AuthHelper::requireAdmin();
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $closure = $id ? $this->closures->find((int) $id) : null;
        if (!$closure) {
            http_response_code(404);
            echo '404 - Reservation closure not found';
            return;
        }

        $this->render($closure);
    }

    public function create(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $closure = $this->postedClosure();
        $errors = $this->validate($closure);
        if ($errors) {
            http_response_code(422);
            $this->render($closure, $errors);
            return;
        }

        $this->closures->create($closure['start_date'], $closure['end_date'], $closure['reason']);
        FlashHelper::success('Penutupan reservasi berhasil ditambahkan.');
        $this->redirect($this->appBaseUrl() . '/admin/closures');
    }

    public function update(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $existing = $id ? $this->closures->find((int) $id) : null;
        if (!$existing) {
            http_response_code(404);
            echo '404 - Reservation closure not found';
            return;
        }

        $closure = $this->postedClosure();
        $closure['id'] = (int) $id;
        $errors = $this->validate($closure);
        if ($errors) {
            http_response_code(422);
            $this->render($closure, $errors);
            return;
        }

        $this->closures->update(
            (int) $id,
            $closure['start_date'],
            $closure['end_date'],
            $closure['reason']
        );
        FlashHelper::success('Penutupan reservasi berhasil diperbarui.');
        $this->redirect($this->appBaseUrl() . '/admin/closures');
    }

    public function delete(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || !$this->closures->find((int) $id)) {
            http_response_code(404);
            echo '404 - Reservation closure not found';
            return;
        }

        $this->closures->delete((int) $id);
        FlashHelper::success('Penutupan reservasi berhasil dihapus.');
        $this->redirect($this->appBaseUrl() . '/admin/closures');
    }

    private function postedClosure(): array
    {
        return [
            'start_date' => $this->postString('start_date'),
            'end_date' => $this->postString('end_date'),
            'reason' => trim($this->postString('reason')),
        ];
    }

    private function validate(array $closure): array
    {
        $errors = [];
        if (!$this->validDate($closure['start_date'])) {
            $errors[] = 'Tanggal mulai tidak valid.';
        }
        if (!$this->validDate($closure['end_date'])) {
            $errors[] = 'Tanggal selesai tidak valid.';
        }
        if ($this->validDate($closure['start_date'])
            && $this->validDate($closure['end_date'])
            && $closure['end_date'] < $closure['start_date']
        ) {
            $errors[] = 'Tanggal selesai harus sama atau setelah tanggal mulai.';
        }
        $reasonLength = preg_match_all('/./us', $closure['reason'], $characters);
        if ($closure['reason'] === '' || $reasonLength === false || $reasonLength > 255) {
            $errors[] = 'Alasan wajib diisi dan maksimal 255 karakter.';
        }

        return $errors;
    }

    private function validDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();

        return $date !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $date->format('Y-m-d') === $value;
    }

    private function render(?array $closure = null, array $errors = []): void
    {
        $this->view('admin/closures', [
            'pageTitle' => 'Reservation Closures',
            'closures' => $this->closures->all(),
            'closure' => $closure,
            'errors' => $errors,
        ]);
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
