<?php
namespace Api;

class CSimpleCrypt {
    static function encrypt($str) {
        return base64_encode(strrev(base64_encode($str)));
    }

    static function decrypt($str) {
        return base64_decode(strrev(base64_decode($str)));
    }
}