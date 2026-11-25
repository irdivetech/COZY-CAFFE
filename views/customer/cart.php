<?php
$pageTitle = $pageTitle ?? 'My Cart';
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
        <p class="text-uppercase small text-muted mb-1">Review your order</p>
        <h1 class="section-title h2">My Cart</h1>
    </div>
    <?php if (!$cart['items']): ?>
        <div class="cozy-card p-5 text-center">
            <i class="fa-solid fa-bag-shopping fa-3x text-muted mb-3"></i>
            <h2 class="h4">Your cart is empty</h2>
            <p class="text-muted">Find something cozy from our menu.</p>
            <a class="btn btn-coffee" href="<?= $escape($baseUrl) ?>/menu">Browse Menu</a>
        </div>
    <?php else: ?>
        <?php if ($cart['has_unavailable_items']): ?>
            <div class="alert alert-warning" role="alert">Some items are unavailable. Remove them before proceeding.</div>
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
                                            <?php if (!$item['is_available']): ?><span class="badge-status badge-cancelled">Unavailable</span><?php endif; ?>
                                            <?php if ($item['promotion_name']): ?><div class="small text-success mt-1"><?= $escape($item['promotion_name']) ?> applied</div><?php endif; ?>
                                        </div>
                                        <div class="text-end">
                                            <div>Rp <?= $money($item['line_total_cents']) ?></div>
                                            <?php if ($item['line_discount_cents'] > 0): ?>
                                                <small class="text-muted text-decoration-line-through">Rp <?= $money($item['line_subtotal_cents']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($item['line_discount_cents'] > 0): ?>
                                        <div class="small text-success">Discount: −Rp <?= $money($item['line_discount_cents']) ?></div>
                                    <?php endif; ?>
                                    <div class="small text-muted mb-2">Rp <?= $money($item['unit_price_cents']) ?> each</div>

                                    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
                                        <form method="post" action="<?= $escape($baseUrl) ?>/cart/update" class="d-flex flex-wrap align-items-end gap-2">
                                            <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                            <input type="hidden" name="cart_item_id" value="<?= (int) $item['cart_item_id'] ?>">
                                            <div>
                                                <label class="form-label small mb-1" for="quantity-<?= (int) $item['cart_item_id'] ?>">Qty</label>
                                                <input class="form-control form-control-sm" id="quantity-<?= (int) $item['cart_item_id'] ?>" name="quantity" type="number" min="1" max="99" value="<?= (int) $item['quantity'] ?>" required style="width: 84px;">
                                            </div>
                                            <div>
                                                <label class="form-label small mb-1" for="notes-<?= (int) $item['cart_item_id'] ?>">Notes</label>
                                                <input class="form-control form-control-sm" id="notes-<?= (int) $item['cart_item_id'] ?>" name="notes" maxlength="255" value="<?= $escape($item['notes'] ?? '') ?>" style="width: min(230px, 50vw);">
                                            </div>
                                            <button class="btn btn-sm btn-outline-coffee" type="submit">Update</button>
                                        </form>
                                        <div class="d-flex align-items-center gap-2">
                                            <form method="post" action="<?= $escape($baseUrl) ?>/cart/change">
                                                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                                <input type="hidden" name="cart_item_id" value="<?= (int) $item['cart_item_id'] ?>">
                                                <input type="hidden" name="delta" value="-1">
                                                <button class="btn btn-sm btn-outline-coffee" type="submit" aria-label="Decrease quantity">−</button>
                                            </form>
                                            <span class="fw-semibold"><?= (int) $item['quantity'] ?></span>
                                            <form method="post" action="<?= $escape($baseUrl) ?>/cart/change">
                                                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                                <input type="hidden" name="cart_item_id" value="<?= (int) $item['cart_item_id'] ?>">
                                                <input type="hidden" name="delta" value="1">
                                                <button class="btn btn-sm btn-outline-coffee" type="submit" aria-label="Increase quantity">+</button>
                                            </form>
                                            <form method="post" action="<?= $escape($baseUrl) ?>/cart/remove" onsubmit="return confirm('Remove this item from your cart?');">
                                                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                                <input type="hidden" name="cart_item_id" value="<?= (int) $item['cart_item_id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <aside class="cozy-card p-4 position-sticky" style="top: 100px;">
                    <h2 class="h5 mb-4">Order Summary</h2>
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>Rp <?= $money($cart['subtotal_cents']) ?></span></div>
                    <div class="d-flex justify-content-between mb-3 text-success"><span>Discount</span><span>−Rp <?= $money($cart['discount_cents']) ?></span></div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5 mb-4"><span>Total</span><span>Rp <?= $money($cart['total_cents']) ?></span></div>
                    <?php if ($cart['has_unavailable_items']): ?>
                        <button class="btn btn-coffee w-100" type="button" disabled>Proceed to Checkout</button>
                    <?php else: ?>
                        <a class="btn btn-coffee w-100" href="<?= $escape($baseUrl) ?>/checkout">Proceed to Checkout</a>
                    <?php endif; ?>
                    <a class="btn btn-outline-coffee w-100 mt-2" href="<?= $escape($baseUrl) ?>/menu">Continue Shopping</a>
                </aside>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
