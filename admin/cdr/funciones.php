#!/usr/bin/php -q
<?
function getdeudor($telefono,$fecha){
$db = 'sgc';
$dbuser = 'asterisk';
$dbpass = '4st3r1sk';
$dbhost = '192.168.1.6';
$link = mysql_connect($dbhost,$dbuser,$dbpass);
mysql_select_db("$db", $link);
$consulta = mysql_query("select IdDeudor from gestion where GstTelefono rlike '$telefono' and FchCreacion = substr('$fecha',1,10) and HraCreacion rlike substr('$fecha',12,17)", $link);
$row = mysql_fetch_row($consulta);
$deudor = $row[0];
return $deudor;
mysql_close($link); 
}

function getnegocio($telefono,$fecha){
$db = 'sgc';
$dbuser = 'asterisk';
$dbpass = '4st3r1sk';
$dbhost = '192.168.1.6';
$link = mysql_connect($dbhost,$dbuser,$dbpass);
mysql_select_db("$db", $link);
$consulta = mysql_query("select IdNegocio from gestion where GstTelefono rlike '$telefono' and FchCreacion = substr('$fecha',1,10) and HraCreacion rlike substr('$fecha',12,17)", $link);
$row = mysql_fetch_row($consulta);
$negocio = $row[0];
return $negocio;
mysql_close($link);
}

?>

