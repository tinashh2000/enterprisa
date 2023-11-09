<?php
use Helpers\HtmlHelper;
use Modules\CModule;
HtmlHelper::addJsFile("Entities/Js/Entities.js");

?>
            <form id="newEntityForm" onsubmit="return false" method="post">
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
                    <div class="col-12">
                    <label>Module</label>
                    <div class="form-group input-group">
                        <select class="select2" name='module'>
                            <option value="*">All</option>
                            <?php
                            $m = CModule::getModulesInfo();
                            foreach($m as $k=>$mdl)
                                echo "<option value='$k'>{$mdl['Name']}</option>";
                            ?>
                        </select>
                    </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                    <label>Type</label>
                    <div class="form-group input-group">
                        <select class="select2" name='module'>
                            <option value="10">Inline</option>
                            <option value="11">External</option>
                            <option value="12">Yes/No</option>
                        </select>
                    </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                    <label>Classification</label>
                    <div class="form-group input-group">
                        <input type="text" name='classification' class="form-control" placeholder="Classification">
                    </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                    <label>Details</label>
                    <div class="form-group input-group">
                        <input type="text" name='details' class="form-control" placeholder="Details">
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

                <div class="row col-12 mt-2 mb-1">
                    <div class="col-4">
                        <button type="button" class="link-btn d-none text-danger mb-2 mr-1 newEntityDeleteBtn"
                                onclick="doDeleteEntity()"
                                ><i
                                    class='fas fa-trash'></i><span>&nbsp;&nbsp;&nbsp;&nbsp;Delete</span>
                        </button>
                    </div>
                    <div class="col-8 d-flex justify-content-end">
                        <a href="#">
                            <button type="submit" class="link-btn text-primary mb-2 mr-3 newEntityCreateBtn" ><i
                                        class='fas fa-check'></i> <span>Create</span></button>
                        </a>
                        <a href="#">
                            <button type="button" class="link-btn d-none text-danger mb-2 mr-1 cancelBtn newEntityCancelBtn"
                                    data-bs-dismiss="modal"><i class='fas fa-ban'></i><span> Cancel</span></button>
                        </a>
                    </div>
                </div>
            </form>
