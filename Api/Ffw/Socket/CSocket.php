<?php
namespace Ffw\Socket;

class CSocket
{

    static function getIPAddrVersion($address) {

        $v6 = preg_match("/^[0-9a-f]{1,4}:([0-9a-f]{0,4}:){1,6}[0-9a-f]{1,4}$/", $address);
        $v4 = preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $address);

        if ($v6 != 0)
            return 6;
        elseif ($v4 != 0)
            return 4;
        else
            return $address== "::1" ? 6 : (null);
    }
}
