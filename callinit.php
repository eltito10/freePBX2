<?php
//parametros
if(isset($_GET['extension']) && isset($_GET['numero']) && isset($_GET['codigo'])) {
$ext = $_GET['extension'];
$num = $_GET['numero'];
$cod = $_GET['codigo'];
//llamada
   if($cod=="230508") {
   system("asterisk -rx 'channel originate SIP/'$ext' extension '$num'@ws'", $retval);
   echo $retval;
   } else echo 'wrong code!';
}
?>

