<?php
$pageTitle = $pageTitle ?? 'CozyCafe';
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
    <div class="hero-banner p-4 p-md-5 mb-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="text-uppercase small fw-semibold">Welcome to CozyCafe</span>
                <h1 class="display-5 fw-bold mt-2">A cozy place for your everyday moments.</h1>
                <p class="lead mb-4">Coffee, comfort food, and a table waiting for you.</p>
                <a class="btn btn-light btn-lg me-2 mb-2" href="<?= $escape($baseUrl) ?>/menu">Explore Menu</a>
                <a class="btn btn-outline-light btn-lg mb-2" href="<?= $escape($baseUrl) ?>/reservation">Reserve a Table</a>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="display-1" aria-hidden="true"><i class="fa-solid fa-mug-hot"></i></div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
        <div>
            <p class="text-uppercase small text-muted mb-1">Made with care</p>
            <h2 class="section-title h3 mb-0">Featured Menu & Promotions</h2>
        </div>
        <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/menu">View full menu</a>
    </div>
    <div class="row g-4">
        <?php foreach ($featuredMenus as $menu): ?>
            <div class="col-sm-6 col-lg-4">
                <article class="cozy-card h-100">
                    <?php if (!empty($menu['image'])): ?>
                        <img src="<?= $escape($baseUrl) ?>/uploads/<?= rawurlencode(basename($menu['image'])) ?>" class="card-img-top" alt="<?= $escape($menu['name']) ?>" style="height: 220px; object-fit: cover;">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center bg-light text-secondary" style="height: 160px;"><i class="fa-solid fa-mug-hot fa-3x"></i></div>
                    <?php endif; ?>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <span class="small text-muted"><?= $escape($menu['category_name']) ?></span>
                                <h3 class="h5 mt-1"><?= $escape($menu['name']) ?></h3>
                            </div>
                            <?php if (!empty($menu['promotion_name'])): ?>
                                <span class="badge rounded-pill text-bg-danger">Promo</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted small"><?= $escape($menu['description'] ?? '') ?></p>
                        <div class="fw-semibold text-coffee mb-3">Rp <?= number_format((float) $menu['price'], 2, ',', '.') ?></div>
                        <a class="btn btn-outline-coffee btn-sm" href="<?= $escape($baseUrl) ?>/menu/detail?id=<?= (int) $menu['id'] ?>">View details</a>
                        <?php if (($currentUser['role'] ?? '') === 'customer'): ?>
                            <form class="d-inline" method="post" action="<?= $escape($baseUrl) ?>/cart/add">
                                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                <input type="hidden" name="menu_id" value="<?= (int) $menu['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button class="btn btn-coffee btn-sm" type="submit">Add to Cart</button>
                            </form>
                        <?php elseif (!$currentUser): ?>
                            <a class="btn btn-coffee btn-sm" href="<?= $escape($baseUrl) ?>/login">Login to Add to Cart</a>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
        <?php if (!$featuredMenus): ?>
            <div class="col-12"><div class="cozy-card p-4 text-center text-muted">Our menu is being prepared. Please check back soon.</div></div>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
