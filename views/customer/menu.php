<?php
$pageTitle = $pageTitle ?? 'Daftar Menu';
$menus = $menus ?? [];
$categories = $categories ?? [];
$selectedCategory = $selectedCategory ?? null;
$searchTerm = $searchTerm ?? '';
$currentUser = $currentUser ?? null;
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
ob_start();
?>
<section class="container py-5">
    <div class="mb-4">
        <p class="text-uppercase small text-muted mb-1">Pilihan Terbaik</p>
        <h1 class="section-title h2">Katalog Menu CozyCafe</h1>
        <p class="text-muted">Kopi pilihan diseduh segar, hidangan lezat, dan kudapan istimewa untuk menemani hari Anda.</p>
    </div>

    <form method="get" action="<?= $escape($baseUrl) ?>/menu" class="cozy-card p-3 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label" for="menuSearch">Cari Menu</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input class="form-control" id="menuSearch" name="search" type="search" placeholder="Cari nama menu atau minuman..." value="<?= $escape($searchTerm) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="categorySelect">Kategori</label>
                <select class="form-select" id="categorySelect" name="category">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= $selectedCategory === (int) $cat['id'] ? 'selected' : '' ?>>
                            <?= $escape($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-coffee w-100" type="submit">Filter</button>
                <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/menu">Reset</a>
            </div>
        </div>
    </form>

    <div class="row g-4">
        <?php foreach ($menus as $menu): ?>
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
                                <h2 class="h5 mt-2 mb-0"><?= $escape($menu['name']) ?></h2>
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
                                    <button class="btn btn-coffee btn-sm" type="submit" title="Tambah ke Keranjang"><i class="fa-solid fa-cart-plus me-1"></i>Pesan</button>
                                </form>
                            <?php elseif (!$currentUser): ?>
                                <a class="btn btn-coffee btn-sm" href="<?= $escape($baseUrl) ?>/login" title="Masuk untuk memesan"><i class="fa-solid fa-cart-plus me-1"></i>Pesan</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
        <?php if (!$menus): ?>
            <div class="col-12"><div class="cozy-card p-5 text-center text-muted">Tidak ada menu yang sesuai dengan filter atau pencarian Anda.</div></div>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
