<?php

namespace Helpers;

use Api\Authentication\CAuth;
use Api\CPerson;
use Api\CPrivilege;
use Api\Mt;
use Api\Session\CSession;
use Modules\CModule;

class CMenu
{
    protected $title = "";
    protected $items;
    protected $link;
    protected $icon;


    function __construct($title = "", $link = "", $icon = "")
    {
        $this->items = array();
        $this->title = $title;
        $this->link = $link;
        $this->icon = $icon;
    }

    function addMenuX($mnu, $pos = null)
    {
        if ($mnu === null) return;
        if ($pos === null || !is_int($pos))
            array_push($this->items, $mnu);
        else {
            array_splice($this->items, $pos, 0, 0);
            $this->items[$pos] = $mnu;
        }
        return $mnu;
    }

    function addMenu($title, $pos = null, $link = null, $icon = "feather icon-home")
    {
        $mnu = new CMenu($title, $link, $icon);

        if ($pos === null || !is_int($pos)) {
            array_push($this->items, $mnu);
        } else {
            array_splice($this->items, $pos, 0, 0);
            $this->items[$pos] = $mnu;
        }
        return $mnu;
    }

    function addItem($icon, $title, $link = "")
    {
        array_push($this->items, array($icon, $title, $link == "" ? "" : Mt::$appRelDir . "/" . $link));
    }

    function getTitle()
    {
        return $this->title;
    }

    function getItems()
    {
        return $this->items;
    }

    function getIcon()
    {
        return $this->icon;
    }

    function close()
    {
    }
}
