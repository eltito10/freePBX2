<?php
include ("lib/defines.php");

$popup_select=1;
include ("lib/smarty.php");

$smarty->display('main.tpl');


if (!isset($c))	$c="0";

$error["0"] = gettext("ERROR : ACCESS REFUSED");
$error["syst"] = gettext("Sorry a problem occur on our system, please try later!");
$error["errorpage"] = gettext("There is an error on this page!");
$error["accessdenied"] = gettext("Sorry, you don t have access to this page !");
$error["construction"] = gettext("Sorry, this page is in construction !");

?>

<br></br><br></br>
<table width="460" border="2" align="center" cellpadding="1" cellspacing="2" bordercolor="#eeeeff" bgcolor="#FFFFFF">
	<tr  class="pp_error_maintable_tr1">
		<td> 					
			<div align="center"><b><font size="3"><?php echo gettext("Error Page");?></font></b></div>
		</td>
	</tr>				 
	<tr> 
	<td align="center" colspan=2> 
		<table width="100%" border="0" cellpadding="5" cellspacing="5">		  
		<tr> 
			<td align="center"><br/>
				<img src="<?php echo KICON_PATH; ?>/system-config-rootpassword.gif"> 
				<br/>
				<b><font size="3"><?php echo $error[$c]?></font></b>
				<br/><br/>
			</td>
		</tr>
		</table>			
	</td>
	</tr>
</table>
<br/><br/>

<?php
	if ($c == "accessdenied")
		$smarty->display('index.tpl');
	$smarty->display('footer.tpl');
?>
