<?php

/*
 * ==========================================================
 * PAY.PHP
 * ==========================================================
 *
 * Payment page
 *
 */

if (!file_exists('config.php')) die();
require('functions.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <title>
        <?php bxc_e(bxc_settings_get('form-title', 'Payment method')) ?>
    </title>
    <link rel="shortcut icon" type="image/svg" href="<?php echo BXC_URL ?>media/icon.svg" />
    <script id="boxcoin" src="<?php echo BXC_URL ?>js/client.js"></script>
    <style>
        body {
            text-align: center;
            padding: 100px 0;
        }

        .bxc-main {
            text-align: left;
            margin: auto;
        }
    </style>
</head>
<body style="display:none">
    <script>(function () { setTimeout(() => { document.body.style.removeProperty('display') }, 500) }())</script>
    <?php bxc_checkout_direct() ?>
</body>
</html>