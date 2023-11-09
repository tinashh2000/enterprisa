<?php
namespace Helpers;

use Api\CPersonEntity;
class PersonEntityHelper {
    static function itemsToOptions($entity)
    {
        if ($types = CPersonEntity::getItems($entity)) {

            foreach ($types as $type)
                echo "<option value='{$type['value']}'>{$type['title']}</option>";

        }
    }

}

