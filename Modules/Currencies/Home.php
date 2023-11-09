<?php

namespace Currencies;

require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Api\CPrivilege;
use Modules\CModule;
use Helpers\HtmlHelper;
use Api\Session\CSession;
use Currencies\CCurrency;
use Api\Mt;

require_once("Scripts/CheckLogin.php");

CPrivilege::verifyPrivilege(CCurrency::CURRENCIES_READ);

require_once("Scripts/HtmlHelper.php");

HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_]);
HtmlHelper::PageStartX(
    ["title" => "Currencies", "description" => "Currencies", "path" =>
        ["Currencies" => "Currencies"]], null);
?>

    <div class="mt-main-body">
        <div class="row">
            <div class="col-12">
                <div id='currencyData'>
                    <div id="mCurrencyToolbar">
                        <div class="form-inline" role="form">
                            <div>
                                <a href="#" onclick='newCurrency()'><i class='fas fa-plus'></i> &nbsp;&nbsp; New
                                    Currency</a>
                            </div>
                            <!--            <button id="ok" type="submit" class="btn btn-primary"></button>-->
                        </div>
                    </div>
                    <table
                            id="currenciesTable"
                            data-show-columns="true"
                            data-search="true"
                            data-show-toggle="true"
                            data-pagination="true"
                            data-virtual-scroll="true"
                            data-toggle="table"
                            data-side-pagination="server"
                            data-server-sort="true"
                            data-query-params="defaultQueryParams"
                            data-response-handler="defaultResponseHandler"
                            data-resizable="true"
                            data-remember-order="true"
                            data-editable-emptytext="Default empty text."
                            data-editable-url="Helper/FetchCurrencies"
                            data-toolbar="#mCurrencyToolbar"
                            data-url="<?php echo Mt::$appRelDir ?>/Helpers/FetchCurrencies">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true" data-visible="false" class='id-column'>Id</th>
                            <th data-field="creationDate" data-sortable="true" data-visible="false"
                                data-formatter="dateFormatter" class='date-column'>Creation Date
                            </th>
                            <th data-field="name" data-sortable="true">Name</th>
                            <th data-field="ratio" data-sortable="true" data-formatter="numberFormatter"
                                class='amt-column'>Ratio
                            </th>
                            <th data-field="description" data-sortable="true" class='description-column'>Description
                            </th>
                        </tr>
                        </thead>
                        <tbody id='currenciesBody'>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <?php
HtmlHelper::PageFooter(['Currencies/Js/Currencies.js']);
HtmlHelper::newModalX("Currency", __DIR__ . "/Contents", "modal-xl");
HtmlHelper::PageEndX();