<?php

namespace Ffw\Compression\Zip;

class CZip {

    static function ArchiveDirectory($source, $destination, $password="") {
        if (extension_loaded('zip') === true) {
            if (file_exists($source) === true) {
                $zip = new ZipArchive();


                if ($zip->open($destination, ZIPARCHIVE::CREATE) === true) {

                    if ($password!="") $zip->setPassword($password);

                    $source = realpath($source);

                    if (is_dir($source) === true) {

                        $iterator = new RecursiveDirectoryIterator($source);
                        $iterator->setFlags(FilesystemIterator::SKIP_DOTS);
                        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);

                        foreach ($files as $file) {
                            $file = realpath($file);

                            if (is_dir($file) === true) {
                                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                            } else if (is_file($file) === true) {
                                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                            }
                        }
                    } else if (is_file($source) === true) {
                        $zip->addFromString(basename($source), file_get_contents($source));
                    }
                }
                return $zip->close();
            }
        }
        return false;
    }
}