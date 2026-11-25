<?php
$pageTitle = $pageTitle ?? 'Reports';
$report = $report ?? [];
$errors = $errors ?? [];
$startDate = $startDate ?? '';
$endDate = $endDate ?? '';
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
<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-muted mb-1">Performance and transactions</p>
            <h1 class="section-title h2 mb-0">Reports</h1>
        </div>
        <button class="btn btn-outline-coffee report-print-hide" type="button" onclick="window.print()">Print report</button>
    </div>
    <?php if ($errors): ?>
        <div class="alert alert-danger report-print-hide" role="alert">
            <?php foreach ($errors as $error): ?><div><?= $escape($error) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="get" action="<?= $escape($baseUrl) ?>/admin/reports" class="cozy-card p-3 mb-4 d-flex flex-wrap align-items-end gap-3 report-print-hide">
        <div>
            <label class="form-label" for="start_date">Start date</label>
            <input class="form-control" id="start_date" name="start_date" type="date" value="<?= $escape($startDate) ?>" required>
        </div>
        <div>
            <label class="form-label" for="end_date">End date</label>
            <input class="form-control" id="end_date" name="end_date" type="date" value="<?= $escape($endDate) ?>" required>
        </div>
        <button class="btn btn-coffee" type="submit">Apply dates</button>
    </form>

    <p class="small text-muted">Period: <?= $escape($startDate) ?> to <?= $escape($endDate) ?></p>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><article class="cozy-card p-4 h-100"><div class="small text-muted">Paid Revenue</div><div class="h3 mb-0">Rp <?= $money($report['revenue'] ?? 0) ?></div></article></div>
        <div class="col-md-4"><article class="cozy-card p-4 h-100"><div class="small text-muted">Transactions</div><div class="h3 mb-0"><?= (int) ($report['transaction_count'] ?? 0) ?></div></article></div>
        <div class="col-md-4"><article class="cozy-card p-4 h-100"><div class="small text-muted">Reservations</div><div class="h3 mb-0"><?= (int) ($report['booking_count'] ?? 0) ?></div></article></div>
    </div>

    <section class="cozy-card mb-4">
        <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Top 5 Best-Selling Menus</h2></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Rank</th><th>Menu</th><th class="text-end">Quantity Sold</th></tr></thead>
                <tbody>
                <?php foreach (($report['top_menus'] ?? []) as $index => $menu): ?>
                    <tr><td><?= $index + 1 ?></td><td><?= $escape($menu['menu_name_snapshot']) ?></td><td class="text-end"><?= (int) $menu['quantity_sold'] ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($report['top_menus'])): ?><tr><td colspan="3" class="text-center text-muted py-4">No menu sales in this period.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="cozy-card">
        <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Transactions in Period</h2></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 report-transactions">
                <thead><tr><th>Date</th><th>Order</th><th>Customer</th><th>Type</th><th>Total</th><th>Payment</th><th>Order Status</th></tr></thead>
                <tbody>
                <?php foreach (($report['transactions'] ?? []) as $transaction): ?>
                    <tr>
                        <td><?= $escape($transaction['created_at']) ?></td>
                        <td><?= $escape($transaction['order_code']) ?></td>
                        <td><?= $escape($transaction['customer_name']) ?></td>
                        <td><?= $escape($transaction['order_type']) ?></td>
                        <td class="text-nowrap">Rp <?= $money($transaction['total_amount']) ?></td>
                        <td><span class="badge <?= $statusClass($transaction['payment_status'] ?? 'Unpaid') ?>"><?= $escape($transaction['payment_status'] ?? 'Unpaid') ?></span></td>
                        <td><span class="badge <?= $statusClass($transaction['status']) ?>"><?= $escape($transaction['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($report['transactions'])): ?><tr><td colspan="7" class="text-center text-muted py-4">No transactions in this period.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
