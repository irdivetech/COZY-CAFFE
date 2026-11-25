<?php

class AdminMenuController extends Controller
{
    private Menu $menus;
    private Category $categories;

    public function __construct()
    {
        $this->menus = new Menu();
        $this->categories = new Category();
    }

    public function index(): void
    {
        AuthHelper::requireAdmin();
        $this->view('admin/menus', [
            'pageTitle' => 'Kelola Menu',
            'menus' => $this->menus->allForAdmin(),
        ]);
    }

    private function postString(string $key): string
    {
        return is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
    }

    public function createForm(): void
    {
        AuthHelper::requireAdmin();
        $this->renderForm([
            'category_id' => '',
            'name' => '',
            'description' => '',
            'price' => '',
            'image' => null,
            'is_available' => 1,
        ]);
    }

    public function editForm(): void
    {
        AuthHelper::requireAdmin();
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $menu = $id ? $this->menus->findForAdmin((int) $id) : null;
        if (!$menu) {
            http_response_code(404);
            echo '404 - Menu tidak ditemukan.';
            return;
        }

        $this->renderForm($menu);
    }

    public function create(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $menu = $this->postedMenu();
        $errors = $this->validate($menu);
        $upload = $this->uploadedImage();
        if ($upload['error'] !== null) {
            $errors[] = $upload['error'];
        }
        if ($errors) {
            http_response_code(422);
            $this->renderForm($menu, $errors);
            return;
        }

        $imageName = null;
        if ($upload['file'] !== null) {
            $imageName = $this->storeImage($upload['file']);
            if ($imageName === null) {
                http_response_code(500);
                $this->renderForm($menu, ['Foto menu gagal disimpan. Periksa izin tulis folder uploads.']);
                return;
            }
        }

        try {
            $this->menus->create(
                (int) $menu['category_id'],
                $menu['name'],
                $menu['description'] === '' ? null : $menu['description'],
                $menu['price'],
                $imageName,
                $menu['is_available']
            );
        } catch (Throwable $exception) {
            if ($imageName !== null) {
                $this->removeImage($imageName);
            }
            throw $exception;
        }

        FlashHelper::success('Menu berhasil ditambahkan.');
        $this->redirect($this->appBaseUrl() . '/admin/menus');
    }

    public function update(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $existing = $id ? $this->menus->findForAdmin((int) $id) : null;
        if (!$existing) {
            http_response_code(404);
            echo '404 - Menu tidak ditemukan.';
            return;
        }

        $menu = $this->postedMenu();
        $menu['id'] = (int) $id;
        $menu['image'] = $existing['image'];
        $errors = $this->validate($menu);
        $upload = $this->uploadedImage();
        if ($upload['error'] !== null) {
            $errors[] = $upload['error'];
        }
        if ($errors) {
            http_response_code(422);
            $this->renderForm($menu, $errors);
            return;
        }

        $newImageName = null;
        if ($upload['file'] !== null) {
            $newImageName = $this->storeImage($upload['file']);
            if ($newImageName === null) {
                http_response_code(500);
                $this->renderForm($menu, ['Foto menu gagal disimpan. Periksa izin tulis folder uploads.']);
                return;
            }
        }

        try {
            $this->menus->update(
                (int) $id,
                (int) $menu['category_id'],
                $menu['name'],
                $menu['description'] === '' ? null : $menu['description'],
                $menu['price'],
                $newImageName,
                $newImageName !== null,
                $menu['is_available']
            );
        } catch (Throwable $exception) {
            if ($newImageName !== null) {
                $this->removeImage($newImageName);
            }
            throw $exception;
        }

        if ($newImageName !== null && $existing['image'] !== null) {
            $this->removeImage($existing['image']);
        }

        FlashHelper::success('Menu berhasil diperbarui.');
        $this->redirect($this->appBaseUrl() . '/admin/menus');
    }

    public function toggle(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || !$this->menus->findForAdmin((int) $id)) {
            http_response_code(404);
            echo '404 - Menu tidak ditemukan.';
            return;
        }

        $isAvailable = $this->menus->toggle((int) $id);
        FlashHelper::success(
            $isAvailable
                ? 'Menu berhasil diaktifkan dan dapat dipesan pelanggan.'
                : 'Menu dinonaktifkan dan tidak lagi ditampilkan ke pelanggan.'
        );
        $this->redirect($this->appBaseUrl() . '/admin/menus');
    }

    public function disable(): void
    {
        $this->toggle();
    }

    private function postedMenu(): array
    {
        return [
            'category_id' => trim($this->postString('category_id')),
            'name' => trim($this->postString('name')),
            'description' => trim($this->postString('description')),
            'price' => trim($this->postString('price')),
            'image' => null,
            'is_available' => $this->postString('is_available') === '1',
        ];
    }

    private function validate(array $menu): array
    {
        $errors = [];
        $categoryId = filter_var($menu['category_id'], FILTER_VALIDATE_INT);
        if (!$categoryId || !$this->categories->find((int) $categoryId)) {
            $errors[] = 'Pilih kategori yang valid.';
        }

        $nameLength = preg_match_all('/./us', $menu['name'], $nameCharacters);
        if ($menu['name'] === '' || $nameLength === false || $nameLength > 150) {
            $errors[] = 'Nama menu wajib diisi dan maksimal 150 karakter.';
        }
        $descriptionLength = preg_match_all('/./us', $menu['description'], $descriptionCharacters);
        if ($descriptionLength === false || strlen($menu['description']) > 65535) {
            $errors[] = 'Deskripsi menu terlalu panjang.';
        }
        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $menu['price']) || (float) $menu['price'] <= 0) {
            $errors[] = 'Harga harus lebih besar dari 0 dan maksimal 8 digit sebelum desimal.';
        }

        return $errors;
    }

    private function uploadedImage(): array
    {
        $file = $_FILES['image'] ?? null;
        if ($file === null || (is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)) {
            return ['file' => null, 'error' => null];
        }
        if (!is_array($file)) {
            return ['file' => null, 'error' => 'Format unggahan foto tidak valid.'];
        }
        if (!SecurityHelper::validateImageUpload($file)) {
            return ['file' => null, 'error' => 'Foto harus JPG, PNG, atau WEBP dan maksimal 2MB.'];
        }

        return ['file' => $file, 'error' => null];
    }

    private function storeImage(array $file): ?string
    {
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return null;
        }

        $extensions = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
        ];
        $extension = $extensions[$imageInfo[2] ?? 0] ?? null;
        if ($extension === null) {
            return null;
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = dirname(__DIR__, 2) . '/public/uploads/' . $fileName;

        return move_uploaded_file($file['tmp_name'], $destination) ? $fileName : null;
    }

    private function removeImage(string $fileName): void
    {
        if (basename($fileName) !== $fileName) {
            return;
        }

        $path = dirname(__DIR__, 2) . '/public/uploads/' . $fileName;
        if (is_file($path) && !unlink($path)) {
            error_log('Unable to remove menu image: ' . $path);
        }
    }

    private function renderForm(array $menu, array $errors = []): void
    {
        $this->view('admin/menu_form', [
            'pageTitle' => isset($menu['id']) ? 'Ubah Menu' : 'Tambah Menu Baru',
            'menu' => $menu,
            'categories' => $this->categories->all(),
            'errors' => $errors,
        ]);
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
