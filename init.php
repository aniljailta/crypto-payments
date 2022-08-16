<?php

/*
 * ==========================================================
 * INIT.PHP
 * ==========================================================
 *
 * This file loads and initilizes the payments
 *
 */

if (!file_exists('config.php')) die();
require_once(__DIR__ . '/functions.php');
if (isset($_POST['data'])) $_POST = json_decode($_POST['data'], true);
if (isset($_POST['init'])) {
    bxc_checkout_init();
}
if (isset($_POST['checkout'])) {
    bxc_checkout($_POST['checkout']);
}

function bxc_checkout_init() {
    $qr_color = bxc_settings_get('color-2');
    if ($qr_color) {
        if (strpos('#', $qr_color) !== false) {
            $qr_color = substr($qr_color, 1);
        } else {
            $qr_color = str_replace(['rgb(', ')', ',', ' '], ['', '', '-', ''], $qr_color);
        }
    } else {
        $qr_color = '23413e';
    }
    $language = bxc_language();
    $translations = $language ? file_get_contents(__DIR__ . '/resources/languages/client/' . $language . '.json') : '{}';
    $settings = ['qr_code_color' => $qr_color, 'countdown' => bxc_settings_get('refresh-interval', 60), 'confirmations' => bxc_settings_get('confirmations', 3), 'webhook' => bxc_settings_get('webhook-url'), 'redirect' => bxc_settings_get('payment-redirect')];
    echo 'var BXC_TRANSLATIONS = ' . ($translations ? $translations : '{}') . '; var BXC_URL = "' . BXC_URL . '"; var BXC_SETTINGS = ' . json_encode($settings, JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE) . ';';
}

function bxc_checkout($settings) {
    $checkout_id = $settings['checkout_id'];
    $custom = strpos($checkout_id, 'custom') !== false;
    $cryptocurrencies = [['btc', 'Bitcoin'], ['eth', 'Ethereum'], ['doge', 'Dogecoin'], ['algo', 'Algorand'], ['usdt', 'Tether ETH'], ['usdt', 'USD Coin ETH'], ['link', 'Chainlink ETH'], ['shib', 'Shiba Inu ETH'], ['bat', 'Basic Attention Token']];
    $cryptocurrencies_code = '';
    $custom_token = bxc_settings_get('custom-token-type');
    if (strpos($checkout_id, 'custom') === false) {
        $settings = bxc_checkout_get($checkout_id);
    }
    if (!$settings) die();
    if (empty($settings['currency'])) $settings['currency'] = bxc_settings_get('currency', 'USD');
    if ($custom_token) {
        $name = bxc_settings_get('custom-token-name');
        $code = bxc_settings_get('custom-token-code');
        $cryptocurrencies_code .= '<div data-custom-coin="' . $custom_token . '" data-cryptocurrency="' . $code . '" class="bxc-flex"><img src="' . bxc_settings_get('custom-token-img') . '" alt="' . $name . '" /><span>' . $name . '</span><span>' . $code . '</span></div>';
    }
    for ($i = 0; $i < count($cryptocurrencies); $i++) {
        $currency_code = $cryptocurrencies[$i][0];
        if (bxc_settings_get('address-' . $currency_code)) {
            $cryptocurrencies_code .= '<div data-cryptocurrency="' . $currency_code . '" class="bxc-flex"><img src="' . BXC_URL . '/media/icon-' . $currency_code . '.svg" alt="' . strtoupper($currency_code) . '" /><span>' . $cryptocurrencies[$i][1] . '</span><span>' . strtoupper($currency_code) . '</span></div>';
        }
    }
    $checkout_price = $settings['price'];
    if ($checkout_price == -1) $checkout_price = '';
    $checkout_type = empty($_POST['payment_page']) ? bxc_isset($settings, 'type', 'I') : 'I';
    $checkout_type = bxc_isset(['I' => 'inline', 'L' => 'link', 'P' => 'popup', 'H' => 'hidden'], $checkout_type, $checkout_type);
    echo '<!-- Boxcoin - https://boxcoin.dev -->';
    if ($checkout_type == 'popup') echo '<div class="bxc-btn bxc-btn-popup"><img src="' . BXC_URL . '/media/icon-cryptos.svg" alt="" />' . bxc_(bxc_settings_get('button-text', 'Pay now')) . '</div><div class="bxc-popup-overlay"></div>';
    $css = false;
    $color_1 = bxc_settings_get('color-1');
    $color_2 = bxc_settings_get('color-2');
    $color_3 = bxc_settings_get('color-3');
    if ($color_1) {
        $css = '.bxc-payment-methods>div:hover,.bxc-btn.bxc-btn-border:hover, .bxc-btn.bxc-btn-border:active { border-color: ' . $color_1 . '; color: ' . $color_1 . '; }';
        $css .= '.bxc-complete-cnt>i, .bxc-failed-cnt>i,.bxc-payment-methods>div:hover span+span,.bxc-clipboard:hover,.bxc-tx-cnt .bxc-loading:before,.bxc-loading:before { color: ' . $color_1 . '; }';
        $css .= '.bxc-tx-status { background-color: ' . $color_1 . '; }';
    }
    if ($color_2) {
        $css .= '.bxc-box { color: ' . $color_2 . '; }';
    }
    if ($color_3) {
        $css .= '.bxc-text,.bxc-payment-methods>div span+span { color: ' . $color_3 . '; }';
        $css .= '.bxc-btn.bxc-btn-border { border-color: ' . $color_3 . '; color: ' . $color_3 . '; }';
    }
    if ($css) echo '<style>' . $css . '</style>';
?>
<div class="bxc-main bxc-start bxc-<?php echo $checkout_type; if (bxc_is_rtl(bxc_language())) echo ' bxc-rtl'; ?>" data-currency="<?php echo $settings['currency'] ?>" data-price="<?php echo $checkout_price ?>" data-external-reference="<?php echo bxc_isset($settings, $custom ? 'externalReference' : 'external_reference', '') ?>" data-title="<?php echo str_replace('"', '', bxc_isset($settings, 'title', '')) ?>" data-description="<?php echo str_replace('"', '', bxc_isset($settings, 'description', '')) ?>">
    <?php if ($checkout_type == 'popup') echo '<i class="bxc-popup-close bxc-icon-close"></i>' ?>
    <div class="bxc-cnt bxc-box">
        <div class="bxc-top">
            <div>
                <?php echo '<div class="bxc-title">' . bxc_(bxc_settings_get('form-title', 'Payment method')) . '</div><div class="bxc-text">' . trim(bxc_(empty($settings['description']) ? bxc_settings_get('form-description', '') : $settings['description'])) . '</div>' ?>
            </div>
        </div>
        <div class="bxc-body">
            <div class="bxc-flex bxc-amount-fiat<?php if (!$checkout_price) echo ' bxc-donation' ?>">
                <div class="bxc-title">
                    <?php bxc_e('Total') ?>
                    <?php if (!$checkout_price) echo '<div class="bxc-text">' . bxc_(bxc_settings_get('user-amount-text', 'Pay what you want')) . '</div>'; ?>
                </div>
                <div class="bxc-title">
                    <?php echo $checkout_price ? strtoupper($settings['currency']) . ' ' . $checkout_price : '<div class="bxc-input" id="user-amount"><span>' . strtoupper($settings['currency']) . '</span><input type="number" min="0" /></div>' ?>
                </div>
            </div>
            <div class="bxc-flex bxc-payment-methods-cnt">
                <div class="bxc-title">
                    <?php bxc_e('Pay with') ?>
                </div>
                <div class="bxc-payment-methods">
                    <?Php echo $cryptocurrencies_code ?>
                </div>
            </div>
        </div>
    </div>
    <div class="bxc-pay-cnt bxc-box">
        <div class="bxc-top">
            <div class="bxc-pay-top-main">
                <div class="bxc-title">
                    <?php bxc_e('Send payment') ?>
                    <div class="bxc-flex">
                        <div class="bxc-countdown bxc-toolip-cnt">
                            <div data-countdown="<?php bxc_settings_get('refresh-interval', 60) ?>"></div>
                            <span class="bxc-toolip">
                                <?php bxc_e('Checkout timeout') ?>
                            </span>
                        </div>
                        <div class="bxc-btn bxc-btn-border bxc-btn-text-icon bxc-back">
                            <i class="bxc-icon-back"></i><?php bxc_e('Back') ?>
                        </div>
                    </div>
                </div>
                <?php echo '<div class="bxc-text">' . trim(bxc_(bxc_settings_get('form-payment-description', ''))) . '</div>' ?>
            </div>
            <div class="bxc-pay-top-back">
                <div class="bxc-title">
                    <?php bxc_e('Are you sure?') ?>
                </div>
                <div class="bxc-text">
                    <?php bxc_e('This transaction will be cancelled. If you already sent the payment please wait.') ?>
                </div>
                <div id="bxc-confirm-cancel" class="bxc-btn bxc-btn-border bxc-btn-red">
                    <?php bxc_e('Yes, I\'m sure') ?>
                </div>
                <div id="bxc-abort-cancel" class="bxc-btn bxc-btn-border bxc-back">
                    <?php bxc_e('Cancel') ?>
                </div>
            </div>
        </div>
        <div class="bxc-body">
            <div class="bxc-flex">
                <img class="bxc-qrcode" src="" alt="QR code" />
                <div class="bxc-flex bxc-qrcode-text">
                    <img src="" alt="" />
                    <div class="bxc-text"></div>
                </div>
            </div>
            <div class="bxc-flex bxc-pay-address">
                <div>
                    <div class="bxc-text"></div>
                    <div class="bxc-title"></div>
                </div>
                <i class="bxc-icon-copy bxc-clipboard bxc-toolip-cnt">
                    <span class="bxc-toolip">
                        <?php bxc_e('Copy to clipboard') ?>
                    </span>
                </i>
            </div>
            <div class="bxc-flex bxc-pay-amount">
                <div>
                    <div class="bxc-text">
                        <?php bxc_e('Total amount') ?>
                    </div>
                    <div class="bxc-title"></div>
                </div>
                <i class="bxc-icon-copy bxc-clipboard bxc-toolip-cnt">
                    <span class="bxc-toolip">
                        <?php bxc_e('Copy to clipboard') ?>
                    </span>
                </i>
            </div>
        </div>
    </div>
    <div class="bxc-tx-cnt bxc-box">
        <div class="bxc-loading"></div>
        <div class="bxc-title">
            <?php bxc_e('Payment received') ?>
        </div>
        <div class="bxc-flex">
            <div class="bxc-tx-status"></div>
            <div class="bxc-tx-confirmations">
                <span></span> /
            </div>
            <div>
                <?php bxc_e('confirmations') ?>
            </div>
        </div>
    </div>
    <div class="bxc-complete-cnt bxc-box">
        <i class="bxc-icon-check"></i>
        <div class="bxc-title">
            <?php bxc_e(bxc_settings_get('success-title', 'Payment completed')) ?>
        </div>
        <div class="bxc-text">
            <?php bxc_e(bxc_settings_get('success-title', 'Thank you for your payment')) ?>
        </div>
    </div>
    <div class="bxc-failed-cnt bxc-box">
        <i class="bxc-icon-close"></i>
        <div class="bxc-title">
            <?php bxc_e(bxc_settings_get('failed-title', 'No payment')) ?>
        </div>
        <div class="bxc-text">
            <?php bxc_e(bxc_settings_get('failed-text', 'We didn\'t detect a payment. If you have already paid, please contact us.')) ?>
        </div>
        <div class="bxc-text">
            <?php bxc_e('Your transaction ID is:') ?>
            <span id="bxc-expired-tx-id"></span>
        </div>
        <div class="bxc-btn bxc-btn-border ">
            <?php bxc_e('Retry') ?>
        </div>
    </div>
</div>
<?php } ?>