<?php
$pageTitle = $pageTitle ?? 'Reserve a Table';
$errors = $errors ?? [];
$formData = $formData ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$minimumDate = date('Y-m-d');
ob_start();
?>
<section class="container py-5" style="max-width: 760px;">
    <div class="mb-4">
        <p class="text-uppercase small text-muted mb-1">Plan your cozy visit</p>
        <h1 class="section-title h2">Reserve a Table</h1>
        <p class="text-muted mb-0">Reservations are for two hours and are confirmed when a table is available.</p>
    </div>
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <section class="cozy-card">
        <form id="reservationForm" method="post" action="<?= $escape($baseUrl) ?>/reservation" data-availability-url="<?= $escape($baseUrl) ?>/reservation/availability" class="p-4 p-md-5">
            <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="booking_date">Date</label>
                    <input class="form-control" id="booking_date" name="booking_date" type="date" min="<?= $escape($minimumDate) ?>" value="<?= $escape($formData['booking_date'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="start_time">Start time</label>
                    <input class="form-control" id="start_time" name="start_time" type="time" step="1800" value="<?= $escape($formData['start_time'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="guest_count">Number of guests</label>
                    <input class="form-control" id="guest_count" name="guest_count" type="number" min="1" max="1000" value="<?= $escape((string) ($formData['guest_count'] ?? '2')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="table_id">Available table</label>
                    <select class="form-select" id="table_id" name="table_id" required disabled>
                        <option value="">Choose date, time, and guests first</option>
                    </select>
                </div>
                <div class="col-12">
                    <div class="form-text" id="availabilityMessage" role="status">Choose a date, time, and guest count to check table availability.</div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="notes">Notes (optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Any special requests?"><?= $escape($formData['notes'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <button class="btn btn-coffee" type="submit">Confirm Reservation</button>
                    <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/bookings">View My Bookings</a>
                </div>
            </div>
        </form>
    </section>
</section>
<script>
(() => {
    const form = document.getElementById('reservationForm');
    const dateInput = document.getElementById('booking_date');
    const timeInput = document.getElementById('start_time');
    const guestsInput = document.getElementById('guest_count');
    const tableSelect = document.getElementById('table_id');
    const message = document.getElementById('availabilityMessage');
    let activeRequest = 0;

    async function refreshAvailability() {
        const requestNumber = ++activeRequest;
        const date = dateInput.value;
        const time = timeInput.value;
        const guests = guestsInput.value;
        tableSelect.replaceChildren(new Option('Checking availability...', ''));
        tableSelect.disabled = true;

        if (!date || !time || !guests) {
            tableSelect.replaceChildren(new Option('Choose date, time, and guests first', ''));
            message.textContent = 'Choose a date, time, and guest count to check table availability.';
            return;
        }

        message.textContent = 'Checking available tables...';
        const query = new URLSearchParams({ date, time, guests });

        try {
            const response = await fetch(form.dataset.availabilityUrl + '?' + query.toString(), {
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();
            if (requestNumber !== activeRequest) {
                return;
            }
            if (!response.ok) {
                tableSelect.replaceChildren(new Option('No available table', ''));
                message.textContent = result.error || 'Unable to check availability.';
                return;
            }
            if (!result.tables.length) {
                tableSelect.replaceChildren(new Option('No available table', ''));
                message.textContent = 'No tables are available for this date and time.';
                return;
            }

            tableSelect.replaceChildren(new Option('Select a table', ''));
            result.tables.forEach((table) => {
                const label = `${table.table_number} — ${table.capacity} seats`;
                tableSelect.add(new Option(label, table.id));
            });
            tableSelect.disabled = false;
            message.textContent = `${result.tables.length} table(s) available.`;
        } catch (error) {
            if (requestNumber === activeRequest) {
                tableSelect.replaceChildren(new Option('Availability unavailable', ''));
                message.textContent = 'Could not check availability. Please try again.';
            }
        }
    }

    [dateInput, timeInput, guestsInput].forEach((input) => {
        input.addEventListener('change', refreshAvailability);
    });
    if (dateInput.value && timeInput.value && guestsInput.value) {
        refreshAvailability();
    }
})();
</script>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
