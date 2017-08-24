<?php
//parametros
if(isset($_GET['extension'])) {
$ext = $_GET['extension'];
//
$output = exec("asterisk -rx 'core show channels concise' |grep ^SIP/$ext");
$channel = substr($output, 0, strpos($output, '!'));
system("asterisk -rx 'channel request hangup '$channel", $retval);
}
?>

