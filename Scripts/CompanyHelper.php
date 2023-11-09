<?php
namespace Helpers;

use Api\Mt;
use Api\Users\CUser;
use Companies\CCompany;
use Helpers\PersonHelper;

class CompanyHelper
{
    static function fromPost()
    {
        if (isset($_POST['taxNumber']) && isset($_POST['extraDetails']) && isset($_POST['companyFlags']) && isset($_POST['companyNotes'])) {
            if ($person = PersonHelper::fromPost()) {
                $flags = $_POST['companyFlags'];
                $company = new CCompany($person, $_POST['taxNumber'], $_POST['extraDetails'], $_POST['companyNotes'], $flags);
                $company->id = Mt::getPostVarZ('companyId');
                return $company;
            }
        }

        print_r($_POST);
        echo "Error on " . __FILE__;
        return null;
    }
}