<?php

class MenuController extends Controller
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
        $currentUser = AuthHelper::currentUser();
        $categoryId = null;
        $filterError = null;
        if (isset($_GET['category'])) {
            if (!is_string($_GET['category'])) {
                http_response_code(422);
                $filterError = 'Kategori filter tidak valid.';
            } elseif ($_GET['category'] !== '') {
                $validatedId = filter_var($_GET['category'], FILTER_VALIDATE_INT);
                if ($validatedId && $this->categories->find((int) $validatedId)) {
                    $categoryId = (int) $validatedId;
                } else {
                    http_response_code(422);
                    $filterError = 'Kategori filter tidak valid.';
                }
            }
        }

        $rawSearch = $_GET['search'] ?? '';
        $search = is_string($rawSearch) ? trim($rawSearch) : '';
        if (!is_string($rawSearch)) {
            http_response_code(422);
            $filterError = 'Kata pencarian tidak valid.';
        }
        if (preg_match_all('/./us', $search, $characters) === false
            || count($characters[0]) > 100
        ) {
            http_response_code(422);
            $filterError = 'Kata pencarian maksimal 100 karakter dan harus berupa teks UTF-8 yang valid.';
        }

        $this->view('customer/menu', [
            'pageTitle' => 'Our Menu',
            'menus' => $filterError === null ? $this->menus->available(0, $categoryId, $search) : [],
            'categories' => $this->categories->all(),
            'selectedCategory' => $categoryId,
            'search' => $search,
            'filterError' => $filterError,
            'currentUser' => $currentUser,
            'cartCount' => ($currentUser['role'] ?? '') === 'customer'
                ? (new CartModel())->itemCount((int) $currentUser['id'])
                : 0,
        ]);
    }

    public function detail(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $menu = $id ? $this->menus->findAvailable((int) $id) : null;

        if (!$menu) {
            http_response_code(404);
            echo '404 - Menu not found';
            return;
        }

        $currentUser = AuthHelper::currentUser();
        $this->view('customer/menu_detail', [
            'pageTitle' => $menu['name'],
            'menu' => $menu,
            'currentUser' => $currentUser,
            'cartCount' => ($currentUser['role'] ?? '') === 'customer'
                ? (new CartModel())->itemCount((int) $currentUser['id'])
                : 0,
        ]);
    }
}
