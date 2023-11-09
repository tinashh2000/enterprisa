<?php
namespace Api;

use Api\ServiceClass;
use Ffw\Status\CStatus;
abstract class PersonServiceClass extends ServiceClass
{
    var $person;
    var $personUid;

    function processPerson()
    {
        try {
            if (!$this->personUid || $this->personUid == "") {

                if ($this->person != null) {
                    if ($this->person->exists()) {
                        return CStatus::jsonError("Person/ID record already exists");
                    }

                    CStatus::pushSettings(0);
                    if ($this->person->uid != "") {
                        $this->personUid = $this->person->uid;
                    } else {
                        CPerson::temporaryElevate();
                        if (!$this->person->create()) {
                            CStatus::popSettings();
                            return CStatus::jsonError(CStatus::popError() ?? "Error while processing person's record");
                        }
                        $this->personUid = $this->person->uid;
                    }
                    CStatus::popSettings();
                    return true;
                } else {
                    return CStatus::jsonError("Missing person's record");
                }
            }
            return true;
        } catch(\Exception $e) {
            return CStatus::jsonError("Error occure while processing person's record");
        }
        return false;
    }
}
