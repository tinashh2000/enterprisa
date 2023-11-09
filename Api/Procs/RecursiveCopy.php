<?php
function recursiveCopy($src,$dst, $mv = false) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recursiveCopy($src . '/' . $file,$dst . '/' . $file, $mv);
                if ($mv) {
                    rmdir($src . '/' . $file);
                }
            }
            else {
                if ($mv) {
                    if (!rename($src . '/' . $file,$dst . '/' . $file)) {
                        copy($src . '/' . $file,$dst . '/' . $file);
                        unlink($src . '/' . $file);
                    }
                }
                else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
    }
    closedir($dir);
}