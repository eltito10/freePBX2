<?php
/***************************************************************************
 *
 * Class.RateEngine.php : RateEngine Functions for A2Billing
 * Written for PHP 4.x & PHP 5.X versions.
 *
 * A2Billing -- Asterisk billing solution.
 * Copyright (C) 2004, 2007 Belaid Arezqui <areski _atl_ gmail com>
 *
 * See http://www.asterisk2billing.org for more information about
 * the A2Billing project. 
 * Please submit bug reports, patches, etc to <areski _atl_ gmail com>
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 ****************************************************************************/
 
 
class RateEngine {
	var $debug_st = 0;
	
	var $ratecard_obj = array();
	
	var $freetimetocall_left = array();
	
	var $number_trunk=0;
	var $lastcost=0;
	var $lastbuycost=0;
	var $answeredtime;
	var $dialstatus;
	var $usedratecard;
	var $webui = 1;
	var $usedtrunk			= 0;
	var $freetimetocall_used= 0;

	/* CONSTRUCTOR */

	function RateEngine () {     
		
	}
	
	
	/* CONSTRUCTOR */
	function Reinit () {     
		
		$this -> number_trunk = 0;
		$this -> answeredtime = 0;	
		$this -> dialstatus = '';
		$this -> usedratecard = '';
		$this -> usedtrunk = '';
		$this -> lastcost = '';
		$this -> lastbuycost = '';
		
	}
	
	
	/*
		RATE ENGINE
		CALCUL THE RATE ACCORDING TO THE RATEGROUP, LCR - RATECARD
	*/
	function rate_engine_findrates (&$A2B, $phonenumber, $tariffgroupid){
		
		global $agi;
		
		// Check if we want to force the call plan
		if (is_numeric($A2B->agiconfig['force_callplan_id']) && ($A2B->agiconfig['force_callplan_id'] > 0)){
			$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "force the call plan : ".$A2B->agiconfig['force_callplan_id']);
			$tariffgroupid = $A2B->tariff = $A2B->agiconfig['force_callplan_id'];
		}
		
		if ($this->webui){
			$A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CC_asterisk_rate-engine: ($tariffgroupid, $phonenumber)]");	
		}
		
		
		/***  0 ) CODE TO RETURN THE DAY OF WEEK + CREATE THE CLAUSE  ***/
		
		$daytag = date("w", time()); // Day of week ( Sunday = 0 .. Saturday = 6 )
		$hours = date("G", time()); // Hours in 24h format ( 0-23 )
		$minutes = date("i", time()); // Minutes (00-59)
		if (!$daytag) $daytag=6;
		else $daytag--;
		if ($this -> debug_st) echo "$daytag $hours $minutes <br>";
		// Race condiction on $minutes ?! 
		$minutes_since_monday = ($daytag * 1440) + ($hours * 60) + $minutes;
		if ($this -> debug_st) echo "$minutes_since_monday<br> ";
		//$sql_clause_days = " AND ".$daytable[$daytag]."='1' ";		
		$sql_clause_days = " AND (starttime <= ".$minutes_since_monday." AND endtime >=".$minutes_since_monday.") "; 
		
		/*
		
		SELECT tariffgroupname, lcrtype, idtariffgroup, cc_tariffgroup_plan.idtariffplan, tariffname, destination,
		
		cc_ratecard.id,
		dialprefix, destination, buyrate, buyrateinitblock, buyrateincrement, rateinitial, initblock, billingblock, 
		connectcharge, disconnectcharge, stepchargea, chargea, timechargea, billingblocka, stepchargeb, chargeb, 
		timechargeb, billingblockb, stepchargec, chargec, timechargec, billingblockc,	
		
		cc_tariffplan.id_trunk AS tp_id_trunk, tp_trunk.trunkprefix AS tp_trunk, tp_trunk.providertech AS tp_providertech, 
		tp_trunk.providerip AS tp_providerip, tp_trunk.removeprefix AS tp_removeprefix,
		cc_ratecard.id_trunk AS rc_id_trunk, rt_trunk.trunkprefix AS rc_trunkprefix, rt_trunk.providertech AS rc_providertech, 
		rt_trunk.providerip AS rc_providerip, rt_trunk.removeprefix AS rc_removeprefix
		
		FROM cc_tariffgroup 
		LEFT JOIN cc_tariffgroup_plan ON idtariffgroup=id
		LEFT JOIN cc_tariffplan ON cc_tariffplan.id=cc_tariffgroup_plan.idtariffplan
		LEFT JOIN cc_ratecard ON cc_ratecard.idtariffplan=cc_tariffplan.id
		LEFT JOIN trunk AS rt_trunk ON cc_ratecard.id_trunk=rt_trunk.id_trunk
		LEFT JOIN trunk AS tp_trunk ON cc_tariffplan.id_trunk=tp_trunk.id_trunk
		
		WHERE dialprefix=SUBSTRING('346586699595',1,length(dialprefix)) 
		AND startingdate<= CURRENT_TIMESTAMP AND (expirationdate > CURRENT_TIMESTAMP OR expirationdate IS NULL OR LENGTH(expirationdate)<5)
		AND startdate<= CURRENT_TIMESTAMP AND (stopdate > CURRENT_TIMESTAMP OR stopdate IS NULL OR LENGTH(stopdate)<5)
		ORDER BY LENGTH(dialprefix) DESC
		
		*/
		
		if (strlen($A2B->dnid)>=1) $mydnid = $A2B->dnid;
		if (strlen($A2B->CallerID)>=1) $mycallerid = $A2B->CallerID;
		
		if ($this->webui) $A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CC_asterisk_rate-engine: CALLERID]\n".$A2B->CallerID."\n",0);
		
		if ($A2B->config["database"]['dbtype'] != "postgres"){
			$DNID_QUERY = "SELECT count(dnidprefix) FROM cc_tariffplan RIGHT JOIN cc_tariffgroup_plan ON cc_tariffgroup_plan.idtariffgroup=$tariffgroupid WHERE dnidprefix=SUBSTRING('$mydnid',1,length(dnidprefix))";
			$result_sub = $A2B->instance_table -> SQLExec ($A2B -> DBHandle, $DNID_QUERY);
			if (!is_array($result_sub) || count($result_sub)==0) $nb_dnid = 0;
			else $nb_dnid = $result_sub[0][0];
			$DNID_SUB_QUERY = "AND 0 = $nb_dnid";
			
			$CALLERID_QUERY = "SELECT count(calleridprefix) FROM cc_tariffplan RIGHT JOIN cc_tariffgroup_plan ON cc_tariffgroup_plan.idtariffgroup=$tariffgroupid WHERE calleridprefix=SUBSTRING('$mycallerid',1,length(calleridprefix))";
			$result_sub = $A2B->instance_table -> SQLExec ($A2B -> DBHandle, $CALLERID_QUERY);
			if (!is_array($result_sub) || count($result_sub)==0) $nb_callerid = 0;
			else $nb_callerid = $result_sub[0][0];
			$CID_SUB_QUERY = "AND 0 = $nb_callerid";
			if ($this->webui) $A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CALLERID_QUERY]\n".$CALLERID_QUERY."\n[RESULT]".print_r($result_sub, 1)."\n",0);
			
		}else{
			$DNID_SUB_QUERY = "AND 0 = (SELECT count(dnidprefix) FROM cc_tariffplan RIGHT JOIN cc_tariffgroup_plan ON cc_tariffgroup_plan.idtariffgroup=$tariffgroupid WHERE dnidprefix=SUBSTRING('$mydnid',1,length(dnidprefix)) ) ";
			
			$CID_SUB_QUERY = "AND 0 = (SELECT count(calleridprefix) FROM cc_tariffplan RIGHT JOIN cc_tariffgroup_plan ON cc_tariffgroup_plan.idtariffgroup=$tariffgroupid WHERE calleridprefix=SUBSTRING('$mycallerid',1,length(calleridprefix)) ) ";
		}
		
		// $prefixclause to allow good DB servers to use an index rather than sequential scan
		// justification at http://forum.asterisk2billing.org/viewtopic.php?p=9620#9620
		$max_len_prefix = min(strlen($phonenumber), 15);	// don't match more than 15 digits (the most I have on my side is 8 digit prefixes)
		$prefixclause = '';
		while ($max_len_prefix > 0 ) {
			$prefixclause .= "dialprefix='".substr($phonenumber,0,$max_len_prefix)."' OR ";
			$max_len_prefix--;
		}
		$prefixclause .= "dialprefix='defaultprefix'";
		
		$QUERY = "SELECT 
		tariffgroupname, lcrtype, idtariffgroup, cc_tariffgroup_plan.idtariffplan, tariffname, destination,
		cc_ratecard.id,	dialprefix, destination, buyrate, buyrateinitblock, buyrateincrement, rateinitial, initblock, billingblock, 
		connectcharge, disconnectcharge, stepchargea, chargea, timechargea, billingblocka, stepchargeb, chargeb, 
		timechargeb, billingblockb, stepchargec, chargec, timechargec, billingblockc,
		cc_tariffplan.id_trunk AS tp_id_trunk, tp_trunk.trunkprefix AS tp_trunk, tp_trunk.providertech AS tp_providertech, 
		tp_trunk.providerip AS tp_providerip, tp_trunk.removeprefix AS tp_removeprefix,
		cc_ratecard.id_trunk AS rc_id_trunk, rt_trunk.trunkprefix AS rc_trunkprefix, rt_trunk.providertech AS rc_providertech, 
		rt_trunk.providerip AS rc_providerip, rt_trunk.removeprefix AS rc_removeprefix, musiconhold,
		tp_trunk.failover_trunk AS tp_failover_trunk, rt_trunk.failover_trunk AS rt_failover_trunk,
		tp_trunk.addparameter AS tp_addparameter_trunk, rt_trunk.addparameter AS rt_addparameter_trunk, id_outbound_cidgroup,
		freetimetocall_package_offer, freetimetocall, packagetype, billingtype, startday, id_cc_package_offer
		
		FROM cc_tariffgroup 
		RIGHT JOIN cc_tariffgroup_plan ON cc_tariffgroup_plan.idtariffgroup=cc_tariffgroup.id
		INNER JOIN cc_tariffplan ON (cc_tariffplan.id=cc_tariffgroup_plan.idtariffplan )
		LEFT JOIN cc_ratecard ON cc_ratecard.idtariffplan=cc_tariffplan.id
		LEFT JOIN cc_trunk AS rt_trunk ON cc_ratecard.id_trunk=rt_trunk.id_trunk
		LEFT JOIN cc_trunk AS tp_trunk ON cc_tariffplan.id_trunk=tp_trunk.id_trunk
		LEFT JOIN cc_package_offer ON cc_package_offer.id=cc_tariffgroup.id_cc_package_offer
		
		WHERE cc_tariffgroup.id=$tariffgroupid AND ($prefixclause)
		AND startingdate<= CURRENT_TIMESTAMP AND (expirationdate > CURRENT_TIMESTAMP OR expirationdate IS NULL OR LENGTH(expirationdate)<5)
		AND startdate<= CURRENT_TIMESTAMP AND (stopdate > CURRENT_TIMESTAMP OR stopdate IS NULL OR LENGTH(stopdate)<5)
		$sql_clause_days
		AND idtariffgroup='$tariffgroupid'
		AND ( dnidprefix=SUBSTRING('$mydnid',1,length(dnidprefix)) OR (dnidprefix='all' $DNID_SUB_QUERY)) 
		AND ( calleridprefix=SUBSTRING('$mycallerid',1,length(calleridprefix)) OR (calleridprefix='all' $CID_SUB_QUERY)) 
		ORDER BY LENGTH(dialprefix) DESC";
		
		if ($this->webui) $A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[RATE ENGINE QUERY]\n".$QUERY."\n",0);	
		
		$A2B->instance_table = new Table();
		$result = $A2B->instance_table -> SQLExec ($A2B -> DBHandle, $QUERY);
		
		if (!is_array($result) || count($result)==0) return 0; // NO RATE FOR THIS NUMBER
		
		if ($this->debug_st) echo "::> Count Total result ".count($result)."\n\n";
		if ($this->webui) $A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[rate-engine: Count Total result ".count($result)."]");	
		
		// CHECK IF THERE IS OTHER RATE THAT 'DEFAULT', IF YES REMOVE THE DEFAULT RATES
		// NOT NOT REMOVE SHIFT THEM TO THE END :P
		$ind_stop_default = -1;
		for ($i=0;$i<count($result);$i++){
			if ( $result[$i][7] != 'defaultprefix'){ 
				$ind_stop_default = $i; 
				break;
			}	
		}
		// IMPORTANT TO CUT THE PART OF THE defaultprefix CAUSE WE WILL APPLY THE SORT ACCORDING TO THE RATE
		// DEFAULPERFIX IS AN ESCAPE IN CASE OF NO RATE IS DEFINED, NOT BE COUNT WITH OTHER DURING THE SORT OF RATE
		if ($ind_stop_default>0) {
			$result_defaultprefix = array_slice ($result, 0, $ind_stop_default);
			$result = array_slice ($result, $ind_stop_default, count($result)-$ind_stop_default);
		}
		
		//1) REMOVE THOSE THAT HAVE A SMALLER DIALPREFIX
		$max_len_prefix = strlen($result[0][7]);
		for ($i=1;$i<count($result);$i++){
			if ( strlen($result[$i][7]) < $max_len_prefix) break;
		}	
		$result = array_slice ($result, 0, $i);
		
		
		//2) TAKE THE VALUE OF LCTYPE 
		//LCR : According to the buyer price	-0 	buyrate [col 6]
		//LCD : According to the seller price	-1  rateinitial	[col 9]
		$LCtype = $result[0][1];
		
		// Thanks for the fix from the wiki :D next time email me, lol
		if ($LCtype==0){
			$result = $this -> array_csort($result,'9',SORT_ASC); //1
		}else{
			$result = $this -> array_csort($result,'12',SORT_ASC); //1
		}
		
		// WE ADD THE DEFAULTPREFIX WE REMOVE BEFORE
		if ($ind_stop_default>0) {
			$result = array_merge ($result, $result_defaultprefix);
		}
		
		// 3) REMOVE THOSE THAT USE THE SAME TRUNK - MAKE A DISTINCT
		$mylistoftrunk = array();
		for ($i=0;$i<count($result);$i++){
			
			if ($result[$i][34]==-1) $mylistoftrunk_next[]= $mycurrenttrunk = $result[$i][29];
			else $mylistoftrunk_next[]= $mycurrenttrunk = $result[$i][34];
			
			// Check if we already have the same trunk in the ratecard 
			if ($i==0 || !in_array ($mycurrenttrunk , $mylistoftrunk)) {
				$distinct_result[] = $result[$i];			
			}	
			
			$mylistoftrunk[]= $mycurrenttrunk;				
		}
		
		$this -> ratecard_obj = $distinct_result;
		$this -> number_trunk = count($distinct_result);
		
		// if an extracharge DID number was called increase rates with the extracharge fee
		if (strlen($A2B->dnid)>2 && is_array($A2B->agiconfig['extracharge_did']) && in_array($A2B->dnid, $A2B->agiconfig['extracharge_did']))
		{
			$fee=$A2B->agiconfig['extracharge_fee'][array_search($A2B->dnid, $A2B->agiconfig['extracharge_did'])];
			$buyfee=$A2B->agiconfig['extracharge_buyfee'][array_search($A2B->dnid, $A2B->agiconfig['extracharge_did'])];
			$A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CC_asterisk_rate-engine: Extracharge DID found: ".$A2B->dnid.", extra fee: ".$fee.", extra buy fee: ".$buyfee."]");
			for ($i=0; $i<count($this->ratecard_obj); $i++)
			{
				$this->ratecard_obj[$i][9] +=$buyfee;
				$this->ratecard_obj[$i][12]+=$fee;
			}
		}
		
		if ($this -> debug_st)echo "::> Count Total result ".count($distinct_result)."\n\n";
		if ($this->webui) $A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CC_asterisk_rate-engine: Count Total result ".count($distinct_result)."]");	
		if ($this->webui) $A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CC_asterisk_rate-engine: number_trunk ".$this -> number_trunk."]");
		
		return 1;
	}
	
	/*
		RATE ENGINE - CALCUL TIMEOUT
		* CALCUL THE DURATION ALLOWED FOR THE CALLER TO THIS NUMBER
	*/
	function rate_engine_all_calcultimeout (&$A2B, $credit){
			
		global $agi;
		if ($this->webui) $A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CC_RATE_ENGINE_ALL_CALCULTIMEOUT ($credit)]");
		if (!is_array($this -> ratecard_obj) || count($this -> ratecard_obj)==0) return false;
		
		for ($k=0;$k<count($this -> ratecard_obj);$k++){
			$res_calcultimeout = $this -> rate_engine_calcultimeout ($A2B,$credit,$k);
			if ($this->webui) 
				$A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CC_RATE_ENGINE_ALL_CALCULTIMEOUT: k=$k - res_calcultimeout:$res_calcultimeout]");
			if (substr($res_calcultimeout,0,5)=='ERROR')	return false;
		}	
			
		return true;
	}
	
	/*
		RATE ENGINE - CALCUL TIMEOUT
		* CALCUL THE DURATION ALLOWED FOR THE CALLER TO THIS NUMBER
	*/
	function rate_engine_calcultimeout (&$A2B, $credit, $K=0){

		global $agi;
		$rateinitial = round(abs($this -> ratecard_obj[$K][12]),4);
		$initblock = $this -> ratecard_obj[$K][13];
		$billingblock = $this -> ratecard_obj[$K][14];	
		$connectcharge = round(abs($this -> ratecard_obj[$K][15]),4);
		$disconnectcharge = round(abs($this -> ratecard_obj[$K][16]),4);	
		$stepchargea = $this -> ratecard_obj[$K][17]; 		$chargea = round(abs($this -> ratecard_obj[$K][18]),4);
		$timechargea = $this -> ratecard_obj[$K][19];		$billingblocka = $this -> ratecard_obj[$K][20];	
		$stepchargeb = $this -> ratecard_obj[$K][21];		$chargeb = round(abs($this -> ratecard_obj[$K][22]),4);
		$timechargeb = $this -> ratecard_obj[$K][23];		$billingblockb = $this -> ratecard_obj[$K][24];	
		$stepchargec = $this -> ratecard_obj[$K][25];		$chargec = round(abs($this -> ratecard_obj[$K][26]),4);	
		$timechargec = $this -> ratecard_obj[$K][27];		$billingblockc = $this -> ratecard_obj[$K][28];
		
		// ****************  PACKAGE PARAMETERS ****************  
		$freetimetocall_package_offer = $this -> ratecard_obj[$K][45];
		$freetimetocall = $this -> ratecard_obj[$K][46];
		$packagetype = $this -> ratecard_obj[$K][47];
		$billingtype = $this -> ratecard_obj[$K][48];
		$startday = $this -> ratecard_obj[$K][49];
		$id_cc_package_offer = $this -> ratecard_obj[$K][50];
		
		// CHANGE THIS - ONLY ALLOW FREE TIME FOR CUSTOMER THAT HAVE MINIMUM CREDIT TO CALL A DESTINATION
		
		// WE HAVE THE SAME CALL PLAN FOR ALL SO WE CAN RESTRICT THIS CALCULATION TO 1 BEFORE EACH CALL
		$this -> freetimetocall_left[$K] = 0;
		if ($K == 0){
			// CHECK IF WE HAVE A FREETIME THAT CAN APPLY FOR THIS DESTINATION
			if ($freetimetocall_package_offer==1 && $freetimetocall>0){
				// WE NEED TO RETRIEVE THE AMOUNT OF USED MINUTE FOR THIS CUSTOMER ACCORDING TO BILLINGTYPE (Monthly ; Weekly) & STARTDAY
				if ($billingtype == 0){
					// PROCESSING FOR MONTHLY
					// if > last day of the month
					if ($startday > date("t")) $startday = date("t");
					if ($startday <= 0 ) $startday = 1;
					
					// Check if the startday is upper that the current day
					if ($startday > date("j")) $year_month = date('Y-m', strtotime('-1 month'));
					else $year_month = date('Y-m');
					
					$yearmonth = sprintf("%s-%02d",$year_month,$startday);
					if ($A2B->config["database"]['dbtype'] == "postgres"){
	 					$UNIX_TIMESTAMP = "";
					}else{
						$UNIX_TIMESTAMP = "UNIX_TIMESTAMP";
					}
					$CLAUSE_DATE=" $UNIX_TIMESTAMP(date_consumption) >= $UNIX_TIMESTAMP('$yearmonth')";
				}else{
					// PROCESSING FOR WEEKLY
					$startday = $startday % 7;
					$dayofweek = date("w"); // Numeric representation of the day of the week 0 (for Sunday) through 6 (for Saturday)
					if ($dayofweek==0) $dayofweek=7;
					if ($dayofweek < $startday) $dayofweek = $dayofweek + 7;
					$diffday = $dayofweek - $startday;
					if ($A2B->config["database"]['dbtype'] == "postgres"){
	 					$CLAUSE_DATE = "date_consumption >= (CURRENT_DATE - interval '$diffday day') ";
					}else{
						$CLAUSE_DATE = "date_consumption >= DATE_SUB(CURRENT_DATE, INTERVAL $diffday DAY) ";
					}
				}
				$QUERY = "SELECT  sum(used_secondes) AS used_secondes FROM cc_card_package_offer ".
						" WHERE $CLAUSE_DATE AND id_cc_card = '".$A2B->id_card."' AND id_cc_package_offer = '$id_cc_package_offer' ";
				
				$pack_result = $A2B -> instance_table -> SQLExec ($A2B -> DBHandle, $QUERY);
				
				if (is_array($pack_result) && count($pack_result)>0) {
					$this->freetimetocall_used = $pack_result[0][0];
				} else {
					$this->freetimetocall_used = 0;
				}
				
				$A2B -> write_log ('line:'.__LINE__." - PACK USED TIME : $QUERY ; RESULT -> $this->freetimetocall_used");
				if ($this -> debug_st) echo ('line:'.__LINE__." - PACK USED TIME : $QUERY ; RESULT -> $this->freetimetocall_used");
				
				$this -> freetimetocall_left[$K] = $freetimetocall - $this->freetimetocall_used;
				if ($this -> freetimetocall_left[$K] < 0) $this -> freetimetocall_left[$K] = 0;
				
			}
		}
		if ($this -> debug_st) print_r($this -> freetimetocall_left);
		// ****************  END PACKAGE PARAMETERS ****************  
		
		$credit -= $connectcharge;
		$credit -= $disconnectcharge;
		//$credit -= ($initblock/60)*$rateinitial;
		
		$callbackrate = array();
		
		if(($A2B->mode == 'cid-callback') || ($A2B->mode == 'all-callback')){
			$callbackrate['rateinitial']=$rateinitial;
			$callbackrate['initblock']=$initblock;
			$callbackrate['billingblock']=$billingblock;
			$callbackrate['connectcharge']=$connectcharge;
			$callbackrate['disconnectcharge']=$disconnectcharge;
			$callbackrate['stepchargea']=$stepchargea;
			$callbackrate['timechargea']=$timechargea;
			$callbackrate['stepchargeb']=$stepchargeb;
			$callbackrate['timechargeb']=$timechargeb;
			$callbackrate['stepchargec']=$stepchargec;
			$callbackrate['timechargec']=$timechargec;
		}

		$this -> ratecard_obj[$K]['callbackrate']=$callbackrate;
		$this -> ratecard_obj[$K]['timeout']=0;
		
		// CHECK IF THE USER IS ALLOW TO CALL WITH ITS CREDIT AMOUNT
		/*
		Comment from Abdoulaye Siby
		This following "if" statement used to verify the minimum credit to call can be improved.
		This mininum credit should be calculated based on the destination, and the minimum billing block.
		*/
		if ($credit < $A2B->agiconfig['min_credit_2call']){
			return "ERROR CT1";  //NO ENOUGH CREDIT TO CALL THIS NUMBER
		}
		
		// if ($rateinitial==0) return "ERROR RATEINITIAL($rateinitial)";
		$TIMEOUT = 0;
		$answeredtime_1st_leg = 0;
		
		if ($rateinitial<=0){
			$this -> ratecard_obj[$K]['timeout']= $A2B->agiconfig['maxtime_tocall_negatif_free_route']; // 90 min
			if ($this -> debug_st) print_r($this -> ratecard_obj[$K]);
			return $TIMEOUT;
		}
		
		if($A2B->mode == 'callback'){
			$calling_party_rateinitial = $agi->get_variable('RATEINITIAL', true);
			$calling_party_initblock = $agi->get_variable('INITBLOCK', true);
			$calling_party_billingblock = $agi->get_variable('BILLINGBLOCK', true);
			$calling_party_connectcharge = $agi->get_variable('CONNECTCHARGE', true);
			$calling_party_disconnectcharge = $agi->get_variable('DISCONNECTCHARGE', true);
			$calling_party_stepchargea = $agi->get_variable('STEPCHARGEA', true);
			$calling_party_timechargea = $agi->get_variable('TIMECHARGEA', true);
			$calling_party_stepchargeb = $agi->get_variable('STEPCHARGEB', true);
			$calling_party_timechargeb = $agi->get_variable('TIMECHARGEB', true);
			$calling_party_stepchargec = $agi->get_variable('STEPCHARGEC', true);
			$calling_party_timechargec = $agi->get_variable('TIMECHARGEC', true);
		}
		
		// 2 KIND OF CALCULATION : PROGRESSIVE RATE & FLAT RATE
		// IF FLAT RATE 
		if (empty($chargea) || $chargea==0 || empty($timechargea) || $timechargea==0 ){
			
			if($A2B->mode == 'callback'){
				/*
				Comment from Abdoulaye Siby
				In all-callback or cid-callback mode, the number of minutes for the call must be calculated 
				according to the rates of both legs of the call.
				*/
				
				$credit -= $calling_party_connectcharge;
				$credit -= $calling_party_disconnectcharge;
				$num_min = $credit/($rateinitial + $calling_party_rateinitial);
				//I think that the answered time is in seconds
				$answeredtime_1st_leg = intval($agi->get_variable('ANSWEREDTIME', true));
			} else {
				$num_min = $credit/$rateinitial;
			}
			
			if ($this -> debug_st) echo "num_min:$num_min ($credit/$rateinitial)\n";			
			$num_sec = intval($num_min * 60) - $answeredtime_1st_leg;
			if ($this -> debug_st) echo "num_sec:$num_sec \n";
			
			if ($billingblock > 0) {
				$mod_sec = $num_sec % $billingblock; 	
				$num_sec=$num_sec-$mod_sec;
			}
			
			$TIMEOUT = $num_sec;
		
		// IF PROGRESSIVE RATE 
		}else{
			if ($this -> debug_st) echo "CYCLE A	TIMEOUT:$TIMEOUT\n";
			// CYCLE A
			$credit -= $stepchargea;
			
			//if ($credit<=0) return "ERROR CT2";		//NO ENOUGH CREDIT TO CALL THIS NUMBER
			if ($credit<=0){
				if ($this -> freetimetocall_left[$K] > 0){
					$this -> ratecard_obj[$K]['timeout'] = $this -> freetimetocall_left[$K];
					if ($this -> debug_st) print_r($this -> ratecard_obj[$K]);
					return $this -> freetimetocall_left[$K];
				}else{
					return "ERROR CT2";		//NO ENOUGH CREDIT TO CALL THIS NUMBER
				}
			}
			if (!($chargea>0)) return "ERROR CHARGEA($chargea)";
			$num_min = $credit/$chargea;
			if ($this -> debug_st) echo "			CYCLEA num_min:$num_min ($credit/$chargea)\n";			
			$num_sec = intval($num_min * 60);
			if ($this -> debug_st) echo "			CYCLEA num_sec:$num_sec \n";
			if ($billingblocka > 0) {
				$mod_sec = $num_sec % $billingblocka;
				$num_sec=$num_sec-$mod_sec;
			}
			
			
			if (($num_sec>$timechargea) && !(empty($chargeb) || $chargeb==0 || empty($timechargeb) || $timechargeb==0) ){
				$TIMEOUT += $timechargea;
				$credit -= ($chargea/60) * $timechargea;

				if ($this -> debug_st) echo "		CYCLE B		TIMEOUT:$TIMEOUT\n";
				// CYCLE B
				$credit -= $stepchargeb;
				if ($credit<=0){
					$this -> ratecard_obj[$K]['timeout'] = $TIMEOUT + $this -> freetimetocall_left[$K];
					return $TIMEOUT + $this -> freetimetocall_left[$K];		//NO ENOUGH CREDIT TO GO TO THE CYCLE B
				}
				
				if (!($chargeb>0)) return "ERROR CHARGEB($chargeb)";
				$num_min = $credit/$chargeb;
				if ($this -> debug_st) echo "			CYCLEB num_min:$num_min ($credit/$chargeb)\n";			
				$num_sec = intval($num_min * 60);
				if ($this -> debug_st) echo "			CYCLEB num_sec:$num_sec \n";
				if ($billingblockb > 0) {
					$mod_sec = $num_sec % $billingblockb;
					$num_sec=$num_sec-$mod_sec;
				}
 
				if (($num_sec>$timechargeb) && !(empty($chargec) || $chargec==0 || empty($timechargec) || $timechargec==0) )
				{
					$TIMEOUT += $timechargeb;
					$credit -= ($chargeb/60) * $timechargeb;
						
					if ($this -> debug_st) echo "				CYCLE C		TIMEOUT:$TIMEOUT\n";
					// CYCLE C
					$credit -= $stepchargec;
					if ($credit<=0){
						$this -> ratecard_obj[$K]['timeout'] = $TIMEOUT + $this -> freetimetocall_left[$K];
						return $TIMEOUT + $this -> freetimetocall_left[$K];		//NO ENOUGH CREDIT TO GO TO THE CYCLE C
					}
					
					if (!($chargec>0)) return "ERROR CHARGEC($chargec)";
					$num_min = $credit/$chargec;
					if ($this -> debug_st) echo "			CYCLEC num_min:$num_min ($credit/$chargec)\n";			
					$num_sec = intval($num_min * 60);
					if ($this -> debug_st) echo "			CYCLEC num_sec:$num_sec \n";
					if ($billingblockc > 0) {
						$mod_sec = $num_sec % $billingblockc;
						$num_sec=$num_sec-$mod_sec; 
					}
							
						
					if (($num_sec>$timechargec)){
						if ($this -> debug_st) echo "		OUT CYCLE C		TIMEOUT:$TIMEOUT\n";
						$TIMEOUT += $timechargec;
						$credit -= ($chargec/60) * $timechargec;
								
						// IF CYCLE C IS FINISH USE THE RATEINITIAL								
						$num_min = $credit/$rateinitial;
						if ($this -> debug_st) echo "			OUT CYCLEC num_min:$num_min ($credit/$rateinitial)\n";			
						$num_sec = intval($num_min * 60);
						if ($this -> debug_st) echo "			OUT CYCLEC num_sec:$num_sec \n";
						if ($billingblock > 0) {
							$mod_sec = $num_sec % $billingblock;
							$num_sec=$num_sec-$mod_sec;
						}
						$TIMEOUT += $num_sec;								
						// THIS IS THE END
							
					}else{
						$TIMEOUT += $num_sec;
					}					
					
				}else{
						
					if (($num_sec>$timechargeb)){
						$TIMEOUT += $timechargeb;
						if ($this -> debug_st) echo "		OUT CYCLE B		TIMEOUT:$TIMEOUT\n";
						$credit -= ($chargeb/60) * $timechargeb;
							
						// IF CYCLE B IS FINISH USE THE RATEINITIAL
						$num_min = $credit/$rateinitial;
						if ($this -> debug_st) echo "			OUT CYCLEB num_min:$num_min ($credit/$rateinitial)\n";			
						$num_sec = intval($num_min * 60);
						if ($this -> debug_st) echo "			OUT CYCLEB num_sec:$num_sec \n";
						if ($billingblock > 0) {
							$mod_sec = $num_sec % $billingblock;
							$num_sec=$num_sec-$mod_sec; 
						}
							
							$TIMEOUT += $num_sec;								
							// THIS IS THE END
						
					}else{
						$TIMEOUT += $num_sec;
					}	
						
				}
			
			}else{
				
				if (($num_sec>$timechargea)){				
					$TIMEOUT += $timechargea;
					if ($this -> debug_st) echo "		OUT CYCLE A		TIMEOUT:$TIMEOUT\n";
					$credit -= ($chargea/60) * $timechargea;
					
					// IF CYCLE A IS FINISH USE THE RATEINITIAL
					$num_min = $credit/$rateinitial;
					if ($this -> debug_st) echo "			OUT CYCLEA num_min:$num_min ($credit/$rateinitial)\n";			
					$num_sec = intval($num_min * 60);
					if ($this -> debug_st) echo "			OUT CYCLEA num_sec:$num_sec \n";;
					if ($billingblock > 0) {
						$mod_sec = $num_sec % $billingblock;
						$num_sec=$num_sec-$mod_sec; 
					}
					
					$TIMEOUT += $num_sec;								
					// THIS IS THE END
				
				}else{
					$TIMEOUT += $num_sec;
				}	
		
			}		
		}
		$this -> ratecard_obj[$K]['timeout']=$TIMEOUT + $this -> freetimetocall_left[$K];
		if ($this -> debug_st) print_r($this -> ratecard_obj[$K]);
		RETURN $TIMEOUT + $this -> freetimetocall_left[$K];
	}


	/*
		RATE ENGINE - CALCUL COST OF THE CALL
		* CALCUL THE CREDIT COSUMED BY THE CALL
	*/
	function rate_engine_calculcost (&$A2B, $callduration, $K=0)
	{
		global $agi;
		$K = $this->usedratecard;
		$buyrate = round(abs($this -> ratecard_obj[$K][9]),4);
		$buyrateinitblock = $this -> ratecard_obj[$K][10];
		$buyrateincrement = $this -> ratecard_obj[$K][11];
		
		$rateinitial = round(abs($this -> ratecard_obj[$K][12]),4);
		$initblock = $this -> ratecard_obj[$K][13];
		$billingblock = $this -> ratecard_obj[$K][14];	
		$connectcharge = round(abs($this -> ratecard_obj[$K][15]),4);
		$disconnectcharge = round(abs($this -> ratecard_obj[$K][16]),4);	
		$stepchargea = $this -> ratecard_obj[$K][17]; 		$chargea = round(abs($this -> ratecard_obj[$K][18]),4);
		$timechargea = $this -> ratecard_obj[$K][19];		$billingblocka = $this -> ratecard_obj[$K][20];	
		$stepchargeb = $this -> ratecard_obj[$K][21];		$chargeb = round(abs($this -> ratecard_obj[$K][22]),4);
		$timechargeb = $this -> ratecard_obj[$K][23];		$billingblockb = $this -> ratecard_obj[$K][24];	
		$stepchargec = $this -> ratecard_obj[$K][25];		$chargec = round(abs($this -> ratecard_obj[$K][26]),4);	
		$timechargec = $this -> ratecard_obj[$K][27];		$billingblockc = $this -> ratecard_obj[$K][28];
		
		if (!is_numeric($this->freetimetocall_used))	$this->freetimetocall_used = 0;		
		if ($this -> debug_st)  echo "CALLDURATION: $callduration - freetimetocall_used=$this->freetimetocall_used\n\n";
		$A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CC_RATE_ENGINE_CALCULCOST: K=$K - CALLDURATION:$callduration - freetimetocall_used=$this->freetimetocall_used]");
		
		$cost =0;
		$cost -= $connectcharge;
		$cost -= $disconnectcharge;
		
		// CALCULATION BUYRATE COST
		$buyratecallduration = $callduration;
		
		$buyratecost =0;
		if ($buyratecallduration<$buyrateinitblock) $buyratecallduration=$buyrateinitblock;
		if ($buyrateincrement > 0) {	
			$mod_sec = $buyratecallduration % $buyrateincrement;
			if ($mod_sec>0) $buyratecallduration += ($buyrateincrement - $mod_sec);
		}
		
		$buyratecost -= ($buyratecallduration/60) * $buyrate;

		if ($this -> debug_st)  echo "1. cost: $cost\n buyratecost:$buyratecost\n";
		
		// #### 	CALCUL SELLRATE COST   #####
		if ($callduration < $initblock) $callduration = $initblock;
		if ($this -> freetimetocall_left[$K] >= $callduration) {
			$this -> freetimetocall_used = $callduration;
		}
		
		$callduration = $callduration - $this->freetimetocall_used;
		
		// 2 KIND OF CALCULATION : PROGRESSIVE RATE & FLAT RATE
		// IF FLAT RATE 
		if (empty($chargea) || $chargea==0 || empty($timechargea) || $timechargea==0 ){
			
			if ($billingblock > 0) {	
				$mod_sec = $callduration % $billingblock;  
				if ($mod_sec>0) $callduration += ($billingblock - $mod_sec);
			}
			
			$cost -= ($callduration/60) * $rateinitial;	
			if ($this -> debug_st)  echo "1.a cost: $cost\n";
			$A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[TEMP - CC_RATE_ENGINE_CALCULCOST: 1. COST: $cost]:[ ($callduration/60) * $rateinitial ]");
		
		// IF PROGRESSIVE RATE 
		}else{
			if ($this -> debug_st) echo "CYCLE A	COST:$cost\n";
			// CYCLE A
			$cost -= $stepchargea;
			if ($this -> debug_st)  echo "1.A cost: $cost\n\n";
			
			if ($callduration>$timechargea){ $duration_report = $callduration-$timechargea; $callduration=$timechargea; }
			
			if ($billingblocka > 0) {	
				$mod_sec = $callduration % $billingblocka;  
				if ($mod_sec>0) $callduration += ($billingblocka - $mod_sec);
			}
			$cost -= ($callduration/60) * $chargea;
			
			if (($duration_report>0) && !(empty($chargeb) || $chargeb==0 || empty($timechargeb) || $timechargeb==0) )
			{
				$callduration=$duration_report;
				$duration_report=0;				
				
				// CYCLE B
				$cost -= $stepchargeb;
				if ($this -> debug_st)  echo "1.B cost: $cost\n\n";
					
				if ($callduration>$timechargeb){ 
					$duration_report = $callduration-$timechargeb; 
					$callduration=$timechargeb;
				}
					
				if ($billingblockb > 0) {	
					$mod_sec = $callduration % $billingblockb;  
					if ($mod_sec>0) $callduration += ($billingblockb - $mod_sec);
				}
				$cost -= ($callduration/60) * $chargeb; // change chargea -> chargeb thanks to Abbas :D
					
				if (($duration_report>0) && !(empty($chargec) || $chargec==0 || empty($timechargec) || $timechargec==0) )
				{
						
					$callduration=$duration_report;
					$duration_report=0;						
						
					// CYCLE C
					$cost -= $stepchargec;
					if ($this -> debug_st)  echo "1.C cost: $cost\n\n";
							
					if ($callduration>$timechargec){ 
						$duration_report = $callduration-$timechargec; 
						$callduration=$timechargec; 
					}
							
					if ($billingblockc > 0) {	
						$mod_sec = $callduration % $billingblockc;  
						if ($mod_sec>0) $callduration += ($billingblockc - $mod_sec);
					}
					$cost -= ($callduration/60) * $chargec;
							
				}
			}
			
			if ($duration_report>0){
			
				if ($duration_report<$initblock) $duration_report=$initblock;
		
				if ($billingblock > 0) {	
					$mod_sec = $duration_report % $billingblock;  
					if ($mod_sec>0) $duration_report += ($billingblock - $mod_sec);
				}
				
				$cost -= ($duration_report/60) * $rateinitial;				
				$A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[TEMP - CC_RATE_ENGINE_CALCULCOST: 2. DURATION_REPORT:$duration_report - COST: $cost]");
			}
			
		}
		$cost = round($cost,4);
		if ($this -> debug_st)  echo "FINAL COST: $cost\n\n";
		$A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CC_RATE_ENGINE_CALCULCOST: K=$K - BUYCOST: $buyratecost - SELLING COST: $cost]");
		$this -> lastcost = $cost;
		$this -> lastbuycost = $buyratecost;
	}


    /* 
		SORT_ASC : Tri en ordre ascendant
      	SORT_DESC : Tri en ordre descendant
	*/
	function array_csort() 
	{  
		$args = func_get_args();
		$marray = array_shift($args);
		$i=0;
		$msortline = "return(array_multisort(";
		foreach ($args as $arg) {
			$i++;
			if (is_string($arg)) {
				foreach ($marray as $row) {
					$sortarr[$i][] = $row[$arg];
				}
			} else {
				$sortarr[$i] = $arg;
			}
			$msortline .= "\$sortarr[".$i."],";
		}
		$msortline .= "\$marray));";
		
		eval($msortline);
		return $marray;
	} 
	
	
	/*
	 * RATE ENGINE - UPDATE SYSTEM (DURATIONCALL)
	 * Calcul the duration allowed for the caller to this number
	 */
	function rate_engine_updatesystem (&$A2B, &$agi, $calledstation, $doibill = 1, $didcall=0, $callback=0){
		
		$K = $this->usedratecard;
		// ****************  PACKAGE PARAMETERS ****************  
		$freetimetocall_package_offer = $this -> ratecard_obj[$K][45];
		$freetimetocall = $this -> ratecard_obj[$K][46];
		$id_cc_package_offer = $this -> ratecard_obj[$K][50];
		
		if ($A2B -> CC_TESTING){ 
			$sessiontime = 120;
			$dialstatus = 'ANSWERED';
		} else {
			$sessiontime = $this -> answeredtime;
			$dialstatus = $this -> dialstatus;
		}
		
		$id_card_package_offer = 0;
		if ($sessiontime > 0) { 
			// HANDLE FREETIME BEFORE CALCULATE THE COST
			$this->freetimetocall_used = 0;
			$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "ft2c_package_offer : $freetimetocall_package_offer ; $freetimetocall ; ".$this -> freetimetocall_left[$K]);
			if ($this -> debug_st) print_r($this -> freetimetocall_left[$K]);
			
			if (($freetimetocall_package_offer==1) && ($freetimetocall > 0) && ($this -> freetimetocall_left[$K] > 0)){
				
				if ($this -> freetimetocall_left[$K] >= $sessiontime){ 
					$this->freetimetocall_used = $sessiontime;
				}else{
					$this->freetimetocall_used = $this -> freetimetocall_left[$K];
				}
				
				$this -> rate_engine_calculcost ($A2B, $sessiontime, 0);
				// rate_engine_calculcost could have change the duration of the call
				$sessiontime = $this -> answeredtime;
				
				$QUERY_FIELS = 'id_cc_card, id_cc_package_offer, used_secondes';
				$QUERY_VALUES = "'".$A2B -> id_card."', '$id_cc_package_offer', '$this->freetimetocall_used'";
				$id_card_package_offer = $A2B -> instance_table -> Add_table ($A2B -> DBHandle, $QUERY_VALUES, $QUERY_FIELS, 'cc_card_package_offer', 'id');
				$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, ":[ID_CARD_PACKAGE_OFFER CREATED : $id_card_package_offer]:[$QUERY_VALUES]");
			} else {
				
				$this -> rate_engine_calculcost ($A2B, $sessiontime, 0);
				
				// rate_engine_calculcost could have change the duration of the call
				$sessiontime = $this -> answeredtime;
			}
			
		}else{
			$sessiontime=0;
		}
		
		$calldestination = $this -> ratecard_obj[$K][5];
		$id_tariffgroup = $this -> ratecard_obj[$K][2];
		$id_tariffplan = $this -> ratecard_obj[$K][3];
		$id_ratecard = $this -> ratecard_obj[$K][6];
		
		$buycost = 0;
		if ($doibill==0 || $sessiontime < $A2B->agiconfig['min_duration_2bill']) $cost = 0;		
		else{ 
			$cost = $this -> lastcost;
			$buycost = abs($this -> lastbuycost);
		}
		//else $cost = abs($this -> lastcost);
		
		if ($cost<0){ 
			$signe='-';
			$signe_cc_call ='+';
		}else{ 
			$signe='+';  
			$signe_cc_call ='-';
		}
		
		$buyrateapply = $this -> ratecard_obj[$K][9];
		$rateapply = $this -> ratecard_obj[$K][12];
		
		$A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CC_RATE_ENGINE_UPDATESYSTEM: usedratecard K=$K - (sessiontime=$sessiontime :: dialstatus=$dialstatus :: buycost=$buycost :: cost=$cost : signe_cc_call=$signe_cc_call: signe=$signe)]");
		
		// CALLTYPE -  0 = NORMAL CALL ; 1 = VOIP CALL (SIP/IAX) ; 2= DIDCALL + TRUNK ; 3 = VOIP CALL DID ; 4 = CALLBACK call		
		if ($didcall) $calltype = 2;
		elseif ($callback) $calltype = 4;
		else $calltype = 0;
		
		
		// MYSQL SELECT now() - INTERVAL 300 SECOND;
		// PGSQL SELECT now() - interval '300 seconds'
		$QUERY = "INSERT INTO cc_call (uniqueid,sessionid,username,nasipaddress,starttime,sessiontime, calledstation, ".
			" terminatecause, stoptime, calledrate, sessionbill, calledcountry, calledsub, destination, id_tariffgroup, id_tariffplan, id_ratecard, id_trunk, src, sipiax, buyrate, buycost, id_card_package_offer) VALUES ".
			"('".$A2B->uniqueid."', '".$A2B->channel."',  '".$A2B->username."', '".$A2B->hostname."', ";
			
		if ($A2B->config["database"]['dbtype'] == "postgres"){			
			$QUERY .= "CURRENT_TIMESTAMP - interval '$sessiontime seconds' ";
		}else{
			$QUERY .= "CURRENT_TIMESTAMP - INTERVAL $sessiontime SECOND ";
		}
		
		$QUERY .= ", '$sessiontime', '$calledstation', '$dialstatus', now(), '$rateapply', '$signe_cc_call".round(abs($cost),4)."', ".
			" '', '', '$calldestination', '$id_tariffgroup', '$id_tariffplan', '$id_ratecard', '".$this -> usedtrunk."', '".$A2B->CallerID."', '$calltype', '$buyrateapply', '$buycost', '$id_card_package_offer')";
		
		
		$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[CC_asterisk_stop  QUERY = $QUERY]");
		
		$result = $A2B->instance_table -> SQLExec ($A2B -> DBHandle, $QUERY, 0);
		$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[CC_asterisk_stop 1.1: SQL: DONE : result=".$result."]");
		
		
		if ($sessiontime>0){
			//Update the global credit	
			$A2B -> credit = $A2B -> credit + $cost;
			
			if ($didcall==0 && $callback==0) $myclause_nodidcall = " , redial='".$calledstation."' ";
			else $myclause_nodidcall='';
			
			
			if ($A2B->nbused>0){
				$QUERY = "UPDATE cc_card SET credit= credit$signe".round(abs($cost),4)." $myclause_nodidcall,  lastuse=now(), nbused=nbused+1 WHERE username='".$A2B->username."'";
			}else{
				$QUERY = "UPDATE cc_card SET credit= credit$signe".round(abs($cost),4)." $myclause_nodidcall,  lastuse=now(), firstusedate=now(), nbused=nbused+1 WHERE username='".
				$A2B->username."'";
			}
			
			$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[CC_asterisk_stop 1.2: SQL: $QUERY]");
			$result = $A2B->instance_table -> SQLExec ($A2B -> DBHandle, $QUERY, 0);
				
				
			$QUERY = "UPDATE cc_trunk SET secondusedreal = secondusedreal + $sessiontime WHERE id_trunk='".$this -> usedtrunk."'";
			$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, $QUERY);
			$result = $A2B->instance_table -> SQLExec ($A2B -> DBHandle, $QUERY, 0);
			
			$QUERY = "UPDATE cc_tariffplan SET secondusedreal = secondusedreal + $sessiontime WHERE id='$id_tariffplan'";
			$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, $QUERY);
			$result = $A2B->instance_table -> SQLExec ($A2B -> DBHandle, $QUERY, 0);
		}
	}
	
	
	/*
		RATE ENGINE - PERFORM CALLS
		$typecall = 1 -> predictive dialer
	*/
	function rate_engine_performcall ($agi, $destination, &$A2B, $typecall=0){
		$max_long = 2147483647; //Maximum value for long type in C++. This will be used to avoid overflow when sending large numbers to Asterisk 

		$old_destination = $destination;
		
		for ($k=0;$k<count($this -> ratecard_obj);$k++){
				
			$destination=$old_destination;
			if ($this -> ratecard_obj[$k][34]!='-1'){
				$usetrunk=34;
				$this -> usedtrunk = $this -> ratecard_obj[$k][34];
				$usetrunk_failover=1;
			}
			else {
				$usetrunk=29;
				$this -> usedtrunk = $this -> ratecard_obj[$k][29];
				$usetrunk_failover=0;
			}
			
			$prefix			= $this -> ratecard_obj[$k][$usetrunk+1];
			$tech 			= $this -> ratecard_obj[$k][$usetrunk+2];
			$ipaddress 		= $this -> ratecard_obj[$k][$usetrunk+3];
			$removeprefix 	= $this -> ratecard_obj[$k][$usetrunk+4];
			$timeout		= $this -> ratecard_obj[$k]['timeout'];
			$musiconhold	= $this -> ratecard_obj[$k][39];
			$failover_trunk	= $this -> ratecard_obj[$k][40+$usetrunk_failover];
			$addparameter	= $this -> ratecard_obj[$k][42+$usetrunk_failover];
			$cidgroupid		= $this -> ratecard_obj[$k][44];

			if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0) 
				$destination= substr($destination, strlen($removeprefix));
				
			if ($typecall==1) $timeout = $A2B -> config["callback"]['predictivedialer_maxtime_tocall']; 
				
			$dialparams = str_replace("%timeout%", min($timeout * 1000, $max_long), $A2B->agiconfig['dialcommand_param']);
			//$dialparams = "|30|HS($timeout)"; // L(".$timeout*1000.":61000:30000)
				
			if (strlen($musiconhold)>0 && $musiconhold!="selected"){
				$dialparams.= "m";
				$myres = $agi->exec("SETMUSICONHOLD $musiconhold");
				$A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "EXEC SETMUSICONHOLD $musiconhold");
			}
				
			if ($A2B -> agiconfig['record_call'] == 1){
				$myres = $agi->exec("MixMonitor {$A2B->uniqueid}.{$A2B->agiconfig['monitor_formatfile']}|b");
				$A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "EXEC MixMonitor {$A2B->uniqueid}.{$A2B->agiconfig['monitor_formatfile']}|b");
			}

				
			$pos_dialingnumber = strpos($ipaddress, '%dialingnumber%' );
				
			$ipaddress = str_replace("%cardnumber%", $A2B->cardnumber, $ipaddress);
			$ipaddress = str_replace("%dialingnumber%", $prefix.$destination, $ipaddress);
				
				
			if ($pos_dialingnumber !== false){					   
				   $dialstr = "$tech/$ipaddress".$dialparams;
			}else{
				if ($A2B->agiconfig['switchdialcommand'] == 1){
					$dialstr = "$tech/$prefix$destination@$ipaddress".$dialparams;
				}else{
					$dialstr = "$tech/$ipaddress/$prefix$destination".$dialparams;
				}
			}
			
			
			//ADDITIONAL PARAMETER 			%dialingnumber%,	%cardnumber%	
			if (strlen($addparameter)>0){
				$addparameter = str_replace("%cardnumber%", $A2B->cardnumber, $addparameter);
				$addparameter = str_replace("%dialingnumber%", $prefix.$destination, $addparameter);
				$dialstr .= $addparameter;
			}
			
			$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "app_callingcard: Dialing '$dialstr' with timeout of '$timeout'.\n");
			
			//# Channel: technology/number@ip_of_gw_to PSTN
			//# Channel: SIP/3465078XXXXX@11.150.54.xxx   /     SIP/phone1@192.168.1.6
			// exten => 1879,1,Dial(SIP/34650XXXXX@255.XX.7.XX,20,tr)
			// Dial(IAX2/guest@misery.digium.com/s@default) 
			//$myres = $agi->agi_exec("EXEC DIAL SIP/3465078XXXXX@254.20.7.28|30|HL(" . ($timeout * 60 * 1000) . ":60000:30000)");

			if ($A2B->config["database"]['dbtype'] == "postgres"){
				$QUERY = "SELECT cid FROM cc_outbound_cid_list WHERE activated = 1 AND outbound_cid_group = $cidgroupid ORDER BY RANDOM() LIMIT 1";
			}
			else
			{
				$QUERY = "SELECT cid FROM cc_outbound_cid_list WHERE activated = 1 AND outbound_cid_group = $cidgroupid ORDER BY RAND() LIMIT 1";	
			}
			
			$A2B->instance_table = new Table();
			$cidresult = $A2B->instance_table -> SQLExec ($A2B -> DBHandle, $QUERY);
			$outcid = 0;
			if (is_array($cidresult) && count($cidresult)>0){
				$outcid = $cidresult[0][0];
				$A2B -> CallerID = $outcid;
				$agi -> set_callerid($outcid);
				$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[EXEC SetCallerID : $outcid]");
			}
			$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "app_callingcard: CIDGROUPID='$cidgroupid' OUTBOUND CID SELECTED IS '$outcid'.");
			
			$myres = $agi->exec("Dial $dialstr");	
    		//exec('Dial', trim("$type/$identifier|$timeout|$options|$url", '|'));
			
			$A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "DIAL $dialstr");
				
			if ($A2B -> agiconfig['record_call'] == 1){
				// Monitor(wav,kiki,m)					
				$myres = $agi->exec("StopMixMonitor");
				$A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "EXEC StopMixMonitor (".$A2B->uniqueid."-".$A2B->cardnumber.")");
			}
			
			$answeredtime = $agi->get_variable("ANSWEREDTIME");
			$this->answeredtime = $answeredtime['data'];
			$dialstatus = $agi->get_variable("DIALSTATUS");
			$this->dialstatus = $dialstatus['data'];
			
			//$this->answeredtime='60';
			//$this->dialstatus='ANSWERED';
			
			// LOOOOP FOR THE FAILOVER LIMITED TO failover_recursive_limit
			$loop_failover = 0;
			while ( $loop_failover <= $A2B->agiconfig['failover_recursive_limit'] && is_numeric($failover_trunk) && $failover_trunk>=0 && (($this->dialstatus == "CHANUNAVAIL") || ($this->dialstatus == "CONGESTION")) ){
				$loop_failover++;
				$this->answeredtime=0;
				$this -> usedtrunk = $failover_trunk;
				
				$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[K=$k]:[ANSWEREDTIME=".$this->answeredtime."-DIALSTATUS=".$this->dialstatus."]");			
				
				
				$destination=$old_destination;
				
				$QUERY = "SELECT trunkprefix, providertech, providerip, removeprefix, failover_trunk FROM cc_trunk WHERE id_trunk='$failover_trunk'";
				$A2B->instance_table = new Table();
				$result = $A2B->instance_table -> SQLExec ($A2B -> DBHandle, $QUERY);
				
				
				if (is_array($result) && count($result)>0){
					
					//DO SELECT WITH THE FAILOVER_TRUNKID
					$prefix		= $result[0][0];
					$tech 		= $result[0][1];
					$ipaddress 	= $result[0][2];
					$removeprefix 	= $result[0][3];
					$next_failover_trunk = $result[0][4];
					
					$pos_dialingnumber = strpos($ipaddress, '%dialingnumber%' );
					
					$ipaddress = str_replace("%cardnumber%", $A2B->cardnumber, $ipaddress);
					$ipaddress = str_replace("%dialingnumber%", $prefix.$destination, $ipaddress);
						
					if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0){
						$destination= substr($destination, strlen($removeprefix));
					}
					
					$dialparams = str_replace("%timeout%", min($timeout * 1000, $max_long), $A2B->agiconfig['dialcommand_param']);
					
					if ($pos_dialingnumber !== false){					   
						$dialstr = "$tech/$ipaddress".$dialparams;
					}else{
						if ($A2B->agiconfig['switchdialcommand'] == 1){
							$dialstr = "$tech/$prefix$destination@$ipaddress".$dialparams;
						}else{
							$dialstr = "$tech/$ipaddress/$prefix$destination".$dialparams;
						}
					}	
					
					$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "FAILOVER app_callingcard: Dialing '$dialstr' with timeout of '$timeout'.\n");
					
					$myres = $agi->exec("DIAL $dialstr");
					$A2B -> debug( WRITELOG, $agi, __FILE__, __LINE__, "DIAL FAILOVER $dialstr");
					
					$answeredtime = $agi->get_variable("ANSWEREDTIME");
					$this->answeredtime = $answeredtime['data'];
					$dialstatus = $agi->get_variable("DIALSTATUS");
					$this->dialstatus = $dialstatus['data'];
					
					$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[FAILOVER K=$k]:[ANSTIME=".$this->answeredtime."-DIALSTATUS=".$this->dialstatus."]");
					
				}
				// IF THE FAILOVER TRUNK IS SAME AS THE ACTUAL TRUNK WE BREAK 
				if ($next_failover_trunk == $failover_trunk) break;
				else $failover_trunk = $next_failover_trunk;
				
			} // END FOR LOOP FAILOVER 

			//# Ooh, something actually happened!
			if ($this->dialstatus  == "BUSY") {
				$this->answeredtime=0;
				//$agi->agi_exec("STREAM FILE prepaid-isbusy #");
				$agi-> stream_file('prepaid-isbusy', '#');
			} elseif ($this->dialstatus == "NOANSWER") {
				$this->answeredtime=0;
				//$agi->agi_exec("STREAM FILE prepaid-noanswer #");
				$agi-> stream_file('prepaid-noanswer', '#');
			} elseif ($this->dialstatus == "CANCEL") {
				$this->answeredtime=0;
			} elseif (($this->dialstatus  == "CHANUNAVAIL") || ($this->dialstatus  == "CONGESTION")) {
				$this->answeredtime=0;
				continue;
			} elseif ($this->dialstatus == "ANSWER") {
				$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "-> dialstatus : ".$this->dialstatus.", answered time is ".$this->answeredtime." \n");
			}

			$this->usedratecard = $k;
			$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[USEDRATECARD=".$this->usedratecard."]");
			return true;
		} // End for
		
		$this->usedratecard=$k-1;
		$A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[USEDRATECARD - FAIL =".$this->usedratecard."]");
		return false;

	}
	

};

?>
