<?php
$pageTitle = $pageTitle ?? 'Our Menu';
$menus = $menus ?? [];
$categories = $categories ?? [];
$selectedCategory = $selectedCategory ?? null;
$search = $search ?? '';
$filterError = $filterError ?? null;
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
        <p class="text-uppercase small text-muted mb-1">Something for every craving</p>
        <h1 class="section-title display-6">Our Menu</h1>
    </div>
    <?php if ($filterError): ?><div class="alert alert-warning" role="alert"><?= $escape($filterError) ?></div><?php endif; ?>
    <form class="cozy-card p-3 mb-4" method="get" action="<?= $escape($baseUrl) ?>/menu">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label" for="search">Search menu</label>
                <input class="form-control" id="search" name="search" type="search" maxlength="100" value="<?= $escape($search) ?>" placeholder="Coffee, dessert...">
            </div>
            <div class="col-md-5">
                <label class="form-label" for="category">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (int) $selectedCategory === (int) $category['id'] ? 'selected' : '' ?>><?= $escape($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid"><button class="btn btn-coffee" type="submit">Filter</button></div>
        </div>
    </form>
    <div class="row g-4">
        <?php foreach ($menus as $menu): ?>
            <div class="col-sm-6 col-lg-4">
                <article class="cozy-card h-100">
                    <?php if (!empty($menu['image'])): ?>
                        <img src="<?= $escape($baseUrl) ?>/uploads/<?= rawurlencode(basename($menu['image'])) ?>" class="card-img-top" alt="<?= $escape($menu['name']) ?>" style="height: 220px; object-fit: cover;">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center bg-light text-secondary" style="height: 160px;"><i class="fa-solid fa-mug-hot fa-3x"></i></div>
                    <?php endif; ?>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="small text-muted"><?= $escape($menu['category_name']) ?></span>
                            <?php if (!empty($menu['promotion_name'])): ?><span class="badge rounded-pill text-bg-danger">Promo</span><?php endif; ?>
                        </div>
                        <h2 class="h5 mt-2"><?= $escape($menu['name']) ?></h2>
                        <?php if (!empty($menu['promotion_name'])): ?>
                            <div class="small text-danger mb-2">
                                <?= $escape($menu['promotion_name']) ?> —
                                <?= $menu['discount_type'] === 'percentage' ? $escape(rtrim(rtrim(number_format((float) $menu['discount_value'], 2, '.', ''), '0'), '.')) . '%' : 'Rp ' . number_format((float) $menu['discount_value'], 0, ',', '.') . ' off' ?>
                            </div>
                        <?php endif; ?>
                        <p class="text-muted small"><?= $escape($menu['description'] ?? '') ?></p>
                        <div class="fw-semibold mb-3">Rp <?= number_format((float) $menu['price'], 2, ',', '.') ?></div>
                        <a class="btn btn-outline-coffee btn-sm" href="<?= $escape($baseUrl) ?>/menu/detail?id=<?= (int) $menu['id'] ?>">Details</a>
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
        <?php if (!$menus): ?><div class="col-12"><div class="cozy-card p-4 text-center text-muted">No menu items match your filters.</div></div><?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
