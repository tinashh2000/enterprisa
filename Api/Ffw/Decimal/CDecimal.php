<?php

namespace Ffw\Decimal;

class CDecimal {

    static function currency($num, $scale=2) {
        if (array_search(substr($num,0,1), ["$", "£"]) !== FALSE)
            $num = trim(substr($num,1));

        if ($num === null || !is_numeric($num)) $num = 0;
        return @bcadd($num, 0, $scale);
    }

    static function decimal($num, $scale=5) {
        if ($num === null || !is_numeric($num)) $num = 0;
        return @bcadd($num, 0, $scale);
    }

    static function double($num, $scale=5) {
        if ($num === null || !is_numeric($num)) $num = 0;
        return @bcadd($num, '0', $scale);
    }

    static function integer($num) {
        if ($num === null || !is_numeric($num)) $num = 0;
        return @bcadd($num, '0', 0);
    }

    static function integer64($num) {
        if ($num === null || !is_numeric($num)) $num = 0;
        return @bcadd($num, '0', 0);
    }

//Addition

    static function add($n1, $n2, $scale=null) {
        return bcadd($n1, $n2, $scale ==null ? bcscale() : $scale);
    }

    static function add128($n1, $n2, $scale=null) {
        return bcadd($n1, $n2, $scale ==null ? bcscale() : $scale);
    }

    static function add64($n1, $n2, $scale=null) {
        return bcadd($n1, $n2, $scale ==null ? bcscale() : $scale);
    }

    static function add128i($n1, $n2) {
        return self::integer($n1) + self::integer($n2);
    }

    static function add64i($n1, $n2) {
        return self::integer64($n1) + self::integer64($n2);
    }


//Subtract

    static function sub($n1, $n2, $scale=null) {
        return bcsub($n1, $n2, $scale ==null ? bcscale() : $scale);
    }

    static function sub128($n1, $n2, $scale=null) {
        return bcsub($n1, $n2, $scale ==null ? bcscale() : $scale);
    }

    static function sub64($n1, $n2, $scale=null) {
        return bcsub($n1, $n2, $scale ==null ? bcscale() : $scale);
    }

    static function sub128i($n1, $n2) {
        return self::integer($n1) + self::integer($n2);
    }

    static function sub64i($n1, $n2) {
        return self::integer64($n1) + self::integer64($n2);
    }

//Multiplication

    static function mul($n1, $n2, $scale=null) {
        return bcmul(self::decimal($n1), self::decimal($n2), $scale ==null ? bcscale() : $scale);
    }

    static function mul128($n1, $n2, $scale=null) {
        return bcmul(self::decimal($n1), self::decimal($n2), $scale ==null ? bcscale() : $scale);
    }

    static function mul64($n1, $n2, $scale=null) {
        return bcmul(self::double($n1), self::double($n2), $scale ==null ? bcscale() : $scale);
    }

    static function mul128i($n1, $n2) {
        return self::integer($n1) * self::integer($n2);
    }

    static function mul64i($n1, $n2) {
        return self::integer64($n1) * self::integer64($n2);
    }

//Division

    static function div($n1, $n2, $scale=null) {
        return bcdiv(self::decimal($n1), self::decimal($n2), $scale ==null ? bcscale() : $scale);
    }

    static function div128($n1, $n2, $scale=null) {
        return bcdiv(self::decimal($n1), self::decimal($n2), $scale ==null ? bcscale() : $scale);
    }

    static function div64($n1, $n2, $scale=null) {
        return bcdiv(self::double($n1), self::double($n2), $scale ==null ? bcscale() : $scale);
    }

    static function div128i($n1, $n2) {
        return self::integer($n1) / self::integer($n2);
    }

    static function div64i($n1, $n2) {
        return self::integer64($n1) / self::integer64($n2);
    }

}