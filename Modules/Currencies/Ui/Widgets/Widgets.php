<?php

namespace Assistant;

use Currencies\CCurrency;
require_once(__DIR__ . "/../../Api/Bootstrap.php");
return [
    ["description" => "Currencies", "filename" => "Currencies", "privileges" => CCurrency::CURRENCIES_READ, "sizes" => [100, 50, 25], "type" => "card" ],
];
