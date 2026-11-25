<?php
$pageTitle = $pageTitle ?? 'Reservasi Meja';
$tables = $tables ?? [];
$operatingHours = $operatingHours ?? [];
$errors = $errors ?? [];
$selectedDate = $selectedDate ?? date('Y-m-d');
$selectedTime = $selectedTime ?? '10:00';
$guestCount = $guestCount ?? 2;
$selectedTableId = $selectedTableId ?? null;
$notes = $notes ?? '';
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
ob_start();
?>
<section class="container py-5" style="max-width: 800px;">
    <div class="mb-4 text-center">
        <p class="text-uppercase small text-muted mb-1">Makan di Tempat Nyaman</p>
        <h1 class="section-title h2">Reservasi Meja Online</h1>
        <p class="text-muted">Setiap reservasi berlaku selama <strong>2 jam</strong> dan otomatis terkonfirmasi saat meja tersedia.</p>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                    <li><?= $escape($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="cozy-card p-4 p-md-5">
        <form method="post" action="<?= $escape($baseUrl) ?>/reservation" id="reservationForm">
            <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="bookingDate">Tanggal Reservasi</label>
                    <input class="form-control" id="bookingDate" name="date" type="date" min="<?= date('Y-m-d') ?>" value="<?= $escape($selectedDate) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="bookingTime">Jam Mulai</label>
                    <input class="form-control" id="bookingTime" name="start_time" type="time" value="<?= $escape($selectedTime) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="guestCount">Jumlah Tamu</label>
                    <input class="form-control" id="guestCount" name="guest_count" type="number" min="1" max="20" value="<?= (int) $guestCount ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" for="tableSelect">Pilih Meja yang Tersedia</label>
                <select class="form-select" id="tableSelect" name="table_id" required>
                    <option value="">-- Memeriksa Meja Tersedia... --</option>
                </select>
                <div class="small text-muted mt-1" id="availabilityMessage">Sistem akan memeriksa meja yang pas dengan jumlah tamu Anda.</div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold" for="notes">Catatan Tambahan (opsional)</label>
                <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="255" placeholder="Contoh: Meja bebas asap rokok, ada anak kecil, perayaan santai..."><?= $escape($notes) ?></textarea>
            </div>

            <button class="btn btn-coffee btn-lg w-100" type="submit"><i class="fa-solid fa-calendar-check me-2"></i>Konfirmasi Reservasi Meja</button>
        </form>
    </section>
</section>

<script>
(() => {
    const dateInput = document.getElementById('bookingDate');
    const timeInput = document.getElementById('bookingTime');
    const guestsInput = document.getElementById('guestCount');
    const tableSelect = document.getElementById('tableSelect');
    const message = document.getElementById('availabilityMessage');
    const baseUrl = <?= json_encode($baseUrl) ?>;
    let activeReq = 0;

    async function refreshAvailability() {
        const date = dateInput.value;
        const time = timeInput.value;
        const guests = guestsInput.value;
        if (!date || !time || !guests) return;

        const reqId = ++activeReq;
        tableSelect.disabled = true;
        message.textContent = 'Memeriksa ketersediaan meja...';

        try {
            const url = `${baseUrl}/reservation/availability?date=${encodeURIComponent(date)}&time=${encodeURIComponent(time)}&guest_count=${encodeURIComponent(guests)}`;
            const res = await fetch(url);
            const data = await res.json();
            if (reqId !== activeReq) return;

            if (!res.ok) {
                tableSelect.innerHTML = '<option value="">Tidak ada meja tersedia</option>';
                message.textContent = data.error || 'Gagal memeriksa ketersediaan meja.';
                return;
            }

            if (!data.tables || !data.tables.length) {
                tableSelect.innerHTML = '<option value="">Tidak ada meja tersedia</option>';
                message.textContent = 'Tidak ada meja yang tersedia pada tanggal dan jam ini.';
                return;
            }

            tableSelect.innerHTML = '<option value="">-- Pilih Meja --</option>';
            data.tables.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = `Meja ${t.table_number} — Kapasitas ${t.capacity} Orang`;
                tableSelect.appendChild(opt);
            });
            tableSelect.disabled = false;
            message.textContent = `Tersedia ${data.tables.length} pilihan meja untuk Anda.`;
        } catch (e) {
            if (reqId === activeReq) {
                tableSelect.innerHTML = '<option value="">Gagal memeriksa meja</option>';
                message.textContent = 'Terjadi gangguan koneksi. Silakan coba lagi.';
            }
        }
    }

    [dateInput, timeInput, guestsInput].forEach(inp => {
        inp.addEventListener('change', refreshAvailability);
    });
    if (dateInput.value && timeInput.value && guestsInput.value) {
        refreshAvailability();
    }
})();
</script>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
