<?php

namespace Assistant;

require_once("Api/Bootstrap.php");
require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Helpers\HtmlHelper;
use Api\Mt;
use Api\AppDB;

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Assistant");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_MAGICDIV_]);
HtmlHelper::PageStartX(
    ["title" => "People", "description" => "People", "path" => ["People" => "People"]], null);

?>
    <div class="mt-main-body">
                    <div class="row">
                        <div class="col-12">
                            <div id='personData'>
                                <div id="mPersonToolbar">
                                    <div class="form-inline" role="form">
                                        <div class="col-12 d-flex align-items-center justify-content-between" role="form">
                                            <div class="d-inline">
                                                <a href="#" onclick='newPerson()'><i class='fas fa-plus'></i>&nbsp;New
                                                    People</a>&nbsp;&nbsp;&nbsp;
                                                <a href="PeopleT"><i class='fas fa-sync'></i>&nbsp;Alternate View</a>
                                            </div>
                                            <div id="mdivToolbar" class="d-flex align-items-center justify-content-between"></div>
                                        </div>
                                    </div>
                                </div>
                                <div id="peopleMagicDiv"
                                     class="magicDiv mt-3"
                                     data-magicDiv-paginate="true"
                                     data-magicDiv-renderer="renderPerson"
                                     data-double-click="personDblClick"
                                     data-magicDiv-source="<?php echo Mt::$appRelDir ?>/Helpers/FetchPeople"
                                     data-magicDiv-toolbar="#mdivToolbar"
                                     data-magicDiv-numRows="2">

                                    <div class="magicDivTemplate">
                                        <div class="col-12 col-sm-6 col-md-6  col-lg-4 d-flex align-items-stretch m-0 p-2">
                                            <div class="col-12 card bg-light p-0 m-0">
                                                <div class="card-body">
                                                    <div class="col-12 p-0">

                                                        <img src="<?php echo Mt::$appRelDir ?>/people/{uid}/pic"
                                                             alt=""
                                                             class="img-100 img-username img-circle float-left mr-3 mb-3">
                                                        <div>
                                                            <h2 class="lead"><b>{name}</b></h2>
                                                            <div class="text-muted small"><i
                                                                        class="fas fa-lg fa-phone"></i>&nbsp; {phone}</div>

                                                            <div class="text-muted small"><i
                                                                        class="fas fa-lg fa-envelope"></i>&nbsp; {email}</div>

                                                            <div class="text-muted small"><i
                                                                        class="fas fa-lg fa-building"></i>&nbsp; Address: {address}, {city}, {country}</div>

                                                            <a href="#" class="btn btn-sm bg-teal">
                                                                <i class="fas fa-comments"></i>
                                                            </a>
                                                            <a href="<?php echo Mt::$appRelDir ?>/people/{id}"
                                                               class="btn btn-sm btn-link">
                                                                <i class="fas fa-user"></i> View Profile
                                                            </a>
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
                </div>

    <?php

HtmlHelper::addJsFile("Assistant/Js/NewPerson.js");
HtmlHelper::newModalX("Person", __DIR__ . "/Contents", "modal-xl");
HtmlHelper::PageFooter(array("Assets/js/countries-select2.js", "Assets/js/Person.js"));
HtmlHelper::PageEndX();
