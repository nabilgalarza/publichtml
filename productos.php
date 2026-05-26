<?php
$improgyp_page = 'tienda';
require_once __DIR__ . '/core_init.php';
include __DIR__ . '/components/head_store.php';
?>
<body class="antialiased">
<?php include __DIR__ . '/components/header.php'; ?>
<?php include __DIR__ . '/components/tienda_body.php'; ?>
<?php include __DIR__ . '/components/footer.php'; ?>
<script src="js/omnibar.js?v=<?= time() ?>"></script>
<script src="js/header_actions.js?v=<?= time() ?>"></script>
<?php include __DIR__ . '/components/tienda_scripts.php'; ?>
<script src="js/cart_checkout.js?v=<?= time() ?>"></script>
<script src="js/checkout_wa.js?v=<?= time() ?>"></script>
</body>
</html>
