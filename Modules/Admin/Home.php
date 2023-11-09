<?php


    use Api\CPrivilege;
    use Modules\CModule;
    use Helpers\HtmlHelper;
    use Api\Mt;
    use Api\Users\CUser;
use Api\CPerson;
    require_once("Scripts/CheckLogin.php");
    require_once(__DIR__ ."/Api/Bootstrap.php");
    CPrivilege::isAdmin();
    require_once("Scripts/HtmlHelper.php");
    HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Crm");
    HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_MAGICDIV_]);
    HtmlHelper::PageStartX(
        ["title" => "Admin Dashboard", "description" => "Admin Dashboard", "path" =>
            ["Home" => "Dashboard"]], null);

    ?>

    <div class="mt-main-body">

        <div class="row">
            <div class="col-12">
                <div class="row">

                    <div class="col-xl-3 col-md-6">
                        <div class="card prod-p-card card-red">
                            <div class="card-body">
                                <div class="row align-items-center m-b-30">
                                    <div class="col">
                                        <h6 class="m-b-5 text-white">Users</h6>
                                        <h3 class="m-b-0 f-w-700 text-white"><?= CUser::count() ?></h3>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-money-bill-alt text-c-red f-18"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card prod-p-card card-blue">
                            <div class="card-body">
                                <div class="row align-items-center m-b-30">
                                    <div class="col">
                                        <h6 class="m-b-5 text-white">Admins</h6>
                                        <h3 class="m-b-0 f-w-700 text-white"><?= CUser::count("privileges='".CPrivilege::ROLE_ADMINISTRATOR."'") ?></h3>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-database text-c-blue f-18"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card prod-p-card card-green">
                            <div class="card-body">
                                <div class="row align-items-center m-b-30">
                                    <div class="col">
                                        <h6 class="m-b-5 text-white">Approved</h6>
                                        <h3 class="m-b-0 f-w-700 text-white"><?= CPerson::count() ?></h3>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign text-c-green f-18"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row mt-3 mb-3">

                    <div class="col-xl-6 col-md-12">
                        <div class="card latest-update-card">
                            <div class="card-header">
                                <h5>Activities</h5>
                            </div>
                            <div class="card-block">
                                <div class="latest-update-box">

                                </div>
                            </div>
                        </div>
                    </div>



                        <div class="col-xl-6 col-md-12">
                            <div class="card new-cust-card">
                                <div class="card-header">
                                    <h5>New Users</h5>
                                </div>
                                <div class="card-block">


                                    <div
                                            id="customersMagicDiv"
                                            class="magicDiv mt-3"
                                            data-magicDiv-paginate="true"
                                            data-magicDiv-source="<?= Mt::$appRelDir ?>/Helpers/FetchCustomers"
                                            data-magicDiv-formatter="myTasksFormatter"
                                            data-magicDiv-numRows="2">
                                        <div class="magicDivTemplate">
                                            <div class="align-middle mb-2">


                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    HtmlHelper::PageFooter();
    HtmlHelper::PageEndX();
