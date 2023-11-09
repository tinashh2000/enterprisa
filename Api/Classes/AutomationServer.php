<?php


namespace Api\Classes;


abstract class AutomationServer
{
    abstract static function getCapabilities();
    abstract static function execute($token, $entity, $function, $data);
    abstract static function requestToken($entity, $data);
}