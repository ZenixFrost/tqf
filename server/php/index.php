<?php
/*
 * jQuery File Upload Plugin PHP Example
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);
require('UploadHandler.php');
class CustomUploadHandler extends UploadHandler {
    protected function trim_file_name($file_path, $name, $size, $type, $error, $index, $content_range) {

        if($type == "application/pdf"){
            $name = md5(time() . mt_rand()).".pdf";
        }elseif($type == "application/vnd.openxmlformats-officedocument.wordprocessingml.document"){
            $name = md5(time() . mt_rand()).".docx";
        }elseif($type == "application/msword"){
            $name = md5(time() . mt_rand()).".doc";
        }

        return $name;
    }
}

$upload_handler = new CustomUploadHandler();


