<?php

use Ffw\Status\CStatus;
use Api\ServiceClass;

$docext = array("doc", "docx", "rtf");
$picext = array("jpg", "png");
const UPLOAD_IMAGE_DONT_CONVERT = 128;

function make_thumb($src, $dest, $desired_width)
{

    try {
        if ($src == $dest) return;

        $ext = pathinfo($src);
        $ext = strtolower($ext["extension"]);

        if ($ext == "svg") {
            if (file_exists($dest))
                unlink($dest);

            if (!file_exists($dest)) {
                copy($src, $dest);
            }
            return;
        }

        $source_image = $ext == 'jpg' ? @imagecreatefromjpeg($src) : @imagecreatefrompng($src);
        if (!$source_image) throw new Exception("Invalid file format");
        $width = imagesx($source_image);
        $height = imagesy($source_image);

        if ($desired_width > $width) $desired_width = $width;

        /* find the "desired height" of this thumbnail, relative to the desired width  */
        $desired_height = floor($height * ($desired_width / $width));

        /* create a new, "virtual" image */
        $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

        /* copy source image at a resized size */
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

        if (file_exists($dest))
            unlink($dest);
        /* create the physical thumbnail image to its destination */
        ($ext == 'jpg') ? imagejpeg($virtual_image, $dest) : imagepng($virtual_image, $dest);

        if (!file_exists($dest)) {
            copy($src, $dest);
        }
    } catch (\Exception $e) {
        return CStatus::jsonError("Could not make a thumbnail " . $e->getMessage());
    }
}

function convert_image($src, $targetFormat = 'jpg', $bDelSource = false)
{

    try {
        $picext = array("jpg", "png");
        /* read the source image */
        $pathinf = pathinfo($src);
        $ext = strtolower($pathinf["extension"]);
        if ($ext == $targetFormat) return $src;
        if (!in_array($ext, $picext)) {
            return false;
        }

        $dest = $pathinf["dirname"] . "/" . $pathinf["filename"] . "." . $targetFormat;
        $source_image = $ext == "jpg" ? imagecreatefromjpeg($src) : imagecreatefrompng($src);
        $width = imagesx($source_image);
        $height = imagesy($source_image);
        $desired_width = $width;

        /* find the "desired height" of this thumbnail, relative to the desired width  */
        $desired_height = floor($height * ($desired_width / $width));

        /* create a new, "virtual" image */
        $virtual_image = imagecreatetruecolor($desired_width, $desired_height);
        imagefill($virtual_image, 0, 0, 0xffffff);

        /* copy source image at a resized size */
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

        if (file_exists($dest)) unlink($dest);

        /* create the physical thumbnail image to its destination */
        ($targetFormat == 'jpg') ? imagejpeg($virtual_image, $dest) : imagepng($virtual_image, $dest);
        if (!file_exists($dest)) {
            copy($src, $dest);
        }

        if ($bDelSource) {
            unlink($src);
        }
        return $dest;
    } catch (\Exception $e) {
        return false;
    }
}

function uploadImage($folder, $prefix, $sizelimit, $filevar, $flags = 0) {

    global $picext;
    $picext = array("jpg", "png", "svg");

    $newname = "";
    $c = 0;

    if (isset($_FILES[$filevar]['name']) && ($file = trim($_FILES[$filevar]['name']) != "")) {
        $fx = $_FILES[$filevar];
        $file = trim($fx['name']);
        if (!empty($fx) && $file != "") {
            if ($fx['error'] != 0) {
                return false;
            }

            $filename = basename($fx['name']);
            $ext = strtolower(pathinfo($filename)["extension"]);

            if ($ext == 1) die("Invalid Filename " . $filename);

            if (in_array($ext, $picext)) {
                if ($fx["size"] > $sizelimit)
                    return CStatus::jsonError("File too big : $filename");
                $dt = gmdate("YmdHis", strtotime("now"));
                $newip = $dt . rand(11111, 99999) . str_replace(array(".", " ", "\\", "/", "<", ">", ":"), "", $_SERVER['REMOTE_ADDR']);
                $cc = 0;

                $sFolderLen = strlen($folder);
                if ($sFolderLen > 0 && substr($folder, $sFolderLen - 1, 1) == "/") {
                    $folder = substr($folder, 0, $sFolderLen - 1);
                }

                do {
                    $thefile = "{$prefix}{$newip}" . dechex(crc32($filename) + rand(11111111, 99999999)) . "{$cc}.{$ext}";
                    $newname = "{$folder}/$thefile";
                    $cc++;
                } while (file_exists($newname) && $cc < 100);

                if (!file_exists($newname)) {
                    if ((move_uploaded_file($fx['tmp_name'], $newname))) {
                        @chmod($newname, 0777);
                        if ($ext == "svg" || $flags & \Api\ServiceClass::NO_IMAGE_CONVERT) {
                            $fn = $newname;
                        } else {
                            $fn = convert_image($newname, "jpg", true);
                        }
                        $pi = pathinfo($fn);
                        return $pi["basename"];
                    } else {
                        if (file_exists($fx['tmp_name'])) {
                            if (copy($fx['tmp_name'], $newname)) return true;
                        } else {
                            return false;
                        }
                        return CStatus::jsonError("File upload error : $newname .... {$fx['tmp_name']}");
                    }
                } else {
                    return CStatus::jsonError("Error: File " . $_FILES["uploaded_file"]["name"] . " already exists");
                }
            } else
                return CStatus::jsonError("File not uploaded :" . $filename);
        } else
            return CStatus::jsonError("No Files Uploaded");
    }
    return CStatus::jsonError("File not uploaded");
}

function uploadFile($folder, $prefix, $sizeLimit = 10485760, $fileVar='file')
{
    if (isset($_FILES[$fileVar]['name']) && ($file = trim($_FILES[$fileVar]['name']) != "")) {
        $fx = $_FILES[$fileVar];
        $file = trim($fx['name']);


        if (empty($fx) || $file == "")
            return CStatus::jsonError("Bad filename. File not uploaded");


        if ($fx['error'] != 0) {
            return CStatus::jsonError("File upload failed");
        }

        $filename = basename($fx['name']);
        $ext = "fil";

        if ($fx["size"] > $sizeLimit)
            return CStatus::jsonError("File too big : $filename");

        $dt = gmdate("YmdHis", strtotime("now"));
        $newip = $dt . rand(11111, 99999) . str_replace(array(".", " ", "\\", "/", "<", ">", ":"), "", $_SERVER['REMOTE_ADDR']);
        $cc = 0;

        $sFolderLen = strlen($folder);
        if ($sFolderLen > 0 && substr($folder, $sFolderLen - 1, 1) == "/") {
            $folder = substr($folder, 0, $sFolderLen - 1);
        }

        do {
            $thefile = "{$prefix}{$newip}" . dechex(crc32($filename) + rand(11111111, 99999999)) . "{$cc}.{$ext}";
            $newname = "{$folder}/$thefile";
            $cc++;
        } while (file_exists($newname) && $cc < 100);

//        echo "LineUF " . __LINE__;

        if (!file_exists($newname)) {
//            echo "LineUF " . __LINE__;

            try {
//                echo $fx['tmp_name'] . " ::: $newname LineUF " . __LINE__;
                if ((move_uploaded_file($fx['tmp_name'], $newname))) {
//                    echo "LineUF " . __LINE__;
                    @chmod($newname, 0775);
                    $fn = $newname;
                    $pi = pathinfo($fn);
                    return $pi["basename"];
                } else {
//                    echo "LineUF " . __LINE__;
                    if (file_exists($fx['tmp_name'])) {
                        if (copy($fx['tmp_name'], $newname)) return true;
                    } else {
                        return false;
                    }
                    return CStatus::jsonError("File upload error : $newname .... {$fx['tmp_name']}");
                }
//                echo "LineUF " . __LINE__;

            } catch(\Exception $e) {
//                echo "LineUF " . __LINE__;
                return CStatus::jsonError("File upload error");
            }
        } else
            return CStatus::jsonError("Error: File " . $_FILES["uploaded_file"]["name"] . " already exists");


//        echo "LineUF " . __LINE__;

    }
//    echo "LineUF " . __LINE__;

    return CStatus::jsonError("File not uploaded");
}

function uploadPic($absDir, $prefix, $maxSize, $fileNameIndex, $flags = 0)
{
    $fileName = uploadImage($absDir, $prefix, 700000, $fileNameIndex, $flags);
    $returnArray = array();
    if ($fileName != "") {
        $returnArray['imagePath'] = "$fileName";
        if ($flags & ServiceClass::MAKE_THUMBNAIL) {
            $basefName = pathinfo($fileName)["filename"];
            $iconFile = $basefName . "_t." . pathinfo($fileName)["extension"];
            make_thumb("{$absDir}/{$fileName}", "{$absDir}/{$iconFile}", 300);
            $returnArray['iconPath'] = "$iconFile";
        }
        return $returnArray;
    }
    return false;
}

?>
