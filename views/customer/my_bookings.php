<?php
$pageTitle = $pageTitle ?? 'My Bookings';
$bookings = $bookings ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
ob_start();
?>
<section class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-muted mb-1">Your visits</p>
            <h1 class="section-title h2 mb-0">My Bookings</h1>
        </div>
        <a class="btn btn-coffee" href="<?= $escape($baseUrl) ?>/reservation">Make a Reservation</a>
    </div>
    <section class="cozy-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Date</th><th>Time</th><th>Table</th><th>Guests</th><th>Status</th><th>Notes</th></tr></thead>
                <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= $escape($booking['booking_date']) ?></td>
                        <td><?= $escape(substr($booking['start_time'], 0, 5)) ?>–<?= $escape(substr($booking['end_time'], 0, 5)) ?></td>
                        <td><?= $escape($booking['table_number']) ?></td>
                        <td><?= (int) $booking['guest_count'] ?></td>
                        <td><span class="badge-status <?= $booking['status'] === 'Confirmed' ? 'badge-confirmed' : ($booking['status'] === 'Cancelled' ? 'badge-cancelled' : 'badge-completed') ?>"><?= $escape($booking['status']) ?></span></td>
                        <td><?= nl2br($escape($booking['notes'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$bookings): ?><tr><td colspan="6" class="text-center text-muted py-5">You have no bookings yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
