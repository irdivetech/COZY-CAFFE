<?php

class AuthController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function showLogin(): void
    {
        if (AuthHelper::currentUser()) {
            $this->redirectForRole(AuthHelper::currentUser()['role']);
        }

        $this->view('auth/login', [
            'pageTitle' => 'Login',
            'error' => null,
            'email' => '',
        ]);
    }

    public function login(): void
    {
        if (!SecurityHelper::verifyCsrfToken()) {
            http_response_code(400);
            $this->view('auth/login', [
                'pageTitle' => 'Login',
                'error' => 'Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.',
                'email' => '',
            ]);
            return;
        }

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $user = filter_var($email, FILTER_VALIDATE_EMAIL)
            ? $this->users->findByEmail($email)
            : null;

        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(422);
            $this->view('auth/login', [
                'pageTitle' => 'Login',
                'error' => 'Email atau password tidak sesuai.',
                'email' => $email,
            ]);
            return;
        }

        session_regenerate_id(true);
        unset($_SESSION['_csrf_token']);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role' => $user['role'],
        ];

        $this->redirectForRole($user['role']);
    }

    public function showRegister(): void
    {
        if (AuthHelper::currentUser()) {
            $this->redirectForRole(AuthHelper::currentUser()['role']);
        }

        $this->view('auth/register', [
            'pageTitle' => 'Register',
            'errors' => [],
            'name' => '',
            'email' => '',
        ]);
    }

    public function register(): void
    {
        if (!SecurityHelper::verifyCsrfToken()) {
            http_response_code(400);
            $this->view('auth/register', [
                'pageTitle' => 'Register',
                'errors' => ['Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.'],
                'name' => '',
                'email' => '',
            ]);
            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
        $errors = [];

        if ($name === '' || $this->characterLength($name) > 100) {
            $errors[] = 'Nama wajib diisi dan maksimal 100 karakter.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 191) {
            $errors[] = 'Masukkan alamat email yang valid.';
        } elseif ($this->users->emailExists($email)) {
            $errors[] = 'Email sudah terdaftar.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password harus terdiri dari minimal 8 karakter.';
        }
        if ($password !== $passwordConfirmation) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        }

        if ($errors) {
            http_response_code(422);
            $this->view('auth/register', [
                'pageTitle' => 'Register',
                'errors' => $errors,
                'name' => $name,
                'email' => $email,
            ]);
            return;
        }

        try {
            $this->users->createCustomer($name, $email, $password);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000' || (int) ($exception->errorInfo[1] ?? 0) !== 1062) {
                throw $exception;
            }

            http_response_code(422);
            $this->view('auth/register', [
                'pageTitle' => 'Register',
                'errors' => ['Email sudah terdaftar.'],
                'name' => $name,
                'email' => $email,
            ]);
            return;
        }

        FlashHelper::success('Pendaftaran berhasil. Silakan login.');
        $this->redirect($this->baseUrl() . '/login');
    }

    public function logout(): void
    {
        if (!SecurityHelper::verifyCsrfToken()) {
            http_response_code(400);
            echo '400 - Invalid CSRF token';
            return;
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $cookie['path'],
                'domain' => $cookie['domain'],
                'secure' => $cookie['secure'],
                'httponly' => $cookie['httponly'],
                'samesite' => $cookie['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();

        $this->redirect($this->baseUrl() . '/login');
    }

    public function showCustomerProfile(): void
    {
        AuthHelper::requireCustomer();
        $this->showProfile('customer/profile', 'My Profile');
    }

    public function showAdminProfile(): void
    {
        AuthHelper::requireAdmin();
        $this->showProfile('admin/profile', 'Admin Profile');
    }

    public function updateCustomerProfile(): void
    {
        AuthHelper::requireCustomer();
        $this->updateProfile('customer/profile', 'My Profile');
    }

    public function updateAdminProfile(): void
    {
        AuthHelper::requireAdmin();
        $this->updateProfile('admin/profile', 'Admin Profile');
    }

    private function showProfile(string $view, string $pageTitle): void
    {
        $user = AuthHelper::currentUser();
        $data = [
            'pageTitle' => $pageTitle,
            'user' => $user,
            'errors' => [],
            'success' => null,
        ];
        if (($user['role'] ?? '') === 'customer') {
            $data['cartCount'] = (new CartModel())->itemCount((int) $user['id']);
        }
        $this->view($view, $data);
    }

    private function updateProfile(string $view, string $pageTitle): void
    {
        $user = AuthHelper::currentUser();

        if (!SecurityHelper::verifyCsrfToken()) {
            http_response_code(400);
            $this->view($view, [
                'pageTitle' => $pageTitle,
                'user' => $user,
                'errors' => ['Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.'],
                'success' => null,
            ]);
            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
        $errors = [];

        if ($name === '' || $this->characterLength($name) > 100) {
            $errors[] = 'Nama wajib diisi dan maksimal 100 karakter.';
        }
        if (strlen($phone) > 30) {
            $errors[] = 'Nomor telepon maksimal 30 karakter.';
        }

        $changingPassword = $newPassword !== '' || $passwordConfirmation !== '';
        if ($changingPassword) {
            $account = $this->users->findByEmail($user['email']);
            if (!$account || !password_verify($currentPassword, $account['password'])) {
                $errors[] = 'Password lama tidak sesuai.';
            }
            if (strlen($newPassword) < 8) {
                $errors[] = 'Password baru harus terdiri dari minimal 8 karakter.';
            }
            if ($newPassword !== $passwordConfirmation) {
                $errors[] = 'Konfirmasi password baru tidak cocok.';
            }
        }

        if ($errors) {
            http_response_code(422);
            $user['name'] = $name;
            $user['phone'] = $phone;
            $this->view($view, [
                'pageTitle' => $pageTitle,
                'user' => $user,
                'errors' => $errors,
                'success' => null,
            ]);
            return;
        }

        $this->users->updateProfile(
            (int) $user['id'],
            $name,
            $phone === '' ? null : $phone,
            $changingPassword ? $newPassword : null
        );

        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['phone'] = $phone === '' ? null : $phone;

        $data = [
            'pageTitle' => $pageTitle,
            'user' => $_SESSION['user'],
            'errors' => [],
            'success' => 'Profil berhasil diperbarui.',
        ];
        if (($_SESSION['user']['role'] ?? '') === 'customer') {
            $data['cartCount'] = (new CartModel())->itemCount((int) $user['id']);
        }
        $this->view($view, $data);
    }

    private function redirectForRole(string $role): void
    {
        $destination = $role === 'admin' ? '/admin/dashboard' : '/';
        $this->redirect($this->baseUrl() . $destination);
    }

    private function baseUrl(): string
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $baseUrl = dirname($scriptName);

        return $baseUrl === '/' || $baseUrl === '.' ? '' : rtrim($baseUrl, '/');
    }

    private function characterLength(string $value): int
    {
        $matched = preg_match_all('/./us', $value, $characters);
        return $matched === false ? PHP_INT_MAX : $matched;
    }
}
