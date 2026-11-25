<?php
$pageTitle = $pageTitle ?? 'Manage Orders';
$orders = $orders ?? [];
$errors = $errors ?? [];
$selectedStatus = $selectedStatus ?? '';
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$money = static fn ($value): string => number_format((float) $value, 2, ',', '.');
$statuses = ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Completed', 'Cancelled'];
$statusClass = static fn (string $status): string => match ($status) {
    'Completed', 'Ready', 'Paid' => 'text-bg-success',
    'Cancelled' => 'text-bg-danger',
    'Preparing' => 'text-bg-warning',
    default => 'text-bg-secondary',
};
ob_start();
?>
<div class="container-fluid py-2">
    <h1 class="section-title h2 mb-4">Manage Orders</h1>
    <?php if ($errors): ?><div class="alert alert-danger" role="alert"><?php foreach ($errors as $error): ?><div><?= $escape($error) ?></div><?php endforeach; ?></div><?php endif; ?>
    <form method="get" action="<?= $escape($baseUrl) ?>/admin/orders" class="cozy-card p-3 mb-4 d-flex flex-wrap align-items-end gap-3">
        <div>
            <label class="form-label" for="statusFilter">Filter by status</label>
            <select class="form-select" id="statusFilter" name="status">
                <option value="">All statuses</option>
                <?php foreach ($statuses as $status): ?><option value="<?= $escape($status) ?>" <?= $selectedStatus === $status ? 'selected' : '' ?>><?= $escape($status) ?></option><?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-coffee" type="submit">Apply filter</button>
        <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/orders">Clear</a>
    </form>
    <section class="cozy-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Order</th><th>Customer</th><th>Items</th><th>Type / Reservation</th><th>Total</th><th>Payment</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><div class="fw-semibold"><?= $escape($order['order_code']) ?></div><small class="text-muted"><?= $escape($order['created_at']) ?></small></td>
                        <td><?= $escape($order['customer_name']) ?><br><small class="text-muted"><?= $escape($order['customer_email']) ?></small></td>
                        <td>
                            <?php foreach ($order['items'] as $item): ?>
                                <div><?= $escape($item['menu_name_snapshot']) ?> × <?= (int) $item['quantity'] ?></div>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <?= $escape($order['order_type']) ?>
                            <?php if ($order['order_type'] === 'Dine-in' && $order['booking_date']): ?>
                                <div class="small text-muted"><?= $escape($order['booking_date']) ?> · <?= $escape($order['table_number']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-nowrap">Rp <?= $money($order['total_amount']) ?></td>
                        <td>
                            <span class="badge <?= $statusClass($order['payment_status']) ?>"><?= $escape($order['payment_status']) ?></span>
                            <div class="small text-muted"><?= $escape($order['payment_method']) ?></div>
                            <?php if ($order['payment_status'] === 'Unpaid' && $order['status'] !== 'Cancelled'): ?>
                                <form method="post" action="<?= $escape($baseUrl) ?>/admin/orders/payment" class="mt-2">
                                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                    <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                    <button class="btn btn-sm btn-outline-success" type="submit">Confirm Cash Payment</button>
                                </form>
                            <?php elseif ($order['paid_at']): ?>
                                <div class="small text-muted"><?= $escape($order['paid_at']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $statusClass($order['status']) ?>"><?= $escape($order['status']) ?></span>
                            <form method="post" action="<?= $escape($baseUrl) ?>/admin/orders/status" class="mt-2 d-flex gap-2">
                                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                <select class="form-select form-select-sm" name="status" aria-label="Order status">
                                    <?php foreach ($statuses as $status): ?><option value="<?= $escape($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= $escape($status) ?></option><?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-outline-coffee" type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?><tr><td colspan="7" class="text-center text-muted py-5">No orders found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
