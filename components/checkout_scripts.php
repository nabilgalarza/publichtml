<?php
require_once __DIR__ . '/../lib/checkout_helpers.php';
$improgyp_checkout_js = improgyp_checkout_js_config($base_url ?? '');
$improgyp_asset_v = (string) time();
?>
<script>window.IMPROGYP_CHECKOUT = <?= json_encode($improgyp_checkout_js, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="js/cart_checkout.js?v=<?= htmlspecialchars($improgyp_asset_v, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="js/checkout_wa.js?v=<?= htmlspecialchars($improgyp_asset_v, ENT_QUOTES, 'UTF-8') ?>"></script>
