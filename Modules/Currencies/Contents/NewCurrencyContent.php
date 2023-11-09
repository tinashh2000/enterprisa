<?php

use Api\CPrivilege;
use Currencies\CCurrency;
use Helpers\HtmlHelper;
use Api\Mt;
use Api\Users\CurrentUser;

require_once("Scripts/CheckLogin.php");
CPrivilege::verifyPrivilege(CCurrency::CURRENCIES_WRITE);

HtmlHelper::addJsFile("Currencies/Js/Currencies.js");

?>
            <form id="newCurrencyForm" onsubmit="return false" method="post">
                <div class='p-0'>
                    <input name="r" type="hidden" value="create"/>
                    <input name="id" type="hidden" value="0"/>
                    <input name="lastModifiedDate" type="hidden" value="" />
                    <input name="flags" type="hidden" value="0"/>
                    <div class="row">
                        <div class="col-12">
                            <label>Name</label>
                            <div class="form-group input-group">
                                <input type="text" name='name' class="form-control" placeholder="Name">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-sm-4 col-md-3">
                            <label>Ratio</label>
                            <div class="form-group input-group">
                                <input type="text" name='ratio' class="form-control" placeholder="Ratio">
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-3">
                            <label>Symbol</label>
                            <div class="form-group input-group">
                                <input type="text" name='symbol' class="form-control" placeholder="Symbol" value="$">
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-3">
                            <label>Decimal Point Symbol</label>
                            <div class="form-group input-group">
                                <input type="text" name='decimalPointSymbol' class="form-control" placeholder="Decimal Point Symbol" value=".">
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-3">
                            <label>Group Seperator Symbol</label>
                            <div class="form-group input-group">
                                <input type="text" name='groupSymbol' class="form-control" placeholder="Group Symbol" value=",">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-12">
                            <label>Companies</label>
                            <div class="form-group input-group">
                                <select name="account" class="select2 companyPicker" multiple required></select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label>Description</label>
                            <div class="form-group input-group">
                            <textarea type="text" name='description' class="form-control"
                                      placeholder="Description"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-0 row">
                                <div class="col-6 col-md-3 custom-control custom-checkbox">
                                    <input type="checkbox" name="isDefault" class="custom-control-input"
                                           id="setDefault">
                                    <label class="custom-control-label" for="setDefault">Set as default</label>
                                </div>
                                <div class="col-6 col-md-3 custom-control custom-checkbox">
                                    <input type="checkbox" name="isBank" class="custom-control-input"
                                           id="isBank">
                                    <label class="custom-control-label" for="isBank">Bank</label>
                                </div>
                                <div class="col-6 col-md-3 custom-control custom-checkbox">
                                    <input type="checkbox" name="isVirtual" class="custom-control-input"
                                           id="isVirtual">
                                    <label class="custom-control-label" for="isVirtual">Virtual Currency</label>
                                </div>
                                <div class="col-6 col-md-3 custom-control custom-checkbox">
                                    <input type="checkbox" name="isCrypto" class="custom-control-input"
                                           id="isCrypto">
                                    <label class="custom-control-label" for="isCrypto">Crypto-currency</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
