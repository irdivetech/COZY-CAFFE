<?php

class AdminTableController extends Controller
{
    private CafeTable $tables;

    public function __construct()
    {
        $this->tables = new CafeTable();
    }

    public function index(): void
    {
        AuthHelper::requireAdmin();
        $this->view('admin/tables', [
            'pageTitle' => 'Kelola Meja',
            'tables' => $this->tables->all(),
            'errors' => [],
            'formData' => null,
        ]);
    }

    public function edit(): void
    {
        AuthHelper::requireAdmin();
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $table = $id ? $this->tables->find((int) $id) : null;
        if (!$table) {
            http_response_code(404);
            echo '404 - Meja tidak ditemukan.';
            return;
        }

        $this->render($table);
    }

    public function create(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $formData = $this->postedTable();
        $errors = $this->validate($formData);
        if (!$errors && $this->tables->numberExists($formData['table_number'])) {
            $errors[] = 'Nomor meja sudah digunakan.';
        }
        if ($errors) {
            http_response_code(422);
            $this->render($formData, $errors);
            return;
        }

        $this->tables->create(
            $formData['table_number'],
            (int) $formData['capacity'],
            $formData['is_active']
        );
        FlashHelper::success('Meja berhasil ditambahkan.');
        $this->redirect($this->appBaseUrl() . '/admin/tables');
    }

    public function update(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $table = $id ? $this->tables->find((int) $id) : null;
        if (!$table) {
            http_response_code(404);
            echo '404 - Meja tidak ditemukan.';
            return;
        }

        $formData = $this->postedTable();
        $formData['id'] = (int) $id;
        $errors = $this->validate($formData);
        if (!$errors && $this->tables->numberExists($formData['table_number'], (int) $id)) {
            $errors[] = 'Nomor meja sudah digunakan.';
        }
        if ($errors) {
            http_response_code(422);
            $this->render($formData, $errors);
            return;
        }

        $this->tables->update(
            (int) $id,
            $formData['table_number'],
            (int) $formData['capacity'],
            $formData['is_active']
        );
        FlashHelper::success('Meja berhasil diperbarui.');
        $this->redirect($this->appBaseUrl() . '/admin/tables');
    }

    public function toggle(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || !$this->tables->find((int) $id)) {
            http_response_code(404);
            echo '404 - Meja tidak ditemukan.';
            return;
        }

        $this->tables->toggle((int) $id);
        FlashHelper::success('Status meja berhasil diubah.');
        $this->redirect($this->appBaseUrl() . '/admin/tables');
    }

    private function postedTable(): array
    {
        return [
            'table_number' => trim($this->postString('table_number')),
            'capacity' => trim($this->postString('capacity')),
            'is_active' => ($this->postString('is_active') === '1'),
        ];
    }

    private function validate(array $table): array
    {
        $errors = [];
        $length = preg_match_all('/./us', $table['table_number'], $characters);
        if ($table['table_number'] === '' || $length === false || $length > 20) {
            $errors[] = 'Nomor meja wajib diisi dan maksimal 20 karakter.';
        }
        if (filter_var($table['capacity'], FILTER_VALIDATE_INT) === false
            || (int) $table['capacity'] < 1
            || (int) $table['capacity'] > 1000
        ) {
            $errors[] = 'Kapasitas harus berupa bilangan bulat antara 1 dan 1000.';
        }
        return $errors;
    }

    private function render(array $formData, array $errors = []): void
    {
        $this->view('admin/tables', [
            'pageTitle' => 'Kelola Meja',
            'tables' => $this->tables->all(),
            'errors' => $errors,
            'formData' => $formData,
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
        echo '400 - Token CSRF tidak valid.';
        return false;
    }
}
