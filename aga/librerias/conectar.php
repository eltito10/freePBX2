<?php
$link = "";
$result = "";
$nombreservidor = "localhost";	//aqui va el nombre del servidor
$user = "root";		//nombre del usuario con el que se va a conectar
$contrasena = "c4st1ll3j0";		//contrase�a del usuario

function conectarse($basedatos)	//nombre de la base de datos que se pasa como par�metro
{
   global $nombreservidor, $user, $contrasena;
   if (!($link=mysql_connect($nombreservidor, $user, $contrasena)))
   {
      echo "Error conectando a la base de datos.";
      exit();
   }
      if (!mysql_select_db($basedatos,$link))
      {
         echo "Error seleccionando la base de datos.";
         exit();
      }
   return $link;
}


function IndiceDeudor()	//nombre de la base de datos que se pasa como par�metro
{
   static $indDeudor = -1;
   $indDeudor ++;	
   return $indDeudor;
}




?>
