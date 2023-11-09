<form id="emailSettingsForm" onsubmit="return false" method="post">
<?php
    use Api\CAssistant;
    use Ffw\Crypt\CCrypt9;
?>
    <input name="r" type="hidden" value="setEmailSettings"/>
    <input name="id" type="hidden" value="0"/>
    <input name="lastModifiedDate" type="hidden" value="" />
    <input name="flags" type="hidden" value="<?= $emailSettings['flags'] ?? "0" ?>"/>
    <input name="serviceProvider" type="hidden" value=""/>
    <input name="auth" type="hidden" value=""/>

    <div class="row mt-3">
        <div class="col-12 ">
            <label>Display Name</label>
            <div class="form-group input-group">
                <input type="text" name='name' class="form-control"
                       placeholder="Display Name" value="<?= $data['displayName'] ?? "" ?>">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-12 mb-3">
            <label>Service Provider</label>
            <select class="select2" id="serviceProviderPicker" name="serviceProvider" data-placeholder="Service Provider">
                <option value=""></option>
                <option value="gmail">Gmail</option>
                <option value="live">Live</option>
                <option value="yahoo">Yahoo</option>
                <option value="manual" selected>Manual Settings</option>
            </select>
        </div>
    </div>
    <?php if (!isset($data['noIncoming'])) { ?>
    <div class="row">
        <div class="col-12 col-md-12 ">
            <label>Incoming Server</label>
            <div class="form-group input-group">
                <input type="text" name='incomingServer' class="form-control"
                       placeholder="Incoming Server" value="<?= $data['incomingServer']  ?? ""?>">
            </div>
        </div>

        <div class="col-12">
            <div class="row">
                <div class="col-4">
                    <label>Incoming Delivery</label>
                    <select class="select2" id="incomingDeliveryPicker" name="incomingDelivery" data-placeholder="Delivery" value="<?= $data['incomingDelivery'] ?? "" ?>">
                        <option value="imap" selected>Imap</option>
                        <option value="pop3">POP3</option>
                    </select>
                </div>
                <div class="col-4">
                    <label>Incoming Encryption</label>
                    <select class="select2" id="incomingSecurityPicker" name="incomingSecurity" data-placeholder="Security" value="<?= $data['incomingSecurity'] ?? "" ?>">
                        <option value="tls" selected>TLS</option>
                        <option value="ssl">SSL</option>
                    </select>
                </div>
                <div class="col-4">
                    <label>Incoming Port</label>
                    <div class="form-group input-group">
                        <input type="text" name='incomingPort' class="form-control"
                               placeholder="Outgoing Port" value="<?= $data['incomingPort'] ?? "" ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
    <div class="row">
        <div class="col-md-12 col-sm-12 ">
            <label>Outgoing Server</label>
            <div class="form-group input-group">
                <input type="text" name='outgoingServer' class="form-control"
                       placeholder="Outgoing Server" value="<?= $data['outgoingServer'] ?? "" ?>">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-4">
            <label>Outgoing Delivery</label>
            <select class="select2" id="outgoingDeliveryPicker"
                    name="outgoingDelivery" data-placeholder="Delivery" value="<?= $data['outgoingDelivery'] ?? "" ?>">
                <option value="smtp" selected>SMTP</option>
            </select>
        </div>
        <div class="col-4">
            <label>Outgoing Encryption</label>
            <select class="select2" id="outgoingSecurityPicker"
                    name="outgoingSecurity" data-placeholder="Delivery" value="<?= $data['outgoingSecurity'] ?? "" ?>">
                <option value="tls" selected>TLS</option>
                <option value="ssl">SSL</option>
            </select>
        </div>
        <div class="col-4">
            <label>Outgoing Port</label>
            <div class="form-group input-group">
                <input type="text" name='outgoingPort' class="form-control"
                       placeholder="Outgoing Port" value="<?= $data['outgoingPort'] ?? "" ?>">
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6 col-sm-12 ">
            <label>Username or Email Address</label>
            <div class="form-group input-group">
                <input type="text" name='username' class="form-control"
                       placeholder="Username" value="<?= $data['username'] ?? "" ?>">
            </div>
        </div>
        <div class="col-md-6 col-sm-12 ">
            <label>Password</label>
            <div class="form-group input-group">
                <input type="password" name='password' class="form-control"
                       placeholder="Password">
            </div>
        </div>
    </div>

    <a href="#">
        <button type="submit" class="link-btn text-primary mb-2 mr-3"><i
                class='fas fa-save'></i> <span>Save</span></button>
    </a>
</form>

<script>
$(function(){
<?php if (!isset($data['noIncoming'])) { ?>
    $("#incomingSecurityPicker").val("<?= $data['incomingSecurity']  ?? ""?>").trigger("change");
    $("#incomingDeliveryPicker").val("<?= $data['incomingDelivery']  ?? ""?>").trigger("change");
<?php } ?>

$("#outgoingDeliveryPicker").val("<?= $data['outgoingDelivery']  ?? ""?>").trigger("change");
$("#outgoingSecurityPicker").val("<?= $data['outgoingSecurity']  ?? ""?>").trigger("change");
$("#serviceProviderPicker").val("<?= $data['serviceProvider']  ?? ""?>").trigger("change");
});
</script>