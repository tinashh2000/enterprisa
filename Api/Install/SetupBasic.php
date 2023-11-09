<?php
namespace Api\Install;

use Api\AppDB;
use Api\CPersonEntity;
use Api\Install\Setup;
use Api\Mt;
use Api\Session\CSession;
use Api\Users\CUser;
use Ffw\Crypt\CCrypt8;
use Ffw\Status\CStatus;
use Api\CPrivilege;
use Api\CPerson;

    require_once("Classes/CInstall.php");

    $pd = CInstall::getPhaseData(PHASE_BASIC);
    $resetTables = isset(CInstall::$dbSettings->resetDatabase) ? true : false;

    $hf = fopen(Mt::$dataPath . "/installation.dat", "a");

    if ($pd == 0 && isset($_POST['adminName'])) {
        require_once("Classes/Setup.php");

        if (!file_exists(Mt::$dbRootPath))
            mkdir(Mt::$dbRootPath, 0777, true);

        AppDB::disableLogs();
        Setup::initDB($resetTables);
        $adminName = $_POST['adminName'];
        $adminAddress = $_POST['adminAddress'];
        $adminPhone = $_POST['adminPhone'];
        $adminEmail = $_POST['adminEmail'];
        $adminUsername = $_POST['adminUsername'];
        $adminGender = Mt::getPostVarZ('adminGender',1);
        $adminDob = $_POST['adminDob'];
        $adminPassword = $_POST['adminPassword'];
        $adminIdNumber = $_POST['adminIdNumber'];
        $privilegesList = CPrivilege::L_CREATE_USER . ";" . CPrivilege::L_DELETE_USER . ";" . CPrivilege::L_VIEW_USER . ";" . CPrivilege::L_ALTER_USER . ";" . CPrivilege::L_ALL_USER;

        $person = new CPerson($adminName, $adminEmail, $adminGender, CPerson::MARITAL_STATUS_SINGLE, $adminPhone, CPerson::PERSON_TYPE_USER, "", $adminAddress, "", "Zimbabwe", "", 0 );
        $person->dob = $adminDob;
        $person->attributes = CPerson::PERSON_ATTR_ADMIN;
        $person->idNumber = $adminIdNumber;
        $user = new CUser( $adminUsername, $adminPassword, "9223372036854775807", $privilegesList, "");  //new CUser
        $person->user = $user;
        CSession::set("name", $adminName);
        CSession::set("email", $adminEmail);
        CSession::set("username", $adminUsername);
        CSession::set("regdate", gmdate("Y-m-d H:i:s", strtotime("now")));
        CSession::set("lastUpdated",  gmdate("Y-m-d H:i:s", strtotime("now")));
        CSession::set("privileges", CPrivilege::ROLE_ADMINISTRATOR);
        CSession::set("privilegesList", "");
        CSession::set("logged", "world20");
        CSession::set("reload_f", 3);

        CStatus::set(0);
        if ($u = $user->exists()) {
            if (Mt::getPostVar("overwritePerson") == "on") {
                $u->password = $adminPassword;
                $u->privileges = "9223372036854775807";
                $u->privilegesList = $privilegesList;
                $u->roles = "";
                $u->edit();
                $person->user = $u;
            } else if (Mt::getPostVar("deletePerson") == "on") {
                CUser::delete($u["id"]);
            }
        }

        if ($p = $person->exists()) {
            $person = new CPerson($adminName, $adminEmail, $adminGender, CPerson::MARITAL_STATUS_SINGLE, $adminPhone, CPerson::PERSON_TYPE_USER, "", $adminAddress, "Harare", "Zimbabwe", "", 0 );
            $person->dob = $adminDob;
            $person->attributes = CPerson::PERSON_ATTR_ADMIN;
            $person->idNumber = $adminIdNumber;

            if (Mt::getPostVar("overwritePerson") == "on") {
                $p->name = $person->name;
                $p->email = $person->email;
                $p->gender = $person->gender;
                $p->marital = $person->maritalStatus;
                $p->phone = $person->phone;
                $p->type = $person->user;
                $p->categories = $person->categories;
                $p->address = $person->address;
                $p->city = trim($p->city) == "" ? $person->city : $p->city;
                $p->country= trim($person->country) == "" ? $person->country : $p->country;
                $p->notes = trim($p->notes) == "" ? $person->notes : $p->notes;
                $p->dob=$person->dob;
                $p->attributes = $person->attributes;
                $p->idNumber = $person->idNumber;
                $p->edit();
            } else if (Mt::getPostVar("deletePerson") == "on"){
                CPerson::delete($p["id"]);
                $person->create();
            }
        } else
            $person->create();

        $errors = CStatus::getErrors();

        $prev = CInstall::getInstallPhase();

        if (!is_array($errors) || count($errors) == 0) {

            CInstall::setInstallPhase(PHASE_BASIC + 1);

            fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "BasicCompleted\n");
            fclose($hf);

            die('{"status":"OK", "messages" : ' . json_encode(CStatus::getMessages()) . ', "errors" : ' . json_encode(CStatus::getErrors()) . '}');

        }

        fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "Basic Failed\n");
        fclose($hf);

        die('{"status":"Error", "messages" : ' . json_encode(CStatus::getMessages()) . ', "errors" : ' . json_encode(CStatus::getErrors()) . ',"' . $prev . '=>' . CInstall::getInstallPhase().'":""}');

    }

    fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "BasicStart\n");
    fclose($hf);

?>

<section class="content">
    <div class="container-fluid v-align">
        <div class="row  d-flex align-self-center justify-content-center">
            <div class='col-md-6 col-sm-10 p-0 m-5'>
                <div class="card">
    <h3 class="col-12 bg-primary pl-3 p-2">Set Administrator Account <?php echo CInstall::$setupPhase ?></h3>
                <div class="p-3">
                    <div class="logo d-flex align-self-center justify-content-end mt-0 p-0 mb-3"><a href="home"><img src='<?php echo \Helpers\HtmlHelper::link("Assets/img/hlogo"); ?>' height='40px'/></a></div>

                    <form onsubmit="return false;" method="post" id="userForm">

                    <input type='hidden' name='adminInfo' value='1'/>

                        <div class="row">
                            <div class="col-12">

                    <label>Full Name</label>
                    <div class="form-group input-group mb-3">
                        <input type="text" name='adminName' class="form-control" placeholder="Full Name">
                    </div>
                            </div></div>

                        <div class="row">
                            <div class="col-12">

                            <label>Username</label>
                    <div class="form-group input-group mb-3">
                        <input type="text" name='adminUsername' class="form-control" placeholder="Username" value="Administrator">
                    </div>
                            </div></div>

                        <div class="row">
                            <div class="col-12">


                            <label>Email</label>
                    <div class="form-group input-group mb-3">
                        <input type="text" name='adminEmail' class="form-control" placeholder="Email Address">
                    </div>
                            </div></div>

                        <div class="row">
                            <div class="col-12">


                            <label>Phone</label>
                    <div class="form-group input-group mb-3">
                        <input type="text" name='adminPhone' class="form-control" placeholder="Phone number">
                    </div>
                            </div></div>


                    <div class="row">

                        <div class="col-md-6 col-sm-12">
                            <label>Date of Birth</label>
                            <div class="form-group input-group mb-3">
                                <input type="text" name='adminDob' id="adminDob" class="form-control" placeholder="Date of Birth">
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <label>Gender</label>
                            <div class="form-group input-group mb-3">
                                <select class="select2" name="adminGender" data-placeholder="Gender"
                                        required>
                                    <option value="1">Male</option>
                                    <option value="2">Female</option>
                                </select>
                            </div>
                        </div>


                    </div>

                        <div class="row">
                            <div class="col-12">


                            <label>Id Number</label>
                    <div class="form-group input-group mb-3">
                        <input type="text" name='adminIdNumber' class="form-control" placeholder="Id Number">
                    </div>
                            </div></div>

                        <div class="row">
                            <div class="col-12">


                            <label>Address</label>
                        <div class="form-group input-group mb-3">
                            <textarea name='adminAddress' class="form-control" placeholder="Address"></textarea>
                        </div>
                            </div></div>


                        <div class="row">
                            <div class="col-12">


                            <label>Password</label>
                    <div class="form-group input-group mb-3">
                        <input type="password" name='adminPassword' class="form-control" placeholder="Password">
                    </div>
                            </div>

                        <div class="row col-12">
                            <div class="col-6">
                            <div class="form-group mb-0 ">
                                <div class="icheck-primary icheck-inline">
                                    <input type="checkbox" id='overwritePerson' name="overwritePerson">
                                    <label for="overwritePerson">Overwrite existing profile</label>
                                </div>
                            </div>
                            </div>
                            <div class="col-6">
                            <div class="form-group mb-0 ">
                                <div class="icheck-primary icheck-inline">
                                    <input type="checkbox" id="deletePerson" name="deletePerson">
                                    <label for="deletePerson">Delete existing profile</label>
                                </div>
                            </div>
                            </div>
                        </div>


                        </div>

                        <div class="row col-12 m-0 p-0">
                            <div class="col-6 d-flex justify-content-start m-0 p-0"><button id="install-proceed" type="submit"
                                                                                            class="btn btn-primary pl-2 pr-2">Proceed
                                    &nbsp;<i class=" fas fa-arrow-circle-right"></i></button></div>
                            <div class="col-6 d-flex justify-content-end m-0 p-0"><a href="<?php echo Mt::$appRelDir ?>"
                                                                                     class="btn btn-warning"
                                                                                     id="install-cancel">Exit Installation&nbsp;<i
                                            class=" fas fa-times-circle"></i></a></div>
                        </div>

                    </form>


                </div></div></div></div></div></section>


<script>

    var initPhase =  function () {

        //Date range picker
        $('#adminDob').daterangepicker({singleDatePicker:true, locale: {format: 'YYYY/MM/DD'}});

        $('#userForm').validate({
            rules: {
                adminName: {
                    required: true
                },
                adminEmail: {
                    required: true,
                    email: true
                },
                adminPhone: {
                    required: true,
                },
                adminDob: {
                    required: true
                },
                adminGender: {
                    required: true
                },
                adminIdNumber: {
                    required: true
                },
                adminPassword: {
                    required: true,
                    minlength: 4
                }
            },
            messages: {
                adminName: {
                    required: "Please enter the admin's full name",
                    email: "Please enter a valid email address"

                },
                adminEmail: {
                    required: "Please provide a email",
                    email: "Please enter a valid email address"
                },
                adminPhone: {
                    required: "Please provide a phone"
                },
                adminDob: {
                    required: "Please provide a Date of Birth"
                },
                adminGender: {
                    required: "Please provide a gender"
                },
                adminIdNumber: {
                    required: "Please provide a valid ID-number"
                },
                adminPassword: {
                    required: "Please provide a password",
                    minlength: "Your password must be at least 5 characters long"
                },

            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function (form) {
                var fData = $("#userForm").serializeArray();
                if (installBusy(true) == false) return false;

                $.post("Install?page=Basic", fData, (response) => {
                    try {
                        var m = JSON.parse(response);
                        if (m["status"] == "OK") {
                            MessageBox(m["messages"], false);
                            installSuccessful(false);
//                            window.location.href="Install";
                        }
                        else if (m["status"] == "Error") {
                            MessageBox(m["messages"], true);
                            MessageBox(m["errors"], true);
                        }else {
                            showStatusDialog(response, "Error. Contact your administrator");
                            installBusy(false);
                        }
                    } catch (e) {
                        installBusy(false);
                        showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
                    }
                });
            }
        });

    }

</script>
