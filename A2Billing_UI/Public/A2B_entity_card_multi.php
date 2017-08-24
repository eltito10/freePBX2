<?php
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("./form_data/FG_var_card.inc");
include ("../lib/smarty.php");


if (! has_rights (ACX_CUSTOMER)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}

$HD_Form -> FG_FILTER_SEARCH_FORM = false;
$HD_Form -> FG_EDITION = false;
$HD_Form -> FG_DELETION = false;
$HD_Form -> FG_OTHER_BUTTON1 = false;
$HD_Form -> FG_OTHER_BUTTON2 = false;
$HD_Form -> FG_FILTER_APPLY = false;

getpost_ifset(array('choose_list', 'creditlimit', 'cardnum', 'addcredit', 'choose_tariff', 'gen_id', 'cardnum', 'choose_simultaccess', 'choose_currency', 'choose_typepaid', 'creditlimit', 'enableexpire', 'expirationdate', 'expiredays', 'runservice', 'sip', 'iax','cardnumberlenght_list'));


/***********************************************************************************/

$HD_Form -> setDBHandler (DbConnect());


// GENERATE CARDS


$nbcard = $choose_list;

if ($nbcard>0){
/*
'2465773443', '331', 'a', 't', 'LASTNAME', 'FIRSTNAME', 'email@kiki.net', 'adresse', 'city', 'state', 'countr', '1000', '65000000', '2465773443'
INSERT INTO card (myusername, credit, tariff, activated, lastname, firstname, email, address, city, state, 
country, zipcode, phone, userpass) values ('2465773443', '331', 'a', 't', 'LASTNAME', 'FIRSTNAME', 'email@domain.com', 
'adresse', 'city', 'state', 'country', '1000', '0000000000', '2465773443')
*/
		$FG_ADITION_SECOND_ADD_TABLE  = "cc_card";		
		$FG_ADITION_SECOND_ADD_FIELDS = "username, useralias, credit, tariff, activated, lastname, firstname, email, address, city, state, country, zipcode, phone, userpass, simultaccess, currency, typepaid , creditlimit, enableexpire, expirationdate, expiredays, uipass, runservice";

		if (DB_TYPE != "postgres"){
		        $FG_ADITION_SECOND_ADD_FIELDS .= ",creationdate ";
		}
				
		$FG_TABLE_SIP_NAME="cc_sip_buddies";
		$FG_TABLE_IAX_NAME="cc_iax_buddies";
		//$FG_QUERY_ADITION_SIP_IAX_FIELDS = "name, accountcode, regexten, amaflags, callerid, context, dtmfmode, host, type, username, allow, secret";
		$FG_QUERY_ADITION_SIP_IAX_FIELDS = "name, accountcode, regexten, amaflags, callerid, context, dtmfmode, host, type, username, allow, secret, id_cc_card, nat,  qualify";
		if (isset($sip)){
			$FG_ADITION_SECOND_ADD_FIELDS .= ", sip_buddy"; 
			$instance_sip_table = new Table($FG_TABLE_SIP_NAME, $FG_QUERY_ADITION_SIP_IAX_FIELDS);
		}
		
		if (isset($iax)){
			$FG_ADITION_SECOND_ADD_FIELDS .= ", iax_buddy";	
			$instance_iax_table = new Table($FG_TABLE_IAX_NAME, $FG_QUERY_ADITION_SIP_IAX_FIELDS);
		}	
		
		if ( (isset($sip)) ||  (isset($iax)) ){
			$list_names = explode(",",$FG_QUERY_ADITION_SIP_IAX);
			$type = FRIEND_TYPE;
			$allow = FRIEND_ALLOW;
			$context = FRIEND_CONTEXT;
			$nat = FRIEND_NAT;
			$amaflags = FRIEND_AMAFLAGS;
			$qualify = FRIEND_QUALIFY;
			$host = FRIEND_HOST;   
			$dtmfmode = FRIEND_DTMFMODE;
		}	
		
		
		$instance_sub_table = new Table($FG_ADITION_SECOND_ADD_TABLE, $FG_ADITION_SECOND_ADD_FIELDS);
				
		$gen_id = time();
		$_SESSION["IDfilter"]=$gen_id;
		
		
		$creditlimit = is_numeric($creditlimit) ? $creditlimit : 0;
		//echo "::> $choose_simultaccess, $choose_currency, $choose_typepaid, $creditlimit";
		for ($k=0;$k<$nbcard;$k++){
			 $arr_card_alias = gen_card_with_alias("cc_card", 0, $cardnumberlenght_list);
			 $cardnum = $arr_card_alias[0];
			 $useralias = $arr_card_alias[1];
			if (!is_numeric($addcredit)) $addcredit=0;
			$passui_secret = MDP_NUMERIC(10);
			$FG_ADITION_SECOND_ADD_VALUE  = "'$cardnum', '$useralias', '$addcredit', '$choose_tariff', 't', '$gen_id', '', '', '', '', '', '', '', '', '$cardnum', $choose_simultaccess, '$choose_currency', $choose_typepaid, $creditlimit, $enableexpire, '$expirationdate', $expiredays, '$passui_secret', '$runservice'";
			
			
			if (DB_TYPE != "postgres") $FG_ADITION_SECOND_ADD_VALUE .= ",now() ";
					
					
			if (isset($sip)) $FG_ADITION_SECOND_ADD_VALUE .= ", 1";
			if (isset($iax)) $FG_ADITION_SECOND_ADD_VALUE .= ", 1";

			$id_cc_card = $instance_sub_table -> Add_table ($HD_Form -> DBHandle, $FG_ADITION_SECOND_ADD_VALUE, null, null, $HD_Form -> FG_TABLE_ID);
			
			// Insert data for sip_buddy
			if (isset($sip)){
				//$FG_QUERY_ADITION_SIP_IAX_VALUE = "'$cardnum', '$cardnum', '$cardnum', '$amaflag', '$cardnum', '$context', 'RFC2833','dynamic', 'friend', '$cardnum', 'g729,ulaw,alaw,gsm','".$passui_secret."'";
				$FG_QUERY_ADITION_SIP_IAX_VALUE = "'$cardnum', '$cardnum', '$cardnum', '$amaflags', '$cardnum', '$context', '$dtmfmode','$host', '$type', '$cardnum', '$allow', '".$passui_secret."', '$id_cc_card', '$nat', '$qualify'";
				$result_query1 = $instance_sip_table -> Add_table ($HD_Form ->DBHandle, $FG_QUERY_ADITION_SIP_IAX_VALUE, null, null, null);
				
				$_SESSION["is_sip_iax_change"]=1;
				$_SESSION["is_sip_changed"]=1;
			}
			// Insert data for iax_buddy
			if (isset($iax)){
				//$FG_QUERY_ADITION_SIP_IAX_VALUE = "'$cardnum', '$cardnum', '$cardnum', '$amaflag', '$cardnum', '$context', 'RFC2833','dynamic', 'friend', '$cardnum', 'g729,ulaw,alaw,gsm','".$passui_secret."'";
				$FG_QUERY_ADITION_SIP_IAX_VALUE = "'$cardnum', '$cardnum', '$cardnum', '$amaflags', '$cardnum', '$context', '$dtmfmode','$host', '$type', '$cardnum', '$allow', '".$passui_secret."', '$id_cc_card', '$nat', '$qualify'";
				$result_query2 = $instance_iax_table -> Add_table ($HD_Form ->DBHandle, $FG_QUERY_ADITION_SIP_IAX_VALUE, null, null, null);
				
				$_SESSION["is_sip_iax_change"]=1;
				$_SESSION["is_iax_changed"]=1;
			}
		}


		// Save Sip accounts to file
		if (isset($sip)){
			$buddyfile = BUDDY_SIP_FILE;

			$instance_table_friend = new Table($FG_TABLE_SIP_NAME,'id, '.$FG_QUERY_ADITION_SIP_IAX);
			$list_friend = $instance_table_friend -> Get_list ($HD_Form ->DBHandle, '', null, null, null, null);
			if (is_array($list_friend)){
				$fd=fopen($buddyfile,"w");
				if (!$fd){
					$error_msg= "</br><center><b><font color=red>".gettext("Could not open buddy file")." ". $buddyfile."</font></b></center>";
				}else{
					foreach ($list_friend as $data){
						$line="\n\n[".$data[1]."]\n";
						if (fwrite($fd, $line) === FALSE) {
							echo "Impossible to write to the file ($buddyfile)";
							break;
						}else{
							for ($i=1;$i<count($data)-1;$i++){
								if (strlen($data[$i+1])>0){
									if (trim($list_names[$i]) == 'allow'){
										$codecs = explode(",",$data[$i+1]);
										$line = "";
										foreach ($codecs as $value)
											$line .= trim($list_names[$i]).'='.$value."\n";
									} else    $line = (trim($list_names[$i]).'='.$data[$i+1]."\n");
	
									if (fwrite($fd, $line) === FALSE){
										echo gettext("Impossible to write to the file")." ($buddyfile)";
										break;
									}
								}
							}
							}
					}	
					fclose($fd);
				}
			}//end if is_array
		} // END SAVE SIP ACCOUNTS 


		// Save IAX accounts to file
		if (isset($iax)){
			$buddyfile = BUDDY_IAX_FILE;

			$instance_table_friend = new Table($FG_TABLE_IAX_NAME,'id, '.$FG_QUERY_ADITION_SIP_IAX);
			$list_friend = $instance_table_friend -> Get_list ($HD_Form ->DBHandle, '', null, null, null, null);	
			
			if (is_array($list_friend)){
				$fd=fopen($buddyfile,"w");
				if (!$fd){
					$error_msg= "</br><center><b><font color=red>".gettext("Could not open buddy file"). $buddyfile."</font></b></center>";
				}else{
					foreach ($list_friend as $data){
						$line="\n\n[".$data[1]."]\n";
						 if (fwrite($fd, $line) === FALSE) {
							echo "Impossible to write to the file ($buddyfile)";
							break;
						}else{
							for ($i=1;$i<count($data)-1;$i++){
								if (strlen($data[$i+1])>0){
									if (trim($list_names[$i]) == 'allow'){
										$codecs = explode(",",$data[$i+1]);
										$line = "";
										foreach ($codecs as $value)
											$line .= trim($list_names[$i]).'='.$value."\n";
									} else    $line = (trim($list_names[$i]).'='.$data[$i+1]."\n");
	
									if (fwrite($fd, $line) === FALSE){
										echo gettext("Impossible to write to the file")." ($buddyfile)";
										break;
									}
								}
							}
						}
					}
					fclose($fd);
				}
			}// end if is_array
		} // END SAVE IAX ACCOUNTS


}
if (!isset($_SESSION["IDfilter"])) $_SESSION["IDfilter"]='NODEFINED';


$HD_Form -> FG_TABLE_CLAUSE = " lastname='".$_SESSION["IDfilter"]."'";

// END GENERATE CARDS



$HD_Form -> init();


if ($id!="" || !is_null($id)){	
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);	
}


if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;
	


$list = $HD_Form -> perform_action($form_action);



// #### HEADER SECTION
$smarty->display('main.tpl');

// #### HELP SECTION
echo $CC_help_generate_customer;



$instance_table_tariff = new Table("cc_tariffgroup", "id, tariffgroupname");
$FG_TABLE_CLAUSE = "";
$list_tariff = $instance_table_tariff -> Get_list ($HD_Form ->DBHandle, $FG_TABLE_CLAUSE, "tariffgroupname", "ASC", null, null, null, null);
$nb_tariff = count($list_tariff);

// FORM FOR THE GENERATION
?>

	  
   <table align="center"  class="bgcolor_001" border="0" width="65%">
        <tbody><tr>
	<form name="theForm" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
          <td align="left" width="75%">
		<strong>1)</strong> <?php echo gettext("Length of card number:");?>
		<select name="cardnumberlenght_list" size="1" class="form_input_select">
		<?php 
		foreach ($A2B -> cardnumber_range as $value){
		?>
			<option value='<?php echo $value ?>' 
			<?php if ($value == $cardnumberlenght_list) echo "selected";
			?>> <?php echo $value." ".gettext("Digits");?> </option>
			
		<?php
		}
		?>						
		</select><br>
	  	<strong>2)</strong> 
		<select name="choose_list" size="1" class="form_input_select">
			<option value=""><?php echo gettext("Choose the number of cards to create");?></option>
			<option class="input" value="1"><?php echo gettext("1 Card");?></option>
			<option class="input" value="10"><?php echo gettext("10 Cards");?></option>
			<option class="input" value="50"><?php echo gettext("50 Cards");?></option>
			<option class="input" value="100"><?php echo gettext("100 Cards");?></option>
			<option class="input" value="200"><?php echo gettext("200 Cards");?></option>
			<option class="input" value="500"><?php echo gettext("500 Cards");?></option>
			<option class="input" value="5000"><?php echo gettext("5000 Cards");?></option>
		</select>
		<br/>
				
		  	<strong>3)</strong> 
				<select NAME="choose_tariff" size="1" class="form_input_select" >
					<option value=''><?php echo gettext("Choose a Tariff");?></option>
				
				<?php					 
			  	 foreach ($list_tariff as $recordset){ 						 
				?>
					<option class=input value='<?php echo $recordset[0]?>' ><?php echo $recordset[1]?></option>                        
				<?php 	 }
					?>
				</select>
				<br/>
				
			  	<strong>4)</strong> 
				<?php echo gettext("Initial amount of credit");?> : 	<input class="form_input_text" name="addcredit" size="10" maxlength="10" >
				<?php echo strtoupper(BASE_CURRENCY) ?>
				<br/>
				
				<strong>5)</strong> 
				<?php echo gettext("Simultaneous access");?> : 
				<select NAME="choose_simultaccess" size="1" class="form_input_select" >
					<option value='0' selected><?php echo gettext("INDIVIDUAL ACCESS");?></option>
					<option value='1'><?php echo gettext("SIMULTANEOUS ACCESS");?></option>
				   </select>
				<br/>
				
				<strong>6)</strong> 
				<?php echo gettext("Currency");?> :
				<select NAME="choose_currency" size="1" class="form_input_select" >
				<?php 
					foreach($currencies_list as $key => $cur_value) {											
				?>
					<option value='<?php echo $key ?>'><?php echo $cur_value[1].' ('.$cur_value[2].')' ?></option>
				<?php } ?>			
				</select>
				<br/>
				<strong>7)</strong>
				<?php echo gettext("Card type");?> :
				<select NAME="choose_typepaid" size="1" class="form_input_select" >
					<option value='0' selected><?php echo gettext("PREPAID CARD");?></option>
					<option value='1'><?php echo gettext("POSTPAY CARD");?></option>
				   </select>
				<br/>
				<strong>8)</strong>
				<?php echo gettext("Credit Limit of postpay");?> : <input class="form_input_text" name="creditlimit" size="10" maxlength="16" >
				<br/>
				<strong>9)</strong>
			   <?php echo gettext("Enable expire");?>&nbsp;: <select name="enableexpire" class="form_input_select" >
								<option value="0" selected="selected">
						                           <?php echo gettext("NO EXPIRATION");?>                            </option><option value="1">
						                             <?php echo gettext("EXPIRE DATE");?>                          </option><option value="2">
						                            <?php echo gettext("EXPIRE DAYS SINCE FIRST USE");?>                           </option><option value="3">
						                            <?php echo gettext("EXPIRE DAYS SINCE CREATION");?>                           </option></select>
				<br/>
				<?php 
					$begin_date = date("Y");
					$begin_date_plus = date("Y")+10;	
					$end_date = date("-m-d H:i:s");
					$comp_date = "value='".$begin_date.$end_date."'";
					$comp_date_plus = "value='".$begin_date_plus.$end_date."'";
				?>
				<strong>10)</strong>
				<?php echo gettext("Expiry Date");?>&nbsp;: <input class="form_input_text"  name="expirationdate" size="40" maxlength="40" <?php echo $comp_date_plus; ?>><?php echo gettext("(Format YYYY-MM-DD HH:MM:SS)");?>
				<br/>
				<strong>11)</strong>
			   <?php echo gettext("Expiry days");?>&nbsp;: <input class="form_input_text"  name="expiredays" size="10" maxlength="6" value="0">
				<br/>
				<strong>12)</strong>
				<?php echo gettext("Run service");?>&nbsp; : 
				<?php echo gettext("Yes");?> <input name="runservice" value="1" type="radio"> - <?php echo gettext("No");?> <input name="runservice" value="0" checked="checked"  type="radio">
				<br/>
				<strong>13)</strong>
			   <?php echo gettext("Create SIP/IAX Friends");?>&nbsp;: <?php echo gettext("SIP")?> <input type="checkbox" name="sip" value="1" checked> <?php echo gettext("IAX")?> : <input type="checkbox" name="iax" value="1" checked>
				<br/>
		</td>	
		<td align="left" valign="bottom"> 
				<input class="form_input_button"  value=" GENERATE CARDS " type="submit"> 
        </td>
	 </form>
        </tr>
      </tbody></table>
	  <br>
	    
	  
	  <?php  if (($_SESSION["is_admin"]==1) && (2==3)){ ?>
	  
	   <table width="<?php echo $FG_HTML_TABLE_WIDTH?>" border="0" align="center" class="bgcolor_001">
        <tr>
          <td align="left">
		   <form NAME="theForm">
			  	<select NAME="choose_list" size="1" class="form_input_select" >
					<option value=''><?php echo gettext("Choose a Reseller");?></option>
					<?php					 
				  	 foreach ($list_reseller as $recordset){ 						 
					?>
					<option class=input value='<?php echo $recordset[0]?>' <?php if ($recordset[0]==$IDmanager) echo "selected";?>><?php echo $recordset[1]?></option>                        
					<?php 	 }
					?>
				</select>
				<input class="form_input_button"  
				TYPE="button" VALUE=" DISPLAY CUSTOMERS OF THIS RESELLER" onClick="openURL('./PP_entity_anacust.php?IDmanager=')"> 
		   </form>
        </td>
        </tr>
      </table>
	  <br/>
	   <?php  } ?>

<?php
// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;


$_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR]= "SELECT ".$HD_Form -> FG_EXPORT_FIELD_LIST." FROM $HD_Form->FG_TABLE_NAME";
if (strlen($HD_Form->FG_TABLE_CLAUSE)>1) 
	$_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR] .= " WHERE $HD_Form->FG_TABLE_CLAUSE ";
if (!is_null ($HD_Form->FG_ORDER) && ($HD_Form->FG_ORDER!='') && !is_null ($HD_Form->FG_SENS) && ($HD_Form->FG_SENS!='')) 
	$_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR].= " ORDER BY $HD_Form->FG_ORDER $HD_Form->FG_SENS";




// #### FOOTER SECTION
$smarty->display('footer.tpl');
?>
