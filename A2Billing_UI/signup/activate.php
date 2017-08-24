<?php
session_name("UISIGNUP");
session_start();


if (!isset($_SESSION["date_activation"]) || (time()-$_SESSION["date_activation"]) > 60)
{
	$_SESSION["date_activation"]=time();
}
else
{
	sleep(3);
	echo gettext("Sorry the activation has been sent already, please wait 1 minute before making any other try !");
	exit();
}


include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("../lib/smarty.php");
/***********************************************************************************/
$HD_Form = new FormHandler("cc_card","User");
$HD_Form -> setDBHandler (DbConnect());
$HD_Form -> init();
// #### HEADER SECTION
$smarty->display('signup_header.tpl');
// #### TOP SECTION PAGE

$key = null;
$key = $_GET["key"];


$result = null;
$instance_sub_table = new Table('cc_card',"username, lastname, firstname, email, uipass, credit, useralias, loginkey, activatedbyuser");


$QUERY = "( loginkey = '".$key."' )" ;

$list = $instance_sub_table -> Get_list ($HD_Form -> DBHandle, $QUERY);

if(isset($key) && $list[0][8]=="f")
{

    $QUERY = "UPDATE cc_card SET  activatedbyuser = 't' WHERE ( loginkey = '".$key."' ) ";
    $result = $instance_sub_table -> SQLExec ($HD_Form -> DBHandle, $QUERY, 0);
}


if( $list[0][8] != "t" && isset($result) && $result != null){



	$QUERY = "SELECT mailtype, fromemail, fromname, subject, messagetext, messagehtml FROM cc_templatemail WHERE mailtype='signupconfirmed' ";
	$res = $HD_Form -> DBHandle -> Execute($QUERY);
	$num = 0;
	if ($res)
		$num = $res -> RecordCount();

	if (!$num)
	{
		echo "<br>Error : No email Template Found <br>";        
	}else{
	
		for($i=0;$i<$num;$i++){
			$listtemplate[] = $res->fetchRow();
		}
		list($mailtype, $from, $fromname, $subject, $messagetext, $messagehtml) = $listtemplate [0];
		if ($FG_DEBUG == 1) echo "</br><b>BELOW THE CARD PROPERTIES </b><hr></br>";
		$keepmessagetext = $messagetext;
		
	
		$messagetext = $keepmessagetext;	
		list($username, $lastname, $firstname, $email, $uipass, $credit, $cardalias, $loginkey) = $list[0];	
		if ($FG_DEBUG == 1) echo "<br># $username, $lastname, $firstname, $email, $uipass, $credit, $cardalias #</br>";	

		$messagetext = str_replace('$name', $lastname, $messagetext);
		//$message = str_replace('$username', $form->getValue('username'), $messagetext);
		$messagetext = str_replace('$card_gen', $username, $messagetext);
		$messagetext = str_replace('$password', $uipass, $messagetext);
		$messagetext = str_replace('$cardalias', $cardalias, $messagetext);
		$messagetext = str_replace('$cardalias', $cardalias, $messagetext);
		$messagetext = str_replace('=$loginkey', "=$loginkey", $messagetext);
		$messagetext = str_replace('$loginkey', "=$loginkey", $messagetext);

		$em_headers  = "From: ".$fromname." <".$from.">\n";
		$em_headers .= "Reply-To: ".$from."\n";
		$em_headers .= "Return-Path: ".$from."\n";
		$em_headers .= "X-Priority: 3\n";

		mail($email, $subject, $messagetext, $em_headers);
	}

?>

<blockquote>
	<div align="center"><br></br>
	 <font color="#FF0000"><b><?php echo gettext("Welcome! Your account has been successfully activated. Thank you!"); ?></b></font><br>
		  <br></br>
		  
			  
		  <?php echo $list[0][2]; ?> <?php echo $list[0][1]; ?>, <?php echo gettext("Thank you for registering with us !");?><br>
		  <?php echo gettext("An email confirming your information has been sent to"); ?> <b><?php echo $list[0][3]; ?></b><br></br>
			<h3>
			  <?php echo gettext("Your cardnumber is "); ?> <b><font color="#00AA00"><?php echo $list[0][0]; ?></font></b><br></br></br>
			  <?php echo gettext("To login to your account :"); ?></br>
			  <?php echo gettext("Your card alias (login) is "); ?> <b><font color="#00AA00"><?php echo $list[0][6]; ?></font></b><br>
			  <?php echo gettext("Your password is "); ?> <b><font color="#00AA00"><?php echo $list[0][4]; ?></font></b><br>
			</h3>	  
			
			<br><br>
	<?php echo gettext("Follow the link to access your account : ").'<a href="'.$A2B->config["signup"]['urlcustomerinterface'].'">'.$A2B->config["signup"]['urlcustomerinterface']."</a><br>"; ?>
		
</div>
</blockquote>


<?php }else{ ?>

<center>
<br></br><br></br>
<br></br><br></br>

<table width="400">
<tr><td colspan="2" bgcolor="#DDDDDD"></td></tr>
<tr><td colspan="2" bgcolor="#DDDDDD"></td></tr>
<tr>
<td bgcolor="#EEEEEE">
<img src="<?php echo KICON_PATH;?>/khelpcenter.gif"/></td>
<td bgcolor="#EEEEEE">

<b>

<?php
if( $records[0][8] == "t")
{
	echo gettext("Your account is already activated.")." <br>";
}
elseif(isset($result) || $result != null)
{
	// nada
}
else
{
	echo gettext("Your account cannot be activated please contact <br> the website administrator or retry later.")." <br>";
}
?>

</b>

</td></tr>
<tr><td colspan="2" bgcolor="#DDDDDD"></td></tr>
<tr><td colspan="2" bgcolor="#DDDDDD"></td></tr>
</table>

<br></br><br></br>
<br></br><br></br>
</center>
   

<?php
}

// #### FOOTER SECTION
$smarty->display('signup_footer.tpl');

?>
