<?php
namespace Helpers;

use Api\Mt;
use Api\Users\CUser;
use Companies\CCompany;
use Currencies\CCurrency;
use Helpers\PersonHelper;

class CurrencyHelper
{
    static function fromPost()
    {
        if (isset($_POST['name']) && isset($_POST['ratio']) && isset($_POST['flags']) && isset($_POST['description']) ) {
            $flags = 	(Mt::getPostVarN("isDefault") == "on" ? CCurrency::FLAG_DEFAULT : 0) |
                (Mt::getPostVarN("isBank") == "on" ? CCurrency::FLAG_BANK : 0) |
                (Mt::getPostVarN("isVirtual") == "on" ? CCurrency::FLAG_VIRTUAL : 0) |
                (Mt::getPostVarN("isCrypto") == "on" ? CCurrency::FLAG_CRYPTO : 0);

            $currency = new CCurrency($_POST['name'], $_POST['ratio'], $flags, $_POST['description']);
            $currency->id = Mt::getPostVarZ("id");
            $currency->visibility = Mt::getPostVarZ('companies', '*');
            $currency->trailingZeros = Mt::getPostVar('trailingZeroes');
            $currency->symbol = Mt::getPostVar('symbol');
            $currency->decimalSymbol = Mt::getPostVar('decimalSymbol');
            $currency->groupSymbol = Mt::getPostVar('groupSymbol');
            return $currency;
        }
        print_r($_POST);
        echo "Error on " . __FILE__;
        return null;
    }
}