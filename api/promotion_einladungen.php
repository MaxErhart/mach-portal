<?php

$path = "D:\inetpub\download_links\Promotion_Einladungen\\";
$fileName = basename($_GET['file']);
$file=$path.$fileName;
// echo $file;
if(!file_exists($file)){ // file does not exist
  die('file not found');
} else {
  // header("Cache-Control: public");
  // header("Content-Description: File Transfer");
  header("Content-Disposition: attachment; filename=$fileName");
  header("Content-Type: application/pdf_add_launchlink");
  // header("Content-Transfer-Encoding: binary");

  // read the file from disk
  readfile($file);
}

?>