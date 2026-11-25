<?php
$pageTitle = $pageTitle ?? 'Checkout';
$cart = $cart ?? [];
$bookings = $bookings ?? [];
$errors = $errors ?? [];
$formData = $formData ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$money = static fn (int $cents): string => number_format($cents / 100, 2, ',', '.');
$orderType = $formData['order_type'] ?? 'Takeaway';
$bookingMode = $formData['booking_mode'] ?? ($bookings ? 'existing' : 'new');
$minimumDate = date('Y-m-d');
ob_start();
?>
<section class="container py-5">
    <div class="mb-4">
        <p class="text-uppercase small text-muted mb-1">One last step</p>
        <h1 class="section-title h2">Checkout</h1>
    </div>
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <form method="post" action="<?= $escape($baseUrl) ?>/checkout" id="checkoutForm">
        <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <section class="cozy-card p-4 mb-4">
                    <h2 class="h5 mb-3">Order type</h2>
                    <label class="form-label" for="order_type">Choose how you will receive your order</label>
                    <select class="form-select" id="order_type" name="order_type" required>
                        <option value="Takeaway" <?= $orderType === 'Takeaway' ? 'selected' : '' ?>>Takeaway</option>
                        <option value="Dine-in" <?= $orderType === 'Dine-in' ? 'selected' : '' ?>>Dine-in</option>
                    </select>
                </section>

                <section class="cozy-card p-4" id="dineInOptions" hidden>
                    <h2 class="h5 mb-3">Dine-in reservation</h2>
                    <label class="form-label" for="booking_mode">Reservation option</label>
                    <select class="form-select mb-3" id="booking_mode" name="booking_mode">
                        <?php if ($bookings): ?><option value="existing" <?= $bookingMode === 'existing' ? 'selected' : '' ?>>Use an existing reservation</option><?php endif; ?>
                        <option value="new" <?= $bookingMode === 'new' ? 'selected' : '' ?>>Create a new reservation</option>
                    </select>

                    <div id="existingBookingFields">
                        <label class="form-label" for="booking_id">Active reservation</label>
                        <select class="form-select" id="booking_id" name="booking_id">
                            <option value="">Select a reservation</option>
                            <?php foreach ($bookings as $booking): ?>
                                <?php
                                $bookingLabel = $booking['booking_date'] . ' ' . substr($booking['start_time'], 0, 5)
                                    . ' — ' . $booking['table_number'] . ' (' . (int) $booking['guest_count'] . ' guests)';
                                ?>
                                <option value="<?= (int) $booking['id'] ?>" <?= (string) ($formData['booking_id'] ?? '') === (string) $booking['id'] ? 'selected' : '' ?>>
                                    <?= $escape($bookingLabel) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!$bookings): ?><p class="form-text">You have no upcoming confirmed reservations. Create one below.</p><?php endif; ?>
                    </div>

                    <div id="newBookingFields" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="booking_date">Reservation date</label>
                            <input class="form-control" id="booking_date" name="booking_date" type="date" min="<?= $escape($minimumDate) ?>" value="<?= $escape($formData['booking_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="start_time">Start time</label>
                            <input class="form-control" id="start_time" name="start_time" type="time" step="1800" value="<?= $escape($formData['start_time'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="guest_count">Number of guests</label>
                            <input class="form-control" id="guest_count" name="guest_count" type="number" min="1" max="1000" value="<?= $escape((string) ($formData['guest_count'] ?? '2')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="table_id">Available table</label>
                            <select class="form-select" id="table_id" name="table_id" data-selected="<?= $escape((string) ($formData['table_id'] ?? '')) ?>" disabled>
                                <option value="">Choose date, time, and guests first</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-text" id="availabilityMessage" role="status">Choose a date, time, and guest count to check availability.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="booking_notes">Reservation notes (optional)</label>
                            <textarea class="form-control" id="booking_notes" name="booking_notes" maxlength="2000" rows="3"><?= $escape($formData['booking_notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-lg-5">
                <aside class="cozy-card p-4">
                    <h2 class="h5 mb-3">Order summary</h2>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($cart['items'] as $item): ?>
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold"><?= $escape($item['menu_name']) ?> × <?= (int) $item['quantity'] ?></div>
                                    <?php if ($item['notes']): ?><small class="text-muted"><?= $escape($item['notes']) ?></small><?php endif; ?>
                                    <?php if ($item['line_discount_cents'] > 0): ?><div class="small text-success">Discount −Rp <?= $money($item['line_discount_cents']) ?></div><?php endif; ?>
                                </div>
                                <span class="text-nowrap">Rp <?= $money($item['line_total_cents']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>Rp <?= $money($cart['subtotal_cents']) ?></span></div>
                    <div class="d-flex justify-content-between mb-3 text-success"><span>Discount</span><span>−Rp <?= $money($cart['discount_cents']) ?></span></div>
                    <div class="d-flex justify-content-between fw-bold fs-5 mb-3"><span>Total</span><span>Rp <?= $money($cart['total_cents']) ?></span></div>
                    <div class="small text-muted mb-3">Payment method: Cash at the cafe. Payment status starts as Unpaid.</div>
                    <button class="btn btn-coffee w-100" type="submit">Place Order</button>
                    <a class="btn btn-outline-coffee w-100 mt-2" href="<?= $escape($baseUrl) ?>/cart">Back to Cart</a>
                </aside>
            </div>
        </div>
    </form>
</section>
<script>
(() => {
    const form = document.getElementById('checkoutForm');
    const orderType = document.getElementById('order_type');
    const dineInOptions = document.getElementById('dineInOptions');
    const bookingMode = document.getElementById('booking_mode');
    const existingFields = document.getElementById('existingBookingFields');
    const newFields = document.getElementById('newBookingFields');
    const bookingSelect = document.getElementById('booking_id');
    const dateInput = document.getElementById('booking_date');
    const timeInput = document.getElementById('start_time');
    const guestsInput = document.getElementById('guest_count');
    const tableSelect = document.getElementById('table_id');
    const message = document.getElementById('availabilityMessage');
    let activeRequest = 0;

    function updateBookingFields() {
        const isDineIn = orderType.value === 'Dine-in';
        const useExisting = isDineIn && bookingMode.value === 'existing';
        dineInOptions.hidden = !isDineIn;
        existingFields.hidden = !useExisting;
        newFields.hidden = !isDineIn || !useExisting;
        bookingSelect.required = useExisting;
        bookingSelect.disabled = !useExisting;
        [dateInput, timeInput, guestsInput, tableSelect].forEach((input) => {
            input.required = isDineIn && !useExisting;
            input.disabled = !isDineIn || useExisting;
        });
        document.getElementById('booking_notes').disabled = !isDineIn || useExisting;
    }

    async function refreshAvailability() {
        const requestNumber = ++activeRequest;
        const selectedTable = tableSelect.dataset.selected;
        tableSelect.replaceChildren(new Option('Checking availability...', ''));
        tableSelect.disabled = true;
        if (!dateInput.value || !timeInput.value || !guestsInput.value) {
            tableSelect.replaceChildren(new Option('Choose date, time, and guests first', ''));
            if (message) message.textContent = 'Choose a date, time, and guest count to check availability.';
            updateBookingFields();
            return;
        }

        if (message) message.textContent = 'Checking available tables...';
        const query = new URLSearchParams({
            date: dateInput.value,
            time: timeInput.value,
            guests: guestsInput.value
        });
        try {
            const response = await fetch('<?= $escape($baseUrl) ?>/reservation/availability?' + query.toString(), {
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();
            if (requestNumber !== activeRequest) return;
            if (!response.ok || !result.tables.length) {
                tableSelect.replaceChildren(new Option('No available table', ''));
                if (message) message.textContent = result.error || 'No tables are available for this date and time.';
                updateBookingFields();
                return;
            }

            tableSelect.replaceChildren(new Option('Select a table', ''));
            result.tables.forEach((table) => {
                const option = new Option(`${table.table_number} — ${table.capacity} seats`, table.id);
                if (String(table.id) === selectedTable) option.selected = true;
                tableSelect.add(option);
            });
            tableSelect.dataset.selected = '';
            if (message) message.textContent = `${result.tables.length} table(s) available.`;
        } catch (error) {
            if (requestNumber === activeRequest) {
                tableSelect.replaceChildren(new Option('Availability unavailable', ''));
                if (message) message.textContent = 'Could not check availability. Please try again.';
            }
        }
        updateBookingFields();
    }

    orderType.addEventListener('change', updateBookingFields);
    bookingMode.addEventListener('change', updateBookingFields);
    [dateInput, timeInput, guestsInput].forEach((input) => {
        input.addEventListener('change', refreshAvailability);
    });
    updateBookingFields();
    if (dateInput.value && timeInput.value && guestsInput.value) refreshAvailability();
})();
</script>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
