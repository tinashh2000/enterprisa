<?php

namespace TourismPod;

use Api\CCategory;
use Api\CDestination;
use Api\CTag;
use Api\CVehicle;
use Modules\CModule;
use Helpers\HtmlHelper;
use Api\Mt;
use Api\CSlide;
use Api\CPrivilege;
require_once(Mt::$appDir . "/Api/Bootstrap.php");
if (!CPrivilege::isAdmin())
    die(header("Location:404.php"));
CTag::init(true);
CCategory::init(true);
CSlide::init(true);
CVehicle::init(true);
CDestination::init(true);