<?php
use Helpers\HtmlHelper;
use Currencies\CCurrency;

HtmlHelper::addJsFile("Assets/plugins/big-js/big.min.js");
HtmlHelper::addJsFile("Assets/js/Currency.js");

$currency = CCurrency::getDefault() ?? ["id" => 0, "ratio"=>1];

$factor = $currency['ratio'];
echo "
<script>
    const defaultCurrency = '{$currency['id']}';
    const defaultCurrencyFactor = '$factor';
    let currentCurrency = '{$currency['id']}';
    let currencyFactor = '$factor';
</script>";
