<?php
$pageTitle = $pageTitle ?? 'Checkout Pesanan';
$cart = $cart ?? [];
$activeBookings = $activeBookings ?? [];
$operatingHours = $operatingHours ?? [];
$errors = $errors ?? [];
$orderType = $orderType ?? 'Takeaway';
$bookingMode = $bookingMode ?? 'existing';
$selectedBookingId = $selectedBookingId ?? null;
$reservationDate = $reservationDate ?? date('Y-m-d');
$reservationTime = $reservationTime ?? '10:00';
$guestCount = $guestCount ?? 2;
$selectedTableId = $selectedTableId ?? null;
$reservationNotes = $reservationNotes ?? '';
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$money = static fn (int $cents): string => number_format($cents / 100, 2, ',', '.');
ob_start();
?>
<section class="container py-5" style="max-width: 980px;">
    <div class="mb-4">
        <p class="text-uppercase small text-muted mb-1">Langkah Terakhir</p>
        <h1 class="section-title h2">Checkout & Konfirmasi Pesanan</h1>
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

    <form method="post" action="<?= $escape($baseUrl) ?>/checkout" id="checkoutForm">
        <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">

        <section class="cozy-card p-4 mb-4">
            <h2 class="h5 mb-3">1. Pilih Jenis Pemesanan</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="p-3 border rounded-3 w-100 d-block cursor-pointer option-card <?= $orderType === 'Takeaway' ? 'border-coffee bg-light' : '' ?>">
                        <input class="form-check-input me-2" type="radio" name="order_type" value="Takeaway" <?= $orderType === 'Takeaway' ? 'checked' : '' ?> id="typeTakeaway">
                        <strong>Bawa Pulang (Takeaway)</strong>
                        <p class="text-muted small mb-0 mt-1">Pesanan dikemas rapi untuk diambil di cafe dan dinikmati di mana saja.</p>
                    </label>
                </div>
                <div class="col-md-6">
                    <label class="p-3 border rounded-3 w-100 d-block cursor-pointer option-card <?= $orderType === 'Dine-in' ? 'border-coffee bg-light' : '' ?>">
                        <input class="form-check-input me-2" type="radio" name="order_type" value="Dine-in" <?= $orderType === 'Dine-in' ? 'checked' : '' ?> id="typeDineIn">
                        <strong>Makan di Tempat (Dine-in)</strong>
                        <p class="text-muted small mb-0 mt-1">Nikmati hidangan langsung di cafe dengan reservasi meja terkonfirmasi.</p>
                    </label>
                </div>
            </div>
        </section>

        <section class="cozy-card p-4 mb-4" id="dineInCard" style="<?= $orderType === 'Dine-in' ? '' : 'display: none;' ?>">
            <h2 class="h5 mb-3">2. Reservasi Meja (Wajib untuk Dine-in)</h2>
            <div class="mb-3">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="booking_mode" id="modeExisting" value="existing" <?= $bookingMode === 'existing' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="modeExisting">Gunakan Reservasi Aktif Saya</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="booking_mode" id="modeNew" value="new" <?= $bookingMode === 'new' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="modeNew">Buat Reservasi Meja Baru Sekarang</label>
                </div>
            </div>

            <div id="existingBookingSection" style="<?= $bookingMode === 'existing' ? '' : 'display: none;' ?>">
                <label class="form-label" for="booking_id">Pilih Reservasi Aktif</label>
                <select class="form-select" id="booking_id" name="booking_id">
                    <option value="">-- Pilih Jadwal Reservasi Meja --</option>
                    <?php foreach ($activeBookings as $booking): ?>
                        <option value="<?= (int) $booking['id'] ?>" <?= $selectedBookingId === (int) $booking['id'] ? 'selected' : '' ?>>
                            <?= $escape($booking['booking_date']) ?> · Meja <?= $escape($booking['table_number']) ?> (<?= (int) $booking['guest_count'] ?> orang) · <?= $escape(substr($booking['start_time'], 0, 5)) ?>-<?= $escape(substr($booking['end_time'], 0, 5)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!$activeBookings): ?>
                    <div class="small text-muted mt-2">Anda belum memiliki reservasi aktif. Silakan pilih <strong>Buat Reservasi Meja Baru Sekarang</strong> di atas.</div>
                <?php endif; ?>
            </div>

            <div id="newBookingSection" style="<?= $bookingMode === 'new' ? '' : 'display: none;' ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="resDate">Tanggal Reservasi</label>
                        <input class="form-control" id="resDate" name="reservation_date" type="date" min="<?= date('Y-m-d') ?>" value="<?= $escape($reservationDate) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="resTime">Jam Mulai (Durasi 2 Jam)</label>
                        <input class="form-control" id="resTime" name="reservation_time" type="time" value="<?= $escape($reservationTime) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="resGuests">Jumlah Tamu</label>
                        <input class="form-control" id="resGuests" name="guest_count" type="number" min="1" max="20" value="<?= (int) $guestCount ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="resTable">Pilih Meja yang Tersedia</label>
                        <select class="form-select" id="resTable" name="table_id">
                            <option value="">-- Memeriksa Ketersediaan Meja --</option>
                        </select>
                        <div class="small text-muted mt-1" id="resTableMessage">Ketersediaan meja akan diperbarui otomatis.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="resNotes">Catatan Meja (opsional)</label>
                        <input class="form-control" id="resNotes" name="reservation_notes" maxlength="255" placeholder="Contoh: Meja dekat jendela, perayaan ulang tahun..." value="<?= $escape($reservationNotes) ?>">
                    </div>
                </div>
            </div>
        </section>

        <section class="cozy-card p-4 mb-4">
            <h2 class="h5 mb-3">3. Metode Pembayaran</h2>
            <div class="border rounded-3 p-3 bg-light">
                <div class="fw-semibold"><i class="fa-solid fa-money-bill-wave me-2 text-success"></i>Tunai di Kasir (Cash)</div>
                <div class="small text-muted mt-1">Pembayaran dilakukan secara tunai di kasir cafe saat Anda mengambil pesanan atau saat makan di tempat.</div>
            </div>
        </section>

        <section class="cozy-card p-4 mb-4">
            <h2 class="h5 mb-3">4. Ringkasan Pesanan</h2>
            <div class="table-responsive mb-3">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Menu</th><th class="text-center">Jumlah</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>
                    <?php foreach ($cart['items'] as $item): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= $escape($item['menu_name']) ?></div>
                                <?php if ($item['notes']): ?>
                                    <small class="text-muted"><i class="fa-solid fa-pen-nib me-1"></i><?= $escape($item['notes']) ?></small><br>
                                <?php endif; ?>
                                <?php if ($item['line_discount_cents'] > 0): ?>
                                    <small class="text-success">Diskon: −Rp <?= $money($item['line_discount_cents']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= (int) $item['quantity'] ?></td>
                            <td class="text-end text-nowrap fw-semibold">Rp <?= $money($item['line_total_cents']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="border-top pt-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span>Rp <?= $money($cart['subtotal_cents']) ?></span>
                </div>
                <div class="d-flex justify-content-between text-success mb-2">
                    <span>Total Diskon</span>
                    <span>−Rp <?= $money($cart['discount_cents']) ?></span>
                </div>
                <div class="d-flex justify-content-between fs-4 fw-bold text-coffee mb-4">
                    <span>Total Akhir</span>
                    <span>Rp <?= $money($cart['total_cents']) ?></span>
                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/cart"><i class="fa-solid fa-arrow-left me-1"></i>Kembali ke Keranjang</a>
                    <button class="btn btn-coffee btn-lg" type="submit"><i class="fa-solid fa-check me-2"></i>Konfirmasi & Buat Pesanan</button>
                </div>
            </div>
        </section>
    </form>
</section>

<script>
(() => {
    const typeTakeaway = document.getElementById('typeTakeaway');
    const typeDineIn = document.getElementById('typeDineIn');
    const dineInCard = document.getElementById('dineInCard');
    const modeExisting = document.getElementById('modeExisting');
    const modeNew = document.getElementById('modeNew');
    const existingSection = document.getElementById('existingBookingSection');
    const newSection = document.getElementById('newBookingSection');

    function syncOrderType() {
        if (typeDineIn && typeDineIn.checked) {
            dineInCard.style.display = '';
        } else {
            dineInCard.style.display = 'none';
        }
    }

    function syncBookingMode() {
        if (modeNew && modeNew.checked) {
            newSection.style.display = '';
            existingSection.style.display = 'none';
            refreshAvailability();
        } else {
            newSection.style.display = 'none';
            existingSection.style.display = '';
        }
    }

    if (typeTakeaway && typeDineIn) {
        typeTakeaway.addEventListener('change', syncOrderType);
        typeDineIn.addEventListener('change', syncOrderType);
    }
    if (modeExisting && modeNew) {
        modeExisting.addEventListener('change', syncBookingMode);
        modeNew.addEventListener('change', syncBookingMode);
    }

    const dateInput = document.getElementById('resDate');
    const timeInput = document.getElementById('resTime');
    const guestsInput = document.getElementById('resGuests');
    const tableSelect = document.getElementById('resTable');
    const message = document.getElementById('resTableMessage');
    const baseUrl = <?= json_encode($baseUrl) ?>;
    let currentReq = 0;

    async function refreshAvailability() {
        if (!dateInput || !timeInput || !guestsInput || !tableSelect) return;
        const date = dateInput.value;
        const time = timeInput.value;
        const guests = guestsInput.value;
        if (!date || !time || !guests) {
            tableSelect.innerHTML = '<option value="">-- Lengkapi Tanggal, Jam, dan Tamu --</option>';
            return;
        }

        const reqId = ++currentReq;
        tableSelect.disabled = true;
        message.textContent = 'Memeriksa ketersediaan meja...';

        try {
            const url = `${baseUrl}/reservation/availability?date=${encodeURIComponent(date)}&time=${encodeURIComponent(time)}&guest_count=${encodeURIComponent(guests)}`;
            const res = await fetch(url);
            const data = await res.json();
            if (reqId !== currentReq) return;

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

            tableSelect.innerHTML = '<option value="">-- Pilih Meja yang Tersedia --</option>';
            data.tables.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = `Meja ${t.table_number} — Kapasitas ${t.capacity} Orang`;
                tableSelect.appendChild(opt);
            });
            tableSelect.disabled = false;
            message.textContent = `Tersedia ${data.tables.length} pilihan meja.`;
        } catch (e) {
            if (reqId === currentReq) {
                tableSelect.innerHTML = '<option value="">Gagal memeriksa meja</option>';
                message.textContent = 'Terjadi kesalahan jaringan. Coba lagi.';
            }
        }
    }

    if (dateInput && timeInput && guestsInput) {
        dateInput.addEventListener('change', refreshAvailability);
        timeInput.addEventListener('change', refreshAvailability);
        guestsInput.addEventListener('change', refreshAvailability);
    }
})();
</script>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
