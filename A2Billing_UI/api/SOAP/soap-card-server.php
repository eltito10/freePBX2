<?php
include ("../../lib/defines.php");
include ("../../lib/regular_express.inc");
require_once('SOAP/Server.php');
require_once('SOAP/Disco.php');

exit;
// USAGE 
// , to separate the CallerID 9010213,9010214,9010200

/*

    $card = new SOAP_Client($endpoint);
    $method = 'Create_Card';
    
	
    $params = array('security_key' => md5($security_key), 'transaction_code' => 'mytransaction_code', 'account_number' => 'myaccount_number', 'tariff' => '1', 'uipass' => '', 'credit' => '10', 'language' => 'en', 
	'activated' => '1', 'simultaccess' => '0', 'currency' => 'USD', 'runservice' => '0', 'typepaid' => '1', 'creditlimit' => '0', 
	'enableexpire' => '0', 'expirationdate' => '', 'expiredays' => '0', 'lastname' => 'Areski', 'firstname' => 'Areski', 'address' => 'my address', 
	'city' => 'mycity', 'state' => 'mystate', 'country' => 'mycoutry', 'zipcode' => '1000', 'phone' => '646486411', 'fax' => '', 
	'callerid_list' => '21345', 'iax_friend' => '1', 'sip_friend' => '0');
	
    $ans = $card->call($method, $params);
	
*/


//echo $security_key;


class Cards
{
     var $__dispatch_map = array();

     function Cards() {
        // Define the signature of the dispatch map on the Web servicesmethod

        // Necessary for WSDL creation
		
        $this->__dispatch_map['Create_Card'] =
             array('in' => array('security_key' => 'string', 'transaction_code' => 'string', 'account_number' => 'string',
								'tariff' => 'integer', 'uipass' => 'string', 'credit' => 'float', 'language' => 'string', 'activated' => 'integer', 
								'simultaccess' => 'integer', 'currency' => 'string', 'runservice' => 'integer', 'typepaid' => 'integer', 'creditlimit' => 'integer', 
								'enableexpire' => 'integer', 'expirationdate' => 'string', 'expiredays' => 'integer', 'lastname' => 'string',  
								'firstname' => 'string', 'address' => 'string', 'city' => 'string', 'state' => 'string', 'country' => 'string', 
								'zipcode' => 'string', 'phone' => 'string', 'fax' => 'string', 'callerid_list' => 'string',
								'iax_friend' => 'integer', 'sip_friend' => 'integer'),
                   'out' => array('transaction_code' => 'string', 'account_number' => 'string', 'card_number' => 'string', 'card_alias' => 'string', 'uipass' => 'string', 'result' => 'string', 'details' => 'string')
                   );
				   
        $this->__dispatch_map['Remove_Card'] =
             array('in' => array('security_key' => 'string', 'transaction_code' => 'string', 'account_number' => 'string',
								'cardnumber' => 'string'),
                   'out' => array('transaction_code' => 'string', 'account_number' => 'string', 'card_number' => 'string', 'result' => 'string', 'details' => 'string')
                   );
				   
		$this->__dispatch_map['Update_CallerID'] =
             array('in' => array('security_key' => 'string', 'transaction_code' => 'string', 'account_number' => 'string'
			 					, 'cardnumber' => 'string', 'callerid_list' => 'string'),
                   'out' => array('transaction_code' => 'string', 'account_number' => 'string', 'card_number' => 'string', 'result' => 'string', 'details' => 'string')
                   );
     }
	 
	 /*		ACTUALIZAR CLIENTES
		
		Actualizar Caller ID’s en A2Billing .		
		Web service: actualizar_cliente_callerID()		
		Parámetros		
		
		1.Código de transacción = ACTUCTEID
		2.Account Number
		3.Numero de tarjeta
		4.Lista Caller ID’s  *
		
		*  Se envían todos los callerID nuevamente para ser actualizados.		
		Como respuesta se debe recibir de  A2Billing  la siguiente información:
		
		1.Código de transacción= RACTUCTEID
		2.Account Number
		3.Numero de tarjeta
		4.Resultado de la transacción (Error / OK)
	 */
	 
	  /*
	  *		Function for the Service Update_CallerID : Update the callerID list from an existing card 
	  */ 
     function Update_CallerID($security_key, $transaction_code, $account_number, $cardnumber, $callerid_list){ 
	 
	 		// The wrapper variables for security
 			// $security_key = API_SECURITY_KEY;
			$logfile=SOAP_LOGFILE;	

			$mysecurity_key = API_SECURITY_KEY;
						
			$mail_content = "[" . date("Y/m/d G:i:s", mktime()) . "] "."SOAP API - Request asked: Remove_Card [$transaction_code, $account_number, $cardnumber]";
			
			// CHECK SECURITY KEY
			 if (md5($mysecurity_key) !== $security_key  || strlen($security_key)==0)
			 {
				  mail(EMAIL_ADMIN, "ALARM : API - CODE_ERROR SECURITY_KEY ", $mail_content);
				  error_log ("[" . date("Y/m/d G:i:s", mktime()) . "] "." CODE_ERROR SECURITY_KEY"."\n", 3, $logfile);
				  sleep(2);
				  return array($transaction_code, '', '', '', '', 'Error', 'KEY - BAD PARAMETER'."$security_key - $mysecurity_key");				  
			 } 
			 
			 return array($transaction_code, $account_number, $cardnumber, 'result=OK', '');
	 
	 }
	 /*
	  *		Function for the Service Remove_Card : remove an existing card for the database
	  */ 
     function Remove_Card($security_key, $transaction_code, $account_number, $cardnumber){ 
	 		
			// The wrapper variables for security
 			// $security_key = API_SECURITY_KEY;
			$logfile=SOAP_LOGFILE;	

			$mysecurity_key = API_SECURITY_KEY;
						
			$mail_content = "[" . date("Y/m/d G:i:s", mktime()) . "] "."SOAP API - Request asked: Remove_Card [$transaction_code, $account_number, $cardnumber]";
			
			// CHECK SECURITY KEY
			 if (md5($mysecurity_key) !== $security_key  || strlen($security_key)==0)
			 {
				  mail(EMAIL_ADMIN, "ALARM : API - CODE_ERROR SECURITY_KEY ", $mail_content);
				  error_log ("[" . date("Y/m/d G:i:s", mktime()) . "] "." CODE_ERROR SECURITY_KEY"."\n", 3, $logfile);
				  sleep(2);
				  return array($transaction_code, '', '', '', '', 'Error', 'KEY - BAD PARAMETER'."$security_key - $mysecurity_key");				  
			 } 
			   
			   
			// Create new account			
			$FG_TABLE  = "cc_card";
			
			$DBHandle  = DbConnect();
			
			$instance_sub_table = new Table($FG_TABLE);
			
			$FG_EDITION_CLAUSE = " username = '$cardnumber' ";
			
			$res_delete = $instance_sub_table -> Delete_table ($DBHandle, $FG_EDITION_CLAUSE);
			if (!$res_delete){
				return array($transaction_code, $account_number, $cardnumber, 'result=ERROR', 'Cannot remove this cardnumber');
			}else{
				return array($transaction_code, $account_number, $cardnumber, 'result=OK', '');
			}
	 }
	 
	 
	 /*
	  *		Function for the Service Create_Card : create a new card, also can create the sip/iax friends and the additional asterisk conf files
	  */ 
     function Create_Card($security_key, $transaction_code, $account_number, $tariff, $uipass, $credit, $language, $activated, $simultaccess, $currency, $runservice, 
	 $typepaid, $creditlimit, $enableexpire, $expirationdate, $expiredays, $lastname, $firstname, $address, $city, $state, $country, $zipcode, $phone, $fax, 
	 $callerid_list, $iax_friend, $sip_friend){ 
	 
	 		
			// The wrapper variables for security
 			// $security_key = API_SECURITY_KEY;
			$logfile=SOAP_LOGFILE;	

			$mysecurity_key = API_SECURITY_KEY;
						
			$mail_content = "[" . date("Y/m/d G:i:s", mktime()) . "] "."SOAP API - Request asked: Create_Card [$transaction_code, $account_number, $tariff, $uipass, $credit, $language, $activated, $simultaccess, $currency, $runservice, $typepaid, $creditlimit, $enableexpire, $expirationdate, $expiredays, $lastname, $firstname, $address, $city, $state, $country, $zipcode, $phone, $fax, $callerid_list, $iax_friend, $sip_friend]";
			
			// CHECK SECURITY KEY
			 if (md5($mysecurity_key) !== $security_key  || strlen($security_key)==0)
			 {
				  mail(EMAIL_ADMIN, "ALARM : API - CODE_ERROR SECURITY_KEY ", $mail_content);
				  error_log ("[" . date("Y/m/d G:i:s", mktime()) . "] "." CODE_ERROR SECURITY_KEY"."\n", 3, $logfile);
				  sleep(2);
				  return array($transaction_code, '', '', '', '', 'Error', 'KEY - BAD PARAMETER'."$security_key - $mysecurity_key");				  
			 } 
			   
			   
			// Create new account			
			$FG_ADITION_SECOND_ADD_TABLE  = "cc_card";		
			$FG_ADITION_SECOND_ADD_FIELDS = "username, useralias, credit, tariff, id_didgroup, activated, lastname, firstname, email, address, city, state, country,".
			"zipcode, phone, fax, userpass, simultaccess, currency, typepaid, creditlimit, language, runservice, enableexpire, expirationdate, expiredays, uipass, sip_buddy, iax_buddy";
			
			
			
			$arr_card_alias = gen_card_with_alias('cc_card', 1);
			$cardnum = $arr_card_alias[0];
			$useralias = $arr_card_alias[1];
			if ($uipass=='' || 	strlen($uipass)==0)		$uipass = MDP_STRING();
			
			
			
		  	// CHECK PARAMETERS LASTNAME ; FIRSTNAME ; ADDRESS ; ....
 		   	if (!is_numeric($credit) || !is_numeric($creditlimit) || !is_numeric($expiredays) || ($activated!=0 && $activated!=1) || ($simultaccess!=0 && $simultaccess!=1) || ($runservice!=0 && $runservice!=1) || strlen($lastname)>40 || strlen($firstname)>40 || strlen($address)>100 || strlen($city)>40 || strlen($state)>40 || strlen($country)>40 || strlen($zipcode)>40 || strlen($phone)>40 || strlen($email)>60 || strlen($fax)>40)
 			{
	  			mail(EMAIL_ADMIN, "ALARM : API  - BAD PARAMETER ", $mail_content);	  			
	  			error_log ("[" . date("Y/m/d G:i:s", mktime()) . "] "." - BAD PARAMETER "."\n", 3, $logfile);
				return array($transaction_code, '', '', '', '', 'Error', 'BAD PARAMETER');
 			}else{
 				
				if ($expirationdate=='') $expirationdate="now()";
				else $expirationdate="'$expirationdate'";
				$DBHandle  = DbConnect();
				
				$instance_sub_table = new Table($FG_ADITION_SECOND_ADD_TABLE, $FG_ADITION_SECOND_ADD_FIELDS);
				$FG_ADITION_SECOND_ADD_VALUE  = "'$cardnum', '$useralias', '".$credit."', '".$tariff."', '0', '$activated', '$lastname', '$firstname', '$email', '$address', '$city', "
				."'$state', '$country', '$zipcode', '$phone', '$fax', '$cardnum', ".$simultaccess.", '".$currency."', '".$typepaid."','".$creditlimit."', '".$language."', '".$runservice."', '"
				.$enableexpire."', $expirationdate, '$expiredays', '$uipass', '$iax_friend', '$sip_friend'";
				
				$result_query = $instance_sub_table -> Add_table ($DBHandle, $FG_ADITION_SECOND_ADD_VALUE, null,  null, 'id');			
				
				
				if ($result_query){	
					
					$id_cc_card = $result_query;
					
					if (strlen($callerid_list)>1){
						$callerid_list = split(',',$callerid_list);
						if (count($callerid_list)>0){
							$k=0;
							foreach ($callerid_list as $mycallerid){
								$k++;
								if (strlen($mycallerid)>1){
									$QUERY = "SELECT * FROM cc_callerid WHERE cid='$mycallerid'";
									$result = $instance_sub_table -> SQLExec ($DBHandle, $QUERY);
									if (!is_array($result)){
										$QUERY = "INSERT INTO cc_callerid (cid, id_cc_card) VALUES ('$mycallerid', '$id_cc_card')";
										$result = $instance_sub_table -> SQLExec ($DBHandle, $QUERY, 0);
										if ($result==false)
											$callerid_result .= "|callerid$k-$mycallerid=NOK"; 
										else
											$callerid_result .= "|callerid$k-$mycallerid=OK"; 
									}else{
										$callerid_result .= "|callerid$k-$mycallerid=NOK"; 
									}
								}
							}
						}
					}
					
					
					
					//return array('transaction_code', 'account_number', 'card_number', $useralias, 'uipass', 'result', "IDCARD_CREATED=$id_cc_card"."$callerid_result");
					//|LASTQUERY=$QUERY
					
					// CHECK IF THERE IS A FRIEND TO CREATE
					if ($iax_friend || $sip_friend){
					
						// NEW ACCOUNT CREATED 					
						$type = FRIEND_TYPE;
						$allow = FRIEND_ALLOW;
						$context = FRIEND_CONTEXT;
						$nat = FRIEND_NAT;
						$amaflags = FRIEND_AMAFLAGS;
						$qualify = FRIEND_QUALIFY;
						$host = FRIEND_HOST;   
						$dtmfmode = FRIEND_DTMFMODE;
						$uipass = MDP_STRING();
						
						
						$FG_QUERY_ADITION_SIP_IAX='name, type, username, accountcode, regexten, callerid, amaflags, secret, md5secret, nat, dtmfmode, qualify, canreinvite,disallow, allow, host, callgroup, context, defaultip, fromuser, fromdomain, insecure, language, mailbox, permit, deny, mask, pickupgroup, port,restrictcid, rtptimeout, rtpholdtimeout, musiconhold, regseconds, ipaddr, cancallforward';
						
						// For IAX and SIP
						$param_add_fields = "name, accountcode, regexten, amaflags, callerid, context, dtmfmode, host,  type, username, allow, secret";
						$param_add_value = "'$cardnum', '$cardnum', '$cardnum', '$amaflags', '$cardnum', '$context', '$dtmfmode','$host', '$type', '$cardnum', '$allow', '".$uipass."', '$id_cc_card', '$nat', '$qualify'";
						$list_names = explode(",",$FG_QUERY_ADITION_SIP_IAX);
						$FG_TABLE_SIP_NAME="cc_sip_buddies";
						$FG_TABLE_IAX_NAME="cc_iax_buddies";
						
						
						for ($ki=0;$ki<2;$ki++){
						
							if ($ki==0){
								if (!$sip_friend) continue;
								$cfriend='sip'; $FG_TABLE_NAME="cc_sip_buddies";
								$buddyfile = BUDDY_SIP_FILE;
							}else{
								if (!$iax_friend) continue;
								$cfriend='iax'; $FG_TABLE_NAME="cc_iax_buddies";
								$buddyfile = BUDDY_IAX_FILE;
							}							
							
							// Insert Sip/Iax account info
							if (($ki==0 && $sip_friend) || ($ki==1 && $iax_friend)){
								$instance_table1 = new Table($FG_TABLE_NAME, $FG_QUERY_ADITION_SIP_IAX);
								$result_query1=$instance_table1 -> Add_table ($DBHandle, $param_add_value, $param_add_fields, null, null);
								
								$instance_table_friend = new Table($FG_TABLE_NAME,'id, '.$FG_QUERY_ADITION_SIP_IAX);
								$list_friend = $instance_table_friend -> Get_list ($DBHandle, '', null, null, null, null);
							
								$fd=fopen($buddyfile,"w");
								if (!$fd){		
									mail($email_alarm, "ALARM : SOAP-API  - Could not open buddy file '$buddyfile'", $mail_content);
									error_log ("[" . date("Y/m/d G:i:s", mktime()) . "] "."[Could not open buddy file '$buddyfile'] - SOAP-API "."\n", 3, $logfile);
									return array($transaction_code, '', '', '', '', 'Error', 'SOAP-API  - Could not open buddy file $buddyfile');
								}else{
									 foreach ($list_friend as $data){
										$line="\n\n[".$data[1]."]\n";
										if (fwrite($fd, $line) === FALSE) {				
											error_log ("[" . date("Y/m/d G:i:s", mktime()) . "] "."[Impossible to write to the file ($buddyfile)] - CODE_ERROR 8"."\n", 3, $logfile);										
											return array($transaction_code, '', '', '', '', 'Error', 'SOAP-API Impossible to write to the file ($buddyfile)');
										}else{
											for ($i=1;$i<count($data)-1;$i++){
												if (strlen($data[$i+1])>0){
													if (trim($list_names[$i]) == 'allow'){
														$codecs = explode(",",$data[$i+1]);
														$line = "";
														foreach ($codecs as $value)
															$line .= trim($list_names[$i]).'='.$value."\n";
													}
													else	$line = (trim($list_names[$i]).'='.$data[$i+1]."\n");
														
													if (fwrite($fd, $line) === FALSE){ 
														error_log ("[" . date("Y/m/d G:i:s", mktime()) . "] "."[Impossible to write to the file ($buddyfile)] - CODE_ERROR 8"."\n", 3, $logfile);
														return array($transaction_code, '', '', '', '', 'Error', 'SOAP-API ERROR : Card created in the DB but Impossible to write to the file ($buddyfile)');
													}
												}
											}	
										}
										
									}
									fclose($fd);
								}	
							}
						
						} // END OF FOR - KI
						
					} // END if ($iax_friend || $sip_friend)
					
					return array($transaction_code, $account_number, $cardnum, $useralias, $uipass, 'result=OK', "ID CARD_CREATED=$result_query$callerid_result");
					
				}else{				
					mail(EMAIL_ADMIN, "ALARM : SOAP-API (Add_table)", "$FG_ADITION_SECOND_ADD_VALUE\n\n".$mail_content);
					error_log ("[" . date("Y/m/d G:i:s", mktime()) . "] "."[SOAP-API CODE_ERROR Add_table "."\n", 3, $logfile);
					return array($transaction_code, '', '', '', '', 'Error', 'SOAP-API CODE_ERROR Add_table');
				}
			
			} // END - CHECK PARAMETERS LASTNAME ; FIRSTNAME ; ADDRESS ; ....
     
    }
}


$server = new SOAP_Server();

$webservice = new Cards();

$server->addObjectMap($webservice, 'http://schemas.xmlsoap.org/soap/envelope/');


if (isset($_SERVER['REQUEST_METHOD'])  &&  $_SERVER['REQUEST_METHOD']=='POST') {

     $server->service($HTTP_RAW_POST_DATA);
	 
} else {
     // Create the DISCO server
     $disco = new SOAP_DISCO_Server($server,'Cards');
     header("Content-type: text/xml");
     if (isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'],'wsdl') == 0) {
         echo $disco->getWSDL();
     } else {
         echo $disco->getDISCO();
     }
}
exit;





// CLASS xmlrpc_client

/*
Usage:
   $client = new xmlrpc_client("http://localhost:7080");
   print $client->echo('x')."\n";
   print $client->add(1, 3)."\n";

*/
class xmlrpc_client
{
   var $url;
   var $urlparts;

   function xmlrpc_client($url)
   {
       $this->url = $url;
       $this->urlparts = parse_url($this->url);
       foreach(array('scheme', 'host', 'user', 'pass', 'path',
                     'query', 'fragment')
               as $part) {
           if (!isset($this->urlparts[$part])) {
               $this->urlparts[$part] = NULL;
               }
           }
   }

   function call($function, $arguments, &$return)
   {
       $requestprms['host'] = $this->urlparts['host'];
       $requestprms['port'] = $this->urlparts['port'];
       $requestprms['uri'] = $this->urlparts['path'];
       $requestprms['method'] = $function;
       $requestprms['args'] = $arguments;
       $requestprms['debug'] = 0;
       $requestprms['timeout'] = 0;
       $requestprms['user'] = NULL;
       $requestprms['pass'] = NULL;
       $requestprms['secure'] = 0;

       $result = xu_rpc_http_concise($requestprms);
       if (is_array($result) && isset($result['faultCode'])) {
           print('Error in xmlrpc call \''.$function.'\''."\n");
           print('  code  : '.$result['faultCode']."\n");
           print('  message: '.$result['faultString']."\n");
           return false;
           }
       $return = $result;
       return true;
   }

}



/*
 *		function return_xmlrpc_error
 */


function return_xmlrpc_error($errno,$errstr,$errfile=NULL,$errline=NULL
       ,$errcontext=NULL){
   global $xmlrpc_server;
   if(!$xmlrpc_server)die("Error: $errstr in '$errfile', line '$errline'");

   header("Content-type: text/xml; charset=UTF-8");
   print(xmlrpc_encode(array(
       'faultCode'=>$errno
       ,'faultString'=>"Remote XMLRPC Error from
         ".$_SERVER['HTTP_HOST'].": $errstr in at $errfile:$errline"
   )));
   die();
}    


/*
 *		function agesorter
 */

function agesorter($m)
{
	global $agesorter_arr, $xmlrpcerruser, $s;

	xmlrpc_debugmsg("Entering 'agesorter'");
	// get the parameter
	$sno=$m->getParam(0);
	// error string for [if|when] things go wrong
	$err="";
	// create the output value
	$v=new xmlrpcval();
	$agar=array();

	if (isset($sno) && $sno->kindOf()=="array")
	{
		$max=$sno->arraysize();
		// TODO: create debug method to print can work once more
		// print "<!-- found $max array elements -->\n";
		for($i=0; $i<$max; $i++)
		{
			$rec=$sno->arraymem($i);
			if ($rec->kindOf()!="struct")
			{
				$err="Found non-struct in array at element $i";
				break;
			}
			// extract name and age from struct
			$n=$rec->structmem("name");
			$a=$rec->structmem("age");
			// $n and $a are xmlrpcvals,
			// so get the scalarval from them
			$agar[$n->scalarval()]=$a->scalarval();
		}

		$agesorter_arr=$agar;
		// hack, must make global as uksort() won't
		// allow us to pass any other auxilliary information
		uksort($agesorter_arr, agesorter_compare);
		$outAr=array();
		while (list( $key, $val ) = each( $agesorter_arr ) )
		{
			// recreate each struct element
			$outAr[]=new xmlrpcval(array("name" =>
			new xmlrpcval($key),
			"age" =>
			new xmlrpcval($val, "int")), "struct");
		}
		// add this array to the output value
		$v->addArray($outAr);
	}
	else
	{
		$err="Must be one parameter, an array of structs";
	}

	if ($err)
	{
		return new xmlrpcresp(0, $xmlrpcerruser, $err);
	}
	else
	{
		return new xmlrpcresp($v);
	}
}


/*
 *		function getallheaders_xmlrpc
 */
 
function getallheaders_xmlrpc($m)
    {
        global $xmlrpcerruser;
        if (function_exists('getallheaders'))
        {
            return new xmlrpcresp(php_xmlrpc_encode(getallheaders()));
        }
        else
        {
            $headers = array();
            // IIS: poor man's version of getallheaders
            foreach ($_SERVER as $key => $val)
                if (strpos($key, 'HTTP_') === 0)
                {                
                    $key = ucfirst(str_replace('_', '-', strtolower(substr($key, 5))));
                    $headers[$key] = $val;
                }
            return new xmlrpcresp(php_xmlrpc_encode($headers));
        }
    }
	
	
	

?>
