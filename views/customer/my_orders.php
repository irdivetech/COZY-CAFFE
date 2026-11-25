<?php
$pageTitle = $pageTitle ?? 'My Orders';
$orders = $orders ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$money = static fn ($value): string => number_format((float) $value, 2, ',', '.');
$statusClass = static fn (string $status): string => match ($status) {
    'Completed', 'Ready', 'Paid' => 'text-bg-success',
    'Cancelled' => 'text-bg-danger',
    'Preparing' => 'text-bg-warning',
    default => 'text-bg-secondary',
};
ob_start();
?>
<section class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-muted mb-1">Order history</p>
            <h1 class="section-title h2 mb-0">My Orders</h1>
        </div>
        <a class="btn btn-coffee" href="<?= $escape($baseUrl) ?>/menu">Browse Menu</a>
    </div>
    <?php if (!$orders): ?>
        <div class="cozy-card p-5 text-center">
            <h2 class="h4">No orders yet</h2>
            <p class="text-muted">Your completed checkouts will appear here.</p>
            <a class="btn btn-coffee" href="<?= $escape($baseUrl) ?>/menu">Explore Menu</a>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-4">
            <?php foreach ($orders as $order): ?>
                <article class="cozy-card p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1"><?= $escape($order['order_code']) ?></h2>
                            <div class="small text-muted"><?= $escape($order['created_at']) ?> · <?= $escape($order['order_type']) ?></div>
                            <?php if ($order['order_type'] === 'Dine-in' && $order['booking_date']): ?>
                                <div class="small text-muted">
                                    Reservation: <?= $escape($order['booking_date']) ?>,
                                    <?= $escape(substr($order['start_time'], 0, 5)) ?>–<?= $escape(substr($order['end_time'], 0, 5)) ?>,
                                    <?= $escape($order['table_number']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge <?= $statusClass($order['status']) ?>"><?= $escape($order['status']) ?></span>
                            <span class="badge <?= $statusClass($order['payment_status']) ?>"><?= $escape($order['payment_status']) ?> · <?= $escape($order['payment_method']) ?></span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-3">
                            <thead><tr><th>Item</th><th>Price snapshot</th><th>Qty</th><th>Notes</th><th class="text-end">Subtotal</th></tr></thead>
                            <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?= $escape($item['menu_name_snapshot']) ?></td>
                                    <td>Rp <?= $money($item['price_snapshot']) ?></td>
                                    <td><?= (int) $item['quantity'] ?></td>
                                    <td><?= $escape($item['notes'] ?? '') ?></td>
                                    <td class="text-end">Rp <?= $money($item['subtotal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="row justify-content-end">
                        <div class="col-md-5 col-lg-4">
                            <div class="d-flex justify-content-between"><span>Subtotal</span><span>Rp <?= $money($order['subtotal']) ?></span></div>
                            <div class="d-flex justify-content-between text-success"><span>Discount</span><span>−Rp <?= $money($order['discount_amount']) ?></span></div>
                            <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-2"><span>Total</span><span>Rp <?= $money($order['total_amount']) ?></span></div>
                            <?php if ($order['paid_at']): ?><div class="small text-muted text-end mt-1">Paid <?= $escape($order['paid_at']) ?></div><?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
