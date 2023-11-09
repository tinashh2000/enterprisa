<?php
namespace Helpers;

use Api\Mt;
use Api\CPerson;
use http\Client\Curl\User;
use Helpers\UserHelper;

class PersonHelper
{
    static function fromPost($options = null)
    {
        $user = null;
        if (isset($_POST['personId']) && isset($_POST['name']) && isset($_POST['email']) && isset($_POST['phone']) && isset($_POST['address']) && isset($_POST['city']) && isset($_POST['country'])) {
            $user = UserHelper::fromPost();
            if ($options == null || (isset($options['needsUser']) && $user )) {
                $flags = Mt::getGetVarN('flags');

                $categories = Mt::getPostVarN('categories');
                $attributes = Mt::getPostVarN('attributes');

                $person = new CPerson($_POST['name'], $_POST['email'], Mt::getPostVarZ('gender'), Mt::getPostVarZ('maritalStatus'), $_POST['phone'], Mt::getPostVarN("type"), $categories, $_POST['address'], $_POST['city'], $_POST['country'], Mt::getPostVarN('personNotes'), $flags);
                $person->id = $_POST['personId'];
                $person->uid = $_POST['uid'];
                $person->dob = Mt::getPostVarZ("dob", "1800-05-11");
                $person->mobilePhone = Mt::getPostVarN('mobilePhone');
                $person->website = Mt::getPostVarN('website');
                $person->fax = Mt::getPostVarN('fax');
                $person->idNumber = Mt::getPostVarN("idNumber");
                $person->postalCode = Mt::getPostVarN('postalCode');
                $person->visibility = Mt::getPostVarZ('visibility', "*");
                $person->likes = Mt::getPostVarN("likes");
                $person->attributes = $attributes;
                $person->user = $user;

                return $person;
            } else {
                echo "Options error";
            }
        } else {
            echo "Missing stuff";
        }
        echo "Error on " . __FILE__;
        print_r($_POST);
        return null;
    }
}