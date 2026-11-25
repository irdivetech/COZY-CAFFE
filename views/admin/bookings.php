<?php
$pageTitle = $pageTitle ?? 'Manage Bookings';
$bookings = $bookings ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$statusClass = static fn (string $status): string => match ($status) {
    'Completed' => 'text-bg-success',
    'Cancelled' => 'text-bg-danger',
    default => 'text-bg-secondary',
};
ob_start();
?>
<div class="container-fluid py-2">
    <h1 class="section-title h2 mb-4">Manage Bookings</h1>
    <section class="cozy-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Date / Time</th><th>Customer</th><th>Table</th><th>Guests</th><th>Notes</th><th>Status / Action</th></tr></thead>
                <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= $escape($booking['booking_date']) ?><br><?= $escape(substr($booking['start_time'], 0, 5)) ?>–<?= $escape(substr($booking['end_time'], 0, 5)) ?></td>
                        <td><?= $escape($booking['customer_name']) ?><br><small class="text-muted"><?= $escape($booking['customer_email']) ?></small></td>
                        <td><?= $escape($booking['table_number']) ?></td>
                        <td><?= (int) $booking['guest_count'] ?></td>
                        <td><?= nl2br($escape($booking['notes'] ?? '')) ?></td>
                        <td>
                            <span class="badge <?= $statusClass($booking['status']) ?>"><?= $escape($booking['status']) ?></span>
                            <?php if ($booking['status'] === 'Confirmed'): ?>
                                <form method="post" action="<?= $escape($baseUrl) ?>/admin/bookings/status" class="d-flex flex-wrap gap-2 mt-2">
                                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                    <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                    <button class="btn btn-sm btn-outline-success" type="submit" name="status" value="Completed">Mark Completed</button>
                                    <button class="btn btn-sm btn-outline-danger" type="submit" name="status" value="Cancelled">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$bookings): ?><tr><td colspan="6" class="text-center text-muted py-5">No bookings found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
