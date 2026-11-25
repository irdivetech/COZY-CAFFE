<?php

class AdminCategoryController extends Controller
{
    private Category $categories;

    public function __construct()
    {
        $this->categories = new Category();
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
        $category = $id ? $this->categories->find((int) $id) : null;

        if (!$category) {
            http_response_code(404);
            echo '404 - Kategori tidak ditemukan.';
            return;
        }

        $this->render($category);
    }

    public function create(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $name = trim($this->postString('name'));
        $description = trim($this->postString('description'));
        $errors = $this->validate($name, $description);

        if (!$errors && $this->categories->nameExists($name)) {
            $errors[] = 'Nama kategori sudah digunakan.';
        }
        if ($errors) {
            http_response_code(422);
            $this->render([
                'name' => $name,
                'description' => $description,
            ], $errors);
            return;
        }

        $this->categories->create($name, $description === '' ? null : $description);
        FlashHelper::success('Kategori berhasil ditambahkan.');
        $this->redirect($this->appBaseUrl() . '/admin/categories');
    }

    public function update(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $category = $id ? $this->categories->find((int) $id) : null;
        if (!$category) {
            http_response_code(404);
            echo '404 - Kategori tidak ditemukan.';
            return;
        }

        $name = trim($this->postString('name'));
        $description = trim($this->postString('description'));
        $errors = $this->validate($name, $description);

        if (!$errors && $this->categories->nameExists($name, (int) $id)) {
            $errors[] = 'Nama kategori sudah digunakan.';
        }
        if ($errors) {
            http_response_code(422);
            $category['name'] = $name;
            $category['description'] = $description;
            $this->render($category, $errors);
            return;
        }

        $this->categories->update((int) $id, $name, $description === '' ? null : $description);
        FlashHelper::success('Kategori berhasil diperbarui.');
        $this->redirect($this->appBaseUrl() . '/admin/categories');
    }

    public function delete(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $category = $id ? $this->categories->find((int) $id) : null;
        if (!$category) {
            http_response_code(404);
            echo '404 - Kategori tidak ditemukan.';
            return;
        }

        if ($this->categories->menuCount((int) $id) > 0) {
            FlashHelper::error('Kategori tidak dapat dihapus karena masih memiliki menu.');
            $this->redirect($this->appBaseUrl() . '/admin/categories');
        }

        try {
            $this->categories->delete((int) $id);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000' || (int) ($exception->errorInfo[1] ?? 0) !== 1451) {
                throw $exception;
            }
            FlashHelper::error('Kategori tidak dapat dihapus karena masih digunakan.');
            $this->redirect($this->appBaseUrl() . '/admin/categories');
        }

        FlashHelper::success('Kategori berhasil dihapus.');
        $this->redirect($this->appBaseUrl() . '/admin/categories');
    }

    private function render(?array $category = null, array $errors = []): void
    {
        $this->view('admin/categories', [
            'pageTitle' => 'Kelola Kategori',
            'categories' => $this->categories->all(),
            'category' => $category,
            'errors' => $errors,
        ]);
    }

    private function validate(string $name, string $description): array
    {
        $errors = [];
        if ($name === '' || $this->characterLength($name) > 100) {
            $errors[] = 'Nama kategori wajib diisi dan maksimal 100 karakter.';
        }
        if (strlen($description) > 65535) {
            $errors[] = 'Deskripsi kategori terlalu panjang.';
        }
        return $errors;
    }

    private function characterLength(string $value): int
    {
        $matched = preg_match_all('/./us', $value, $characters);
        return $matched === false ? PHP_INT_MAX : $matched;
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
