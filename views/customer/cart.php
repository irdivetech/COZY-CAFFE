<?php
$pageTitle = $pageTitle ?? 'Keranjang Belanja';
$cart = $cart ?? [
    'items' => [],
    'item_count' => 0,
    'subtotal_cents' => 0,
    'discount_cents' => 0,
    'total_cents' => 0,
    'has_unavailable_items' => false,
];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$money = static fn (int $cents): string => number_format($cents / 100, 2, ',', '.');
ob_start();
?>
<section class="container py-5">
    <div class="mb-4">
        <p class="text-uppercase small text-muted mb-1">Periksa Pesanan Anda</p>
        <h1 class="section-title h2">Keranjang Belanja</h1>
    </div>
    <?php if (!$cart['items']): ?>
        <div class="cozy-card p-5 text-center">
            <i class="fa-solid fa-bag-shopping fa-3x text-muted mb-3"></i>
            <h2 class="h4">Keranjang Belanja Anda Kosong</h2>
            <p class="text-muted">Temukan aneka minuman dan makanan favorit di menu kami.</p>
            <a class="btn btn-coffee mt-2" href="<?= $escape($baseUrl) ?>/menu"><i class="fa-solid fa-utensils me-2"></i>Lihat Menu</a>
        </div>
    <?php else: ?>
        <?php if ($cart['has_unavailable_items']): ?>
            <div class="alert alert-warning" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>Beberapa item tidak tersedia saat ini. Silakan hapus item tersebut sebelum melanjutkan ke checkout.
            </div>
        <?php endif; ?>
        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($cart['items'] as $item): ?>
                        <article class="cozy-card p-3 p-md-4">
                            <div class="row g-3 align-items-start">
                                <div class="col-auto">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="<?= $escape($baseUrl) ?>/uploads/<?= rawurlencode(basename($item['image'])) ?>" alt="" width="90" height="90" class="rounded object-fit-cover">
                                    <?php else: ?>
                                        <div class="rounded bg-light d-flex align-items-center justify-content-center text-secondary" style="width: 90px; height: 90px;"><i class="fa-solid fa-mug-hot fa-2x"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col">
                                    <div class="d-flex flex-wrap justify-content-between gap-2">
                                        <div>
                                            <h2 class="h5 mb-1"><?= $escape($item['menu_name']) ?></h2>
                                            <?php if (!$item['is_available']): ?>
                                                <span class="badge text-bg-danger">Tidak Tersedia</span>
                                            <?php endif; ?>
                                            <?php if ($item['promotion_name']): ?>
                                                <div class="small text-success mt-1"><i class="fa-solid fa-tag me-1"></i>Promo: <?= $escape($item['promotion_name']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-coffee">Rp <?= $money($item['line_total_cents']) ?></div>
                                            <?php if ($item['line_discount_cents'] > 0): ?>
                                                <small class="text-muted text-decoration-line-through">Rp <?= $money($item['line_subtotal_cents']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($item['line_discount_cents'] > 0): ?>
                                        <div class="small text-success">Hemat: −Rp <?= $money($item['line_discount_cents']) ?></div>
                                    <?php endif; ?>
                                    <div class="small text-muted mb-2">Rp <?= $money($item['unit_price_cents']) ?> / porsi</div>

                                    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mt-3">
                                        <form method="post" action="<?= $escape($baseUrl) ?>/cart/update" class="d-flex flex-wrap align-items-end gap-2">
                                            <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                            <input type="hidden" name="cart_item_id" value="<?= (int) $item['cart_item_id'] ?>">
                                            <div>
                                                <label class="form-label small text-muted mb-1" for="notes-<?= (int) $item['cart_item_id'] ?>">Catatan</label>
                                                <input class="form-control form-control-sm" id="notes-<?= (int) $item['cart_item_id'] ?>" name="notes" maxlength="255" value="<?= $escape($item['notes'] ?? '') ?>" placeholder="Catatan item...">
                                            </div>
                                            <div>
                                                <label class="form-label small text-muted mb-1" for="qty-<?= (int) $item['cart_item_id'] ?>">Jumlah</label>
                                                <input class="form-control form-control-sm" id="qty-<?= (int) $item['cart_item_id'] ?>" name="quantity" type="number" min="1" max="99" value="<?= (int) $item['quantity'] ?>" style="width: 75px;">
                                            </div>
                                            <button class="btn btn-outline-coffee btn-sm" type="submit">Simpan</button>
                                        </form>

                                        <form method="post" action="<?= $escape($baseUrl) ?>/cart/remove">
                                            <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                            <input type="hidden" name="cart_item_id" value="<?= (int) $item['cart_item_id'] ?>">
                                            <button class="btn btn-outline-danger btn-sm" type="submit" onclick="return confirm('Hapus menu ini dari keranjang?');"><i class="fa-solid fa-trash me-1"></i>Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <aside class="cozy-card p-4">
                    <h2 class="h5 mb-3">Ringkasan Pesanan</h2>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total item</span>
                        <span><?= (int) $cart['item_count'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span>Rp <?= $money($cart['subtotal_cents']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-success mb-3">
                        <span>Total Diskon</span>
                        <span>−Rp <?= $money($cart['discount_cents']) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
                        <span>Total Pembayaran</span>
                        <span class="text-coffee">Rp <?= $money($cart['total_cents']) ?></span>
                    </div>
                    <p class="small text-muted mb-3"><i class="fa-solid fa-circle-info me-1"></i>Pembayaran tunai (Cash) diselesaikan langsung di kasir saat kedatangan atau pengambilan pesanan.</p>
                    <a class="btn btn-coffee w-100 <?= $cart['has_unavailable_items'] ? 'disabled' : '' ?>" href="<?= $escape($baseUrl) ?>/checkout">
                        <i class="fa-solid fa-arrow-right me-2"></i>Lanjut ke Checkout
                    </a>
                    <?php if ($cart['has_unavailable_items']): ?>
                        <div class="small text-danger text-center mt-2">Hapus item tidak tersedia sebelum checkout.</div>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
