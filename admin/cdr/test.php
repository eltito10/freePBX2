<?php
$months = Array ( 0 => 'Enero', 1 => 'Febrero', 2 => 'Marzo', 3 => 'Abril', 4 => 'Mayo', 5 => 'Junio', 6 => 'Julio', 7 => 'Agosto', 8 => 'Septiembre', 9 => 'Octubre', 10 => 'Noviembre', 11 => 'Diciembre' );
$fromstatsmonth_sday='2011-03';
$mesnum = intval(substr($fromstatsmonth_sday,-2));
$mes = $months[$mesnum-1];
$anho = substr($fromstatsmonth_sday,0,4);
$db = 'asteriskcdrdb';
$dbuser = 'freepbx';
$dbpass = 'fpbx';
$dbhost = 'localhost';
$link4 = mysql_connect($dbhost,$dbuser,$dbpass);
mysql_select_db("$db", $link4);
$contenido = mysql_query("select distinct userfield from cdr where userfield != ''", $link4);
while ($nego = mysql_fetch_array($contenido)){
$mylegend = $nego["userfield"];
        $sql = "select userfield,sum(duration) from cdr where userfield='$mylegend' and calldate rlike '$fromstatsmonth_sday'";
        $datos = mysql_query($sql, $link4);
	while ($foo = mysql_fetch_assoc($datos)) {
	$dur = intval($foo["sum(duration)"]/60);
	$data = array($foo["userfield"],$dur);
	}
	var_dump($data[1]);
}
?>

