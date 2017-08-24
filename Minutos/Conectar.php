<?php
$Conectar = mysql_connect('localhost','asterisk','4st3r1sk');
	if(!$Conectar) {
		die('No se Puede Conectar a la BD \n'.mysql_error());
	}else{
		echo "Conexion Satisfactoria";	
	}
	mysql_select_db('astquota',$Conectar) or die('Error no se Puede Acceder a la BD'.mysql_error());
?>
