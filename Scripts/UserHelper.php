<?php
namespace Helpers;
use Api\CPerson;
use Api\Mt;
use Api\Users\CUser;

class UserHelper
{

    static function fromPost() {
        if (isset($_POST['username']) && (trim($_POST['username']) != "") && isset($_POST['password']) && isset($_POST['privilegesList']) && isset($_POST['roles']) && isset($_POST['flags']) && isset($_POST['comments'])) {
			$priv = 0;  //(intval(Mt::getPostVarZ('rolesList')) == 1 && CPrivilege::isAdmin()) ? CPrivilege::ROLE_ADMINISTRATOR : 0;
			$user = new CUser($_POST['username'], $_POST['password'], $priv, $_POST['privilegesList'], $_POST['roles']);
			$user->id = Mt::getPostVarZ('userId');
			$user->personUid = Mt::getPostVarZ('personUid');
			$user->comments = $_POST['comments'];
			$user->flags = 0;
			return $user;
        }
        return null;
    }
}