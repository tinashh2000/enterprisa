<?php


namespace Api;


use Api\Users\CurrentUser;
use Ffw\Crypt\CCrypt8;

class CBreachCrypt extends CCrypt8 {
static protected $scrambleArray=array(65, 204, 148, 131, 232, 56, 60, 149, 14, 44, 78, 113, 145, 89, 168, 160, 47, 54, 112, 118, 127, 26, 95, 122, 36, 244, 189, 90, 97, 184, 139, 40, 208, 251, 28, 73, 229, 110, 69, 29, 179, 241, 181, 45, 240, 111, 119, 248, 62, 63, 246, 35, 59, 70, 221, 214, 129, 134, 180, 92, 34, 9, 67, 182, 206, 212, 165, 4, 53, 72, 250, 80, 253, 225, 81, 102, 49, 11, 82, 115, 48, 177, 201, 205, 154, 234, 71, 213, 123, 120, 157, 99, 233, 173, 196, 167, 132, 247, 55, 130, 220, 41, 94, 1, 19, 135, 57, 142, 88, 51, 211, 243, 245, 114, 219, 31, 121, 103, 222, 126, 223, 76, 7, 37, 107, 155, 224, 152, 199, 5, 197, 98, 33, 252, 174, 237, 194, 188, 46, 146, 171, 13, 218, 159, 156, 150, 254, 163, 8, 15, 22, 138, 216, 255, 195, 17, 101, 23, 83, 58, 43, 207, 86, 136, 124, 162, 84, 203, 191, 24, 200, 215, 27, 30, 235, 64, 242, 38, 3, 66, 175, 193, 12, 147, 77, 210, 192, 96, 178, 238, 239, 202, 158, 164, 125, 61, 140, 79, 10, 153, 161, 91, 16, 2, 106, 190, 249, 185, 128, 141, 20, 18, 87, 21, 169, 74, 116, 52, 143, 172, 32, 176, 68, 228, 25, 100, 151, 230, 217, 75, 209, 42, 104, 109, 144, 6, 105, 226, 198, 183, 85, 133, 93, 50, 187, 117, 236, 227, 186, 137, 108, 231, 0, 166, 170, 39);
}
class CBreach
{
    static function create($module, $description, $data) {
        json_encode(["user"=>CurrentUser::getUsername(), "date" => gmdate("Y-m-d H:i:s", strtotime("now")), "location" =>["ipAddress" =>$_SERVER['REMOTE_ADDR'], "location" => $_SERVER['REMOTE_HOST'], 'port'=>$_SERVER['REMOTE_PORT']]]);
    }
}