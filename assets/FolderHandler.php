<?php

class FolderHandler {

    private $HOME_DIR = "deregister-attachment";

    private $home;
    private $length = 128;

    /**
     * FolderHandler constructor.
     */
    function __construct() {
        $this->home = get_temp_dir() . '/' . $this->HOME_DIR;
        if (!file_exists($this->home)) {
            mkdir($this->home);
        }
    }

    /**
     * @param $file_content string The content of the file to be stored
     * @param $extension string The extension of the file to be stored
     * @return bool|string False if file saving failed, a string containing the filepath of the stored file otherwise
     */
    function store_file($file_content, $extension) {
        $file_name = bin2hex(openssl_random_pseudo_bytes($this->length / 2)) . '.' . $extension;
        $file = fopen($this->home . '/' . $file_name, 'w');
        if ($file) {
            fwrite($file, $file_content);
            fclose($file);
            return $this->home . '/' . $file_name;
        }
        else {
            return False;
        }
    }

    /**
     * @param $files array Files to be removed as absolute paths
     */
    function remove_files($files) {
        foreach ($files as $item) {
            unlink($item);
        }
    }
}