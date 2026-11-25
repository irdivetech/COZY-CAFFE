<?php
$pageTitle = $pageTitle ?? 'Menu Details';
$menu = $menu ?? [];
$currentUser = $currentUser ?? null;
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
ob_start();
?>
<section class="container py-5" style="max-width: 1000px;">
    <a class="small" href="<?= $escape($baseUrl) ?>/menu"><i class="fa-solid fa-arrow-left me-1"></i>Back to menu</a>
    <article class="cozy-card mt-3">
        <div class="row g-0">
            <div class="col-lg-6">
                <?php if (!empty($menu['image'])): ?>
                    <img src="<?= $escape($baseUrl) ?>/uploads/<?= rawurlencode(basename($menu['image'])) ?>" class="w-100 h-100 rounded-start object-fit-cover" alt="<?= $escape($menu['name']) ?>" style="min-height: 320px; max-height: 520px;">
                <?php else: ?>
                    <div class="h-100 d-flex align-items-center justify-content-center bg-light text-secondary" style="min-height: 320px;"><i class="fa-solid fa-mug-hot fa-5x"></i></div>
                <?php endif; ?>
            </div>
            <div class="col-lg-6">
                <div class="p-4 p-lg-5">
                    <span class="small text-muted"><?= $escape($menu['category_name']) ?></span>
                    <h1 class="section-title display-6 mt-2"><?= $escape($menu['name']) ?></h1>
                    <?php if (!empty($menu['promotion_name'])): ?>
                        <div class="alert alert-warning py-2">
                            <strong><?= $escape($menu['promotion_name']) ?></strong>
                            <div class="small">
                                <?= $menu['discount_type'] === 'percentage' ? $escape(rtrim(rtrim(number_format((float) $menu['discount_value'], 2, '.', ''), '0'), '.')) . '% discount' : 'Rp ' . number_format((float) $menu['discount_value'], 0, ',', '.') . ' discount' ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <p class="text-muted"><?= nl2br($escape($menu['description'] ?? '')) ?></p>
                    <p class="fs-4 fw-semibold mb-4">Rp <?= number_format((float) $menu['price'], 2, ',', '.') ?></p>
                    <?php if (($currentUser['role'] ?? '') === 'customer'): ?>
                        <form method="post" action="<?= $escape($baseUrl) ?>/cart/add">
                            <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                            <input type="hidden" name="menu_id" value="<?= (int) $menu['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label" for="quantity">Quantity</label>
                                <input class="form-control" id="quantity" name="quantity" type="number" min="1" max="99" value="1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="notes">Item notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="255" placeholder="Optional notes"></textarea>
                            </div>
                            <button class="btn btn-coffee" type="submit"><i class="fa-solid fa-bag-shopping me-2"></i>Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <a class="btn btn-coffee" href="<?= $escape($baseUrl) ?>/login">Login to order</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </article>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
