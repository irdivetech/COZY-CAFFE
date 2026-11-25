<?php

require_once dirname(__DIR__) . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    if (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443')
    ) {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}

spl_autoload_register(function (string $className): void {
    $projectRoot = dirname(__DIR__);
    $paths = [
        $projectRoot . '/app/Core/' . $className . '.php',
        $projectRoot . '/app/Helpers/' . $className . '.php',
        $projectRoot . '/app/Models/' . $className . '.php',
        $projectRoot . '/app/Services/' . $className . '.php',
        $projectRoot . '/app/Controllers/' . $className . '.php',
    ];

    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

$router = new Router();

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/profile', [AuthController::class, 'showCustomerProfile']);
$router->post('/profile', [AuthController::class, 'updateCustomerProfile']);
$router->get('/admin/profile', [AuthController::class, 'showAdminProfile']);
$router->post('/admin/profile', [AuthController::class, 'updateAdminProfile']);

$router->get('/notifications', [NotificationController::class, 'index']);
$router->post('/notifications/read', [NotificationController::class, 'markRead']);
$router->post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
$router->get('/admin/notifications', [NotificationController::class, 'adminIndex']);
$router->post('/admin/notifications/read', [NotificationController::class, 'adminMarkRead']);
$router->post('/admin/notifications/read-all', [NotificationController::class, 'adminMarkAllRead']);
$router->get('/admin/dashboard', [AdminDashboardController::class, 'index']);
$router->get('/admin/reports', [AdminReportController::class, 'index']);

$router->get('/', [HomeController::class, 'index']);
$router->get('/menu', [MenuController::class, 'index']);
$router->get('/menu/detail', [MenuController::class, 'detail']);

$router->get('/admin/categories', [AdminCategoryController::class, 'index']);
$router->get('/admin/categories/edit', [AdminCategoryController::class, 'edit']);
$router->post('/admin/categories/create', [AdminCategoryController::class, 'create']);
$router->post('/admin/categories/update', [AdminCategoryController::class, 'update']);
$router->post('/admin/categories/delete', [AdminCategoryController::class, 'delete']);

$router->get('/admin/menus', [AdminMenuController::class, 'index']);
$router->get('/admin/menus/new', [AdminMenuController::class, 'createForm']);
$router->get('/admin/menus/edit', [AdminMenuController::class, 'editForm']);
$router->post('/admin/menus/create', [AdminMenuController::class, 'create']);
$router->post('/admin/menus/update', [AdminMenuController::class, 'update']);
$router->post('/admin/menus/disable', [AdminMenuController::class, 'disable']);

$router->get('/admin/tables', [AdminTableController::class, 'index']);
$router->get('/admin/tables/edit', [AdminTableController::class, 'edit']);
$router->post('/admin/tables/create', [AdminTableController::class, 'create']);
$router->post('/admin/tables/update', [AdminTableController::class, 'update']);
$router->post('/admin/tables/toggle', [AdminTableController::class, 'toggle']);

$router->get('/admin/customers', [AdminCustomerController::class, 'index']);

$router->get('/admin/operating-hours', [AdminOperatingHourController::class, 'index']);
$router->post('/admin/operating-hours/update', [AdminOperatingHourController::class, 'update']);

$router->get('/admin/closures', [AdminClosureController::class, 'index']);
$router->get('/admin/closures/edit', [AdminClosureController::class, 'edit']);
$router->post('/admin/closures/create', [AdminClosureController::class, 'create']);
$router->post('/admin/closures/update', [AdminClosureController::class, 'update']);
$router->post('/admin/closures/delete', [AdminClosureController::class, 'delete']);

$router->get('/admin/promotions', [AdminPromotionController::class, 'index']);
$router->get('/admin/promotions/new', [AdminPromotionController::class, 'createForm']);
$router->get('/admin/promotions/edit', [AdminPromotionController::class, 'editForm']);
$router->post('/admin/promotions/create', [AdminPromotionController::class, 'create']);
$router->post('/admin/promotions/update', [AdminPromotionController::class, 'update']);
$router->post('/admin/promotions/delete', [AdminPromotionController::class, 'delete']);

$router->get('/cart', [CartController::class, 'index']);
$router->post('/cart/add', [CartController::class, 'add']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->post('/cart/change', [CartController::class, 'changeQuantity']);
$router->post('/cart/remove', [CartController::class, 'remove']);

$router->get('/checkout', [CheckoutController::class, 'index']);
$router->post('/checkout', [CheckoutController::class, 'placeOrder']);
$router->get('/orders', [OrderController::class, 'index']);
$router->get('/admin/orders', [AdminOrderController::class, 'index']);
$router->post('/admin/orders/status', [AdminOrderController::class, 'updateStatus']);
$router->post('/admin/orders/payment', [AdminOrderController::class, 'confirmPayment']);
$router->get('/admin/bookings', [AdminBookingController::class, 'index']);
$router->post('/admin/bookings/status', [AdminBookingController::class, 'updateStatus']);

$router->get('/reservation', [BookingController::class, 'reservation']);
$router->get('/reservation/availability', [BookingController::class, 'availability']);
$router->post('/reservation', [BookingController::class, 'create']);
$router->get('/bookings', [BookingController::class, 'myBookings']);

$router->dispatch();
