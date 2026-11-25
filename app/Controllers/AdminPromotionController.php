<?php

class AdminPromotionController extends Controller
{
    private Promotion $promotions;

    public function __construct()
    {
        $this->promotions = new Promotion();
    }

    public function index(): void
    {
        AuthHelper::requireAdmin();
        $this->render();
    }

    public function createForm(): void
    {
        AuthHelper::requireAdmin();
        $this->renderForm([
            'name' => '',
            'description' => '',
            'discount_type' => 'percentage',
            'discount_value' => '',
            'start_date' => '',
            'end_date' => '',
            'is_active' => true,
            'menu_ids' => [],
        ]);
    }

    public function editForm(): void
    {
        AuthHelper::requireAdmin();
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $promotion = $id ? $this->promotions->find((int) $id) : null;
        if (!$promotion) {
            http_response_code(404);
            echo '404 - Promotion not found';
            return;
        }

        $this->renderForm($promotion);
    }

    public function create(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $promotion = $this->postedPromotion();
        $errors = $this->validate($promotion);
        if ($errors) {
            http_response_code(422);
            $this->renderForm($promotion, $errors);
            return;
        }

        $this->promotions->save($promotion);
        FlashHelper::success('Promosi berhasil ditambahkan.');
        $this->redirect($this->appBaseUrl() . '/admin/promotions');
    }

    public function update(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $existing = $id ? $this->promotions->find((int) $id) : null;
        if (!$existing) {
            http_response_code(404);
            echo '404 - Promotion not found';
            return;
        }

        $promotion = $this->postedPromotion();
        $promotion['id'] = (int) $id;
        $errors = $this->validate($promotion);
        if ($errors) {
            http_response_code(422);
            $this->renderForm($promotion, $errors);
            return;
        }

        $this->promotions->save($promotion);
        FlashHelper::success('Promosi berhasil diperbarui.');
        $this->redirect($this->appBaseUrl() . '/admin/promotions');
    }

    public function delete(): void
    {
        AuthHelper::requireAdmin();
        if (!$this->verifyCsrf()) {
            return;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || !$this->promotions->find((int) $id)) {
            http_response_code(404);
            echo '404 - Promotion not found';
            return;
        }

        $this->promotions->delete((int) $id);
        FlashHelper::success('Promosi berhasil dihapus.');
        $this->redirect($this->appBaseUrl() . '/admin/promotions');
    }

    private function postedPromotion(): array
    {
        $rawMenuIds = $_POST['menu_ids'] ?? [];
        $menuIds = [];
        if (is_array($rawMenuIds)) {
            foreach ($rawMenuIds as $menuId) {
                if (is_string($menuId) || is_int($menuId)) {
                    $validatedId = filter_var($menuId, FILTER_VALIDATE_INT);
                    if ($validatedId && $validatedId > 0) {
                        $menuIds[] = (int) $validatedId;
                    }
                }
            }
        }

        return [
            'name' => trim($this->postString('name')),
            'description' => trim($this->postString('description')),
            'discount_type' => $this->postString('discount_type'),
            'discount_value' => trim($this->postString('discount_value')),
            'start_date' => $this->postString('start_date'),
            'end_date' => $this->postString('end_date'),
            'is_active' => $this->postString('is_active') === '1',
            'menu_ids' => array_values(array_unique($menuIds)),
        ];
    }

    private function validate(array $promotion): array
    {
        $errors = [];
        $nameLength = preg_match_all('/./us', $promotion['name'], $nameCharacters);
        if ($promotion['name'] === '' || $nameLength === false || $nameLength > 150) {
            $errors[] = 'Nama promosi wajib diisi dan maksimal 150 karakter.';
        }
        if (strlen($promotion['description']) > 65535) {
            $errors[] = 'Deskripsi promosi terlalu panjang.';
        }
        if (!in_array($promotion['discount_type'], ['percentage', 'fixed'], true)) {
            $errors[] = 'Jenis diskon tidak valid.';
        }
        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $promotion['discount_value'])
            || (float) $promotion['discount_value'] <= 0
        ) {
            $errors[] = 'Nilai diskon harus lebih besar dari 0.';
        } elseif (
            $promotion['discount_type'] === 'percentage'
            && (float) $promotion['discount_value'] > 100
        ) {
            $errors[] = 'Diskon persentase maksimal 100%.';
        }
        if (!$this->validDate($promotion['start_date'])) {
            $errors[] = 'Tanggal mulai promosi tidak valid.';
        }
        if (!$this->validDate($promotion['end_date'])) {
            $errors[] = 'Tanggal berakhir promosi tidak valid.';
        }
        if ($this->validDate($promotion['start_date'])
            && $this->validDate($promotion['end_date'])
            && $promotion['end_date'] < $promotion['start_date']
        ) {
            $errors[] = 'Tanggal berakhir harus sama atau setelah tanggal mulai.';
        }
        if (!$promotion['menu_ids']) {
            $errors[] = 'Pilih setidaknya satu menu untuk promosi.';
        } else {
            $menuOptions = $this->promotions->menuOptions();
            $validMenuIds = array_map('intval', array_column($menuOptions, 'id'));
            if (array_diff($promotion['menu_ids'], $validMenuIds)) {
                $errors[] = 'Daftar menu promosi tidak valid.';
            }
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

    private function render(?array $formData = null, array $errors = []): void
    {
        $this->view('admin/promotions', [
            'pageTitle' => 'Manage Promotions',
            'promotions' => $this->promotions->all(),
            'formData' => $formData,
            'menuOptions' => $this->promotions->menuOptions(),
            'errors' => $errors,
        ]);
    }

    private function renderForm(array $promotion, array $errors = []): void
    {
        $this->render($promotion, $errors);
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
