<?php

namespace Api\Install;

use Api\CPrivilege;
use Api\AppConfig;
use Api\AppDB;
use Api\Mt;
use Api\Session\CSession;
use Api\Users\CUser;
use Api\Users\CurrentUser;
use Accounta\Accounts\CAccount;
use Accounta\Accounts\CTransaction;
use Currencies\CCurrency;
use Ffw\Database\Sqlite\sqliteDB;
use Accounta\CAccounta;
use Accounta\Audit\CAudit;
use Modules\CModule;
use Hira\CDeduction;
use Hira\CDepartment;
use Hira\CEmployee;
use Hira\CPayroll;
use Hira\CProfession;

require_once(__DIR__ . "/../../Bootstrap.php");


class CSample
{

    static function initCurrencies()
    {
        $c = new CCurrency("USD", "1.0", CCurrency::FLAG_DEFAULT, "US Dollar");
        if (!$c->exists()) $c->create();

        $c = new CCurrency("ZWD", "40", 0, "ZWD");
        if (!$c->exists())$c->create();

        $c = new CCurrency("Rand", "10", 0, "Rand");
        if (!$c->exists())$c->create();

        $c = new CCurrency("Pound", "0.86", 0, "Pound");
        if (!$c->exists())$c->create();

        $c = new CCurrency("Pula", "12", 0, "Pula");
        if (!$c->exists())$c->create();

        $c = new CCurrency("Yen", "5", 0, "Yen");
        if (!$c->exists())$c->create();

        $c = new CCurrency("RMB", "8", 0, "RMB");
        if (!$c->exists())$c->create();
    }

    static function initEmployees() {
        $dummyUsers = array("Tendai", "Tapiwa", "Mark", "David", "Tinashe", "Tatenda", "Jonathan", "Emmanuel", "Kudakwashe", "George", "Donald", "Simbarashe", "Obert", "Nelson", "Tichaona", "Andy", "Samantha", "Susan", "Spiwe", "Eunice", "Allen", "Paul", "William", "Tracy", "Catherine", "Jeffrey", "Tongai");
        $dummySurnames = array("Muti", "Chibaya", "Chitsa", "Nyamayaro", "Chibwe", "Zimba", "Mpofu", "Dube", "Moyo", "Ndiweni", "Chanetsa", "Chipara", "Chatunga", "Mhou", "Jena", "Zuva", "Goredema");
        $employee = new CEmployee("Tinashe Mutandagayi", "0779489299", "tinashh2000@gmail.com", "43-13123123", 1, 1000, 0, 21, 1);
        for ($c = 0; $c < 400; $c++) {
            $d = rand(0, count($dummyUsers) - 1);
            $e = rand(0, count($dummySurnames) - 1);
            $employee->email = strtolower("{$dummyUsers[$d]}.{$dummySurnames[$e]}@enterprisa.co.zw");
            $employee->name = "{$dummyUsers[$d]} {$dummySurnames[$e]}";
            $employee->comments = $employee->name;
            $employee->phone = "07787824$c$d$e";
            $employee->idNumber = "63-273498$e$d" . ($c + $e);
            $employee->id  = 0;
            if ($employee->exists()) continue;
            $employee->create();
        }
    }
    static function initUsers()
    {
        $user = new CUser(Mt::$defaultName, "Administrator", 0, "140 Payroll Street, Harare, Zimbabwe", "0777777777", Mt::$defaultUser, "", 0, 1, "1988-05-11");
		$user->username = "tinashh2000";
        $user->email = "tinashh2000@gmail.com";
        $user->name = "Tinashe Mutandagayi";
        $user->comments = "Tinashe Mutandagayi";
        $user->phone = "07787824551112";
        $user->idNumber = "63-273498112232";
        $user->password = "admin";
        if (!$user->exists()) $user->create();

        $dummyUsers = array("Tendai", "Tapiwa", "Mark", "David", "Tinashe", "Tatenda", "Jonathan", "Emmanuel", "Kudakwashe", "George", "Donald", "Simbarashe", "Obert", "Nelson", "Tichaona", "Andy", "Samantha", "Susan", "Spiwe", "Eunice", "Allen", "Paul", "William", "Tracy", "Catherine", "Jeffrey", "Tongai");
        $dummySurnames = array("Muti", "Chibaya", "Chitsa", "Nyamayaro", "Chibwe", "Zimba", "Mpofu", "Dube", "Moyo", "Ndiweni", "Chanetsa", "Chipara", "Chatunga", "Mhou", "Jena", "Zuva", "Goredema");

        $dummyProfessions = array("Accountant", "IT Administrator", "Mechanic", "Sales Agent", "Receptionist", "Manager", "Director", "Computer Programmer");

        for ($c = 0; $c < 400; $c++) {
            $d = rand(0, count($dummyUsers) - 1);
            $e = rand(0, count($dummySurnames) - 1);
            $f = rand(0, count($dummyProfessions) - 1);
            $user->email = strtolower("{$dummyUsers[$d]}.{$dummySurnames[$e]}@enterprisa.co.zw");
            $user->username = strtolower("{$dummyUsers[$d]}.{$dummySurnames[$e]}");
            $user->profession = $dummyProfessions[$f];
            $user->profile = "";
            $user->name = "{$dummyUsers[$d]} {$dummySurnames[$e]}";
            $user->comments = $user->name;
            $user->phone = "07787824{$c}{$d}{$e}";
            $user->idNumber = "63-273498{$e}{$d}" . ($c+$e);
            $user->id = 0;
            if (!$user->exists()) {
                if (!$user->create()) return;
            }
        }
    }

    static function initAccounts($useSqlite = false)
    {
        return;
        foreach(CAccount::$assets as $k=>$i) {
            $c = new CAccount("Income", "Income", "Income account", "", "1", "1", "0.0", "0", "", "");
            $c->companyId = 1;
            if (!$c->exists()) $c->create();
        }
//        $c = new CAccount("Expense", "Expense", "Expense account", "","1", "2", "1234.0", "0", "", "");
//        $c->companyId = 1;
//        if (!$c->exists()) $c->create();

        $token1 = CAccount::open(1);
        $token2 = CAccount::open(2);

        $expensesLen = count(CAccount::$expenses);
        $incomeLen = count(CAccount::$income);

        if ($useSqlite) {

            $file1 = CSession::getValue("Accounta_Account_1", "file");
            $file2 = CSession::getValue("Accounta_Account_2", "file");

            $dbFil1 = CAccount::$defaultPath . "/{$file1}";
            $dbFil2 = CAccount::$defaultPath . "/{$file2}";

            $sqliteDB1 = new SqliteDB($dbFil1);
            $sqliteDB2 = new SqliteDB($dbFil2);
        }
        $username = CurrentUser::getUsername();

        for ($i = 0; $i < 1000; $i++) {
            $dt = gmdate("Y-m-d H:i:s", rand(3600, 86300) + time() - 86400000 + ($i * 86400));
            $amount1 = rand(1, 12334345);
            $amount2 = rand(1, 12334345);

            $amount1 = $amount1 * (rand(0, 1) & 1 ? -1 : 1);
            $amount2 = $amount2 * (rand(0, 1) & 1 ? -1 : 1);

            $desc1 = $amount1 < 0 ? CAccount::$expenses[rand(0, $expensesLen - 1)] : CAccount::$income[rand(0, $incomeLen - 1)];
            $desc2 = $amount2 < 0 ? CAccount::$expenses[rand(0, $expensesLen - 1)] : CAccount::$income[rand(0, $incomeLen - 1)];
            if ($useSqlite) {
                $sqliteDB1->query("INSERT INTO records (dateTime, recordedDateTime,`name`, description, authority, amount, comments, flags, username) VALUES ('$dt', '$dt', '', '$desc1', '', '$amount1', '', 0, '$username')");
                $sqliteDB2->query("INSERT INTO records (dateTime, recordedDateTime,`name`, description, authority, amount, comments, flags, username) VALUES ('$dt', '$dt', '', '$desc2', '', '$amount2', '', 0, '$username')");
            } else {
                $c = new CTransaction($desc1, $amount1, "1", "", "1", $dt);
                $c->create($token1);
                $c = new CTransaction($desc2, $amount2, "2", "", "2", $dt);
                $c->create($token2);
            }
        }

        if ($useSqlite) {
            $sqliteDB1->close();
            $sqliteDB2->close();
        }
    }

    static function initDepartments() {
        $departments = array("Information Technology", "Accounts and Finance", "Audit", "Human Resources", "Security", "Management", "Sales", "Production", "Engineering", "Media and Advertising", "Legal", "Advisors", "Marketing", "Purchasing");
        foreach ($departments as $dept) {
            $x = new CDepartment($dept);
            if (!$x->exists()) $x->create();
        }
    }
    static function initSamples()
    {
        CModule::use_module("Currencies");
        CModule::use_module("Accounta");
        CModule::use_module("Hira");
        CModule::use_module("Assistant");

        self::initUsers();
        self::initCurrencies();;
        self::initAccounts();
        self::initDepartments();
        self::initEmployees();
        //initProfessions();
    }
}