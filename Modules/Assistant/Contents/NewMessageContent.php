<?php
namespace Assistant;

use Api\CPersonEntity;
use Helpers\HtmlHelper;

use Api\Users\CurrentUser;
use Api\CPrivilege;

?>
<form id="newMessageForm" onsubmit="return false" method="post">
    <!-- /.card-header -->
    <input type="hidden" name="r" />
    <input type="hidden" name="recipients" value="" />
    <input type="hidden" name="types" value="" />
    <input type="hidden" name="categories" value="" />

    <div class="row">
        <div class="col-md-12 col-sm-12">
            <label>Recipients</label>
            <div class="form-group input-group">
                <select id="messageRecipient" class="select2" data-placeholder="Recipients" multiple>
                    <option value=""></option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-3">
                <div class="form-group mb-0 d-flex justify-content-end">
                    <div class="custom-control custom-checkbox mr-4">
                    <?php if (CPrivilege::isAdmin()) { ?>
                            <a href="#" id="moreOptions" class="mt-1 text-primary">More Options >></a>
                    <?php } ?>
                    </div>
                    <div class="custom-control custom-checkbox mr-4">
                        <input type="checkbox" name="sendInternal" id="sendInternal" class="custom-control-input" checked />
                        <label class="custom-control-label" for="sendInternal">Send internal message</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="sendEmail" id="sendEmail" class="custom-control-input" />
                        <label class="custom-control-label" for="sendEmail">Send email(s)</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if (CPrivilege::isAdmin()) { ?>
    <div class="row d-none" id="advancedOptions">
        <div class="col-12 col-sm-6">
            <label>Recipients By Type</label>
            <div class="form-group input-group">
                <select id="recipientTypes" data-placeholder="Type" multiple class="select2">
                    <option value="*">All</option>
                    <?php
                        $t = CPersonEntity::getItems("Types");
                        foreach($t as $type)
                            echo "<option value='{$type['value']}'>{$type['title']}</option>";
                    ?>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <label>Recipients By Category</label>
            <div class="form-group input-group">
                <select id="recipientCategories" data-placeholder="Categories" multiple class="select2">
                    <option value="*">All</option>
                    <?php
                    $c = CPersonEntity::getItems("Categories");
                    foreach($c as $cat)
                        echo "<option value='{$cat['value']}'>{$cat['title']}</option>";

                    ?>
                </select>

            </div>
        </div>


    </div>
    <?php } ?>



    <div class="row">
        <div class="col-md-12 col-sm-12">
            <label>Subject</label>
            <div class="form-group input-group">
                <input class="form-control" name="subject" placeholder="Subject:" value="<?= $subject ?? '' ?>">
            </div>
        </div>

        <div class="col-md-12 col-sm-12">
            <label>Messages</label>
            <div class="form-group input-group">
                <textarea id="compose-textarea" name="message" class="form-control" style="height: 300px"><?= $message ?? '' ?></textarea>
            </div>
        </div>
    </div>

    <div class="">
        <div class="float-right">
            <button type="button" class="btn btn-default"><i class="fas fa-pencil-alt"></i>
                Draft
            </button>
            <button type="submit" class="btn btn-primary"><i class="far fa-envelope"></i> Send
            </button>
        </div>
    </div>

</form>
