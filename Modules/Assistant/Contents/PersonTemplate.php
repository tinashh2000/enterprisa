<?php

use Api\Mt;
use Api\Users\CurrentUser;
use Api\CPersonEntity;
use Helpers\PersonEntityHelper;
use Api\CPerson;
use Api\CPrivilege;
use Helpers\HtmlHelper;

if (!isset($personRequirements))
    $personRequirements = array("name", "gender", "marital", "email", "idNumber", "phone", "dob", "address", "city", "country");

$isAdmin = CPrivilege::isAdmin();
$notAPerson = (isset($notAPerson) && $notAPerson) || (in_array("isNotAPerson", $personRequirements));
HtmlHelper::addJsFile("Assets/js/countries-select2.js");
HtmlHelper::addJsFile("Assets/js/Person.js");
?>

<div class="person-container">

    <input type="hidden" name="personId" value="0"/>
    <input type="hidden" name="uid" value=""/>
    <input type="hidden" name="profile"/>
    <input type="hidden" name="address"/>
    <input type="hidden" name="visibility" value="*"/>

    <h2>Personal Details</h2>
    <div class="row">
        <div class="col-md-2 col-sm-12">
            <div class="form-group form-float">
                <div class="form-line">
                    <img class="user-picture m-1" name="pic" src='<?php echo Mt::$appRelDir; ?>/Assets/img/placeholder.svg'
                         height='100px' id="profilePicImg"/>
                    <input type="file" class="form-control" name="profilePic"
                           id="profilePicFile"<?php echo array_search("pic", $personRequirements) !== FALSE ? " required" : "" ?>>
                </div>
            </div>
        </div>

        <div class="col-md-10 col-12">
            <div class="col-12 col-sm-5 p-0 m-0">
                <label>Title</label>
                <div class="form-group input-group">
                    <input type="text" name='title' class="form-control" placeholder="Title">
                </div>
            </div>
            <div class="col-12 p-0 m-0">
                <label>Name</label>
                <div class="form-group input-group">
                    <input type="text" name='name' class="form-control" placeholder="Name" required>
                </div>
            </div>

            <div class="col-12 p-0 m-0">
                <label>Email <span class="pl-3" id="personSearchStatus"></span></label>
                <div class="form-group input-group">
                    <input type="email" id="personFormEmail" name='email' class="form-control"
                           placeholder="Email"<?php echo array_search("email", $personRequirements) !== FALSE ? " required" : "" ?>>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-sm-12 ">
            <label>Phone</label>
            <div class="form-group input-group">
                <input type="text" name='phone' class="form-control"
                       placeholder="Phone"<?php echo array_search("phone", $personRequirements) !== FALSE ? " required" : "" ?>>
            </div>
        </div>

        <div class="col-md-6 col-sm-12 ">
            <label>Phone 2</label>
            <div class="form-group input-group">
                <input type="text" name='mobilePhone' class="form-control" placeholder="Mobile Phone">
            </div>
        </div>
    </div>

    <?php
    if (!$notAPerson) {
        ?>
        <div class="row">
            <div class="col-6 col-sm-3 ">
                <label>Date Of Birth</label>
                <div class="form-group input-group">
                    <input type="date" name='dob' id="dateOfBirth" class="form-control"
                           placeholder="Date of Birth"
                           value="1800-01-01"<?php echo array_search("dob", $personRequirements) !== FALSE ? " required" : "" ?>>
                </div>
            </div>

            <div class="col-6 col-sm-3 ">
                <label>ID Number</label>
                <div class="form-group input-group">
                    <input type="text" name='idNumber' class="form-control"
                           placeholder="Id-Number"<?php echo array_search("idNumber", $personRequirements) !== FALSE ? " required" : "" ?>>
                </div>
            </div>


            <div class="form-group form-float col-sm-3 col-6 ">
                <div class="form-line">
                    <label class="form-label">Gender</label>
                    <select class="form-control select2" id="mGenderPicker" name="gender"
                            data-placeholder="Select a Gender"
                            placeholder="Select a Gender"<?php echo array_search("gender", $personRequirements) !== FALSE ? " required" : "" ?>>
                        <option value=""></option>
                        <option value="<?php echo CPerson::GENDER_MALE ?>">Male</option>
                        <option value="<?php echo CPerson::GENDER_FEMALE ?>">Female</option>
                    </select>
                </div>
            </div>

            <div class="form-group form-float col-sm-3 col-6 ">
                <div class="form-line">
                    <label class="form-label">Marital Status</label>
                    <div class="">
                        <select class="select2" name="maritalStatus"
                                data-placeholder="Marital Status"<?php echo array_search("marital", $personRequirements) !== FALSE ? " required" : "" ?>>
                            <option value=''></option>
                            <option value='<?php echo CPerson::MARITAL_STATUS_SINGLE ?>'>Single</option>
                            <option value='<?php echo CPerson::MARITAL_STATUS_MARRIED ?>'>Married</option>
                            <option value='<?php echo CPerson::MARITAL_STATUS_DIVORCED ?>'>Divorced</option>
                            <option value='<?php echo CPerson::MARITAL_STATUS_WIDOW ?>'>Widow/Widower</option>
                            <option value='<?php echo CPerson::MARITAL_STATUS_OTHER ?>'>Other</option>
                        </select>
                    </div>
                </div>
            </div>

        </div>

    <?php } ?>

    <h2 class="mt-5">Contact Details</h2>

    <div class="row">
        <div class="col-md-6 col-sm-12 ">
            <label>Website (URL)</label>
            <div class="form-group input-group">
                <input type="text" name='website' class="form-control"
                       placeholder="Website"<?php echo array_search("website", $personRequirements) !== FALSE ? " required" : "" ?>>
            </div>
        </div>
        <?php if (!$notAPerson) { ?>
            <div class="col-md-6 col-sm-12 ">
                <label>Profession</label>
                <div class="form-group input-group">
                    <input name="profession" class="form-control"
                           placeholder="Profession"<?php echo array_search("profession", $personRequirements) !== FALSE ? " required" : "" ?>/>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php if (!$notAPerson) { ?>
        <div class="row">
            <div class="col-md-6 col-sm-12 ">
                <label>Education</label>
                <div class="form-group input-group">
                    <input name="education" data-entity="profile" class="form-control"
                           placeholder="Education"<?php echo array_search("education", $personRequirements) !== FALSE ? " required" : "" ?>/>
                </div>
            </div>

            <div class="col-md-6 col-sm-12 ">
                <label>Skills</label>
                <div class="form-group input-group">
                    <input name="skills" data-entity="profile" class="form-control"
                           placeholder="Skills"<?php echo array_search("skills", $personRequirements) !== FALSE ? " required" : "" ?>/>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-sm-12 ">
                <label>Visibility</label>
                <div class="form-group input-group">
                    <select class="select2" multiple name="visibilityList">
                        <option value="*">Everyone</option>
                        <option value="<?php echo CurrentUser::getUsername() ?>">Me Only</option>
                    </select>
                </div>
            </div>
        </div>
    <?php } ?>
    <div class="row">
        <div class="col-12 col-md-6">
            <label>Address 1</label>
            <div class="form-group input-group">
                <input type="text" name='address1' class="form-control"
                       placeholder="Address"<?php echo array_search("address", $personRequirements) !== FALSE ? " required" : "" ?>/>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <label>Address 2</label>
            <div class="form-group input-group">
                <input type="text" name='address2' class="form-control" placeholder="Address"/>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-sm-12">
            <label>City</label>
            <div class="form-group input-group">
                <input type="text" name='city' class="form-control"
                       placeholder="City"<?php echo array_search("city", $personRequirements) !== FALSE ? " required" : "" ?>>
            </div>
        </div>

        <div class="col-md-6 col-12 ">
            <label class="form-label">Country</label>
            <div class="form-group input-group">
                <select id="mCountryPicker" class="select2"
                        name="country"<?php echo array_search("country", $personRequirements) !== FALSE ? " required" : "" ?>></select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-sm-12 ">
            <label>Postal Code</label>
            <div class="form-group input-group">
                <input type="text" name='postalCode' class="form-control"
                       placeholder="Postal Code"<?php echo array_search("postal", $personRequirements) !== FALSE ? " required" : "" ?>>
            </div>
        </div>

        <div class="form-group form-float col-md-6 col-sm-12">
            <div class="form-line">
                <label>Fax</label>
                <div class="form-group input-group">
                    <input type="text" name='fax' class="form-control" placeholder="Fax">
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <label>Notes</label>
            <div class="form-group input-group">
                <textarea type="text" name='personNotes' class="form-control" placeholder="Notes"></textarea>
            </div>
        </div>
    </div>


    <?php if (!$notAPerson && $isAdmin) { ?>
        <h2>Administrative Details</h2>
        <div class="row">
            <div class="col-md-4 col-sm-12 ">
                <label>Type</label>
                <div class="form-group input-group">
                    <input type="hidden" name="type"/>
                    <select class="select2" name="typeList" id="typePicker" multiple>
                        <?php
                        PersonEntityHelper::itemsToOptions("Types");
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-md-4 col-sm-12 ">
                <label>Categories</label>
                <div class="form-group input-group">
                    <input type="hidden" name="categories"/>
                    <select class="select2" data-tags="true" name="categoriesList" multiple>
                        <?php
                        PersonEntityHelper::itemsToOptions("Categories");
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-md-4 col-sm-12 ">
                <label>Attributes</label>
                <div class="form-group input-group">
                    <input type="hidden" name="attributes"/>
                    <select class="select2" data-tags="true" name="attributesList" multiple>
                        <?php
                        PersonEntityHelper::itemsToOptions("Attributes");
                        ?>
                    </select>
                </div>
            </div>
        </div>

    <?php } ?>

</div>