<?php

/**
 * @file
 * plays recording file
 */

  $zipname = 'file.zip';
  $zip = new ZipArchive();
  $zip->open($zipname, ZipArchive::CREATE);
  //$uniques = array('1365270743.462354','1365270664.462319','1365270836.462376','1365270903.462398');
  /*
  $uniques = array(array("1365270743.462354", "2013-04-06 12:52:23"),
                   array("1365270664.462319", "2013-04-06 12:51:04"),
                   array("1365270836.462376", "2013-04-06 12:53:56"),
                   array("1365270903.462398", "2013-04-06 12:55:03")
                  );*/

  $uniques = $_SESSION['data'];
  foreach ($uniques as $item) {
    $unique = $item['0'];
    $fecha =  $item['1'];
    $dia = substr($fecha, 0, 10);
    $horas = substr($fecha, 11, 19);
    $hora = str_replace(':', '', $horas);
    //echo $unique." ".$fecha." ".$dia." ".$hora."<br>";
    //$cmd = 'find /var/spool/asterisk/monitor/'.$dia.'/ -name *-'.$hora.'-';
    $cmd = 'find /var/spool/asterisk/monitor/ -name *-'.$hora.'-';
    $path = exec($cmd.$unique.'.gsm');
    $name = basename($path);
    // See if the file exists
    if (is_file($path)) {
      //echo $path." ".$name."<br>";
      $zip->addFile($path,$name); 
    } else
      echo "No lo encontró";
  }
  $zip->close();

  header('Content-Type: application/zip');
  header('Content-disposition: attachment; filename=file.zip');
  header('Content-Length: '.filesize($zipname));
  readfile($zipname);

?>