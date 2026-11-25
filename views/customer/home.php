<?php
$pageTitle = $pageTitle ?? 'CozyCafe - A Cozy Place, A Better Taste';
$featuredMenus = $featuredMenus ?? [];
$currentUser = $currentUser ?? null;
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
ob_start();
?>
<section class="container py-4 py-lg-5">
    <div class="hero-banner p-4 p-md-5 mb-5 text-white">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="text-uppercase small fw-semibold tracking-wide">Selamat Datang di CozyCafe</span>
                <h1 class="display-5 fw-bold mt-2">Tempat nyaman untuk setiap momen Anda.</h1>
                <p class="lead mb-4">Kopi pilihan yang nikmat, hidangan lezat, dan meja hangat selalu siap menyambut Anda.</p>
                <a class="btn btn-light btn-lg me-2 mb-2" href="<?= $escape($baseUrl) ?>/menu"><i class="fa-solid fa-utensils me-2"></i>Jelajahi Menu</a>
                <a class="btn btn-outline-light btn-lg mb-2" href="<?= $escape($baseUrl) ?>/reservation"><i class="fa-solid fa-chair me-2"></i>Reservasi Meja</a>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="display-1 text-white opacity-75" aria-hidden="true"><i class="fa-solid fa-mug-hot"></i></div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-muted mb-1">Dibuat Sepenuh Hati</p>
            <h2 class="section-title h3 mb-0">Menu Pilihan & Promo Spesial</h2>
        </div>
        <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/menu">Lihat Semua Menu <i class="fa-solid fa-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-4">
        <?php foreach ($featuredMenus as $menu): ?>
            <div class="col-sm-6 col-lg-4">
                <article class="cozy-card h-100 d-flex flex-column">
                    <?php if (!empty($menu['image'])): ?>
                        <img src="<?= $escape($baseUrl) ?>/uploads/<?= rawurlencode(basename($menu['image'])) ?>" class="card-img-top" alt="<?= $escape($menu['name']) ?>" style="height: 220px; object-fit: cover;">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center bg-light text-secondary" style="height: 180px;"><i class="fa-solid fa-mug-hot fa-3x"></i></div>
                    <?php endif; ?>
                    <div class="card-body p-4 d-flex flex-column flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <span class="badge bg-light text-dark border"><?= $escape($menu['category_name']) ?></span>
                                <h3 class="h5 mt-2 mb-0"><?= $escape($menu['name']) ?></h3>
                            </div>
                            <?php if (!empty($menu['promotion_name'])): ?>
                                <span class="badge rounded-pill text-bg-danger">Promo</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted small flex-grow-1"><?= $escape($menu['description'] ?? '') ?></p>
                        <div class="fw-semibold text-coffee fs-5 mb-3">Rp <?= number_format((float) $menu['price'], 2, ',', '.') ?></div>
                        <div class="d-flex gap-2">
                            <a class="btn btn-outline-coffee btn-sm flex-grow-1" href="<?= $escape($baseUrl) ?>/menu/detail?id=<?= (int) $menu['id'] ?>">Lihat Detail</a>
                            <?php if (($currentUser['role'] ?? '') === 'customer'): ?>
                                <form class="d-inline" method="post" action="<?= $escape($baseUrl) ?>/cart/add">
                                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                    <input type="hidden" name="menu_id" value="<?= (int) $menu['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button class="btn btn-coffee btn-sm" type="submit" title="Tambah ke Keranjang"><i class="fa-solid fa-cart-plus"></i></button>
                                </form>
                            <?php elseif (!$currentUser): ?>
                                <a class="btn btn-coffee btn-sm" href="<?= $escape($baseUrl) ?>/login" title="Masuk untuk Memesan"><i class="fa-solid fa-cart-plus"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
        <?php if (!$featuredMenus): ?>
            <div class="col-12"><div class="cozy-card p-5 text-center text-muted">Daftar menu sedang disiapkan. Silakan kunjungi kembali dalam waktu dekat.</div></div>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
