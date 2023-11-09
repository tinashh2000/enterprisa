<?php
namespace Api\Users;
use Api\Session\CSession;

class CurrentUser {
    static function getFullname() {
        global $mtPrefix;
        return CSession::get("name");
    }

    static function getRegDate() {
        global $mtPrefix;
        return CSession::get("regdate");
    }

    static function getProfilePic() {
        global $mtPrefix;
        return "../res/tinface.jpg";
    }

    static function getUsername() {
        global $mtPrefix;
        return CSession::get("username");
    }

    static function getUid() {
        global $mtPrefix;
        return CSession::get("uid");
    }

    static function getPic() {
        global $mtPrefix;
        $pic = CSession::get("email");
        if (!$pic || !file_exists($pic)) {
            $pic = "img/avatar.png";
        }
        return $pic;
    }

    static function getEmail() {
        global $mtPrefix;
        return CSession::get("email");
    }

    static function getCurrentPrivatePath() {
        return CSession::get("privatePath");
    }
}