<?php

use Ffw\Crypt\CCrypt8;
use Helpers\HtmlHelper;
use Api\Mt;
use Api\Users\CurrentUser;

HtmlHelper::addJsFile("Assistant/Js/Person.js");
?>
            <form id="newPersonForm" onsubmit="return false" method="post">
                <input name="r" type="hidden" value="create"/>
                <input name="id" type="hidden" value="0"/>
                <input name="lastModifiedDate" type="hidden" value="" />
                <input name="flags" type="hidden" value="0"/>

<?php
$personRequirements = array("name");
require("PersonTemplate.php")
?>
            </form>
<?php HtmlHelper::addJsFile("Assets/js/countries-select2.js") ?>
