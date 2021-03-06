<?php

getpost_ifset(array('id', 'trunkcode', 'trunkprefix', 'removeprefix', 'providertech', 'providerip', 'addparameter','failover_trunk', 'id_provider', 'popup_select'));


$HD_Form = new FormHandler("cc_trunk","trunk");

$HD_Form -> FG_DEBUG = 0;
$HD_Form -> FG_TABLE_ID = " id_trunk";
$HD_Form -> FG_TABLE_DEFAULT_ORDER = " id_trunk";
$HD_Form -> FG_EDITION_CLAUSE = " id_trunk='%id' ";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";

// Dependent Tables
$HD_Form -> FG_FK_DELETE_ALLOWED = true;
$HD_Form -> FG_FK_DELETE_CONFIRM = true;
$HD_Form -> FG_FK_WARNONLY = true;
$HD_Form -> FG_FK_TABLENAMES = array("cc_ratecard");
$HD_Form -> FG_FK_EDITION_CLAUSE = array(" id_trunk ");

$HD_Form -> FG_FK_DELETE_MESSAGE = gettext("You have some rates attached to this Trunk! Please comfirm that you really want to remove this Trunk? ");


if ($popup_select)
{
	$HD_Form -> AddViewElement(gettext("ID"), "id_trunk", "7%", "center", "sort");
	$HD_Form -> AddViewElement(gettext("LABEL"), "trunkcode", "15%", "center", "sort");
	$HD_Form -> AddViewElement(gettext("PROVIDER"), "id_provider" ,"10%", "center", "sort", "15", "lie", "cc_provider", "provider_name", "id='%id'", "%1");

	$HD_Form -> FieldViewElement ('id_trunk, trunkcode, id_provider');
}
else
{
	$HD_Form -> AddViewElement(gettext("LABEL"), "trunkcode", "15%", "center", "sort");
	$HD_Form -> AddViewElement(gettext("ADD_PREFIX"), "trunkprefix", "7%", "center", "sort");
	$HD_Form -> AddViewElement(gettext("REMOVE_PREFIX"), "removeprefix", "7%", "center", "sort");
	$HD_Form -> AddViewElement(gettext("TECH"), "providertech", "7%", "center", "sort");
	$HD_Form -> AddViewElement(gettext("IP/HOST"), "providerip" , "15%", "center", "sort");
	$HD_Form -> AddViewElement(gettext("PROVIDER"), "id_provider" ,"10%", "center", "sort", "15", "lie", "cc_provider", "provider_name", "id='%id'", "%1");
	$HD_Form -> AddViewElement(gettext("MINUTES USED"), "secondusedreal", "10%", "center", "SORT", "30", "", "", "", "", "", "display_minute");

	$HD_Form -> FieldViewElement ('trunkcode, trunkprefix, removeprefix, providertech, providerip, id_provider, secondusedreal');
}

$HD_Form -> FG_ACTION_SIZE_COLUMN = '20%';
$HD_Form -> CV_NO_FIELDS  = gettext("THERE IS NO ".strtoupper($HD_Form->FG_INSTANCE_NAME)." CREATED!");
$HD_Form -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = false;
$HD_Form -> CV_TEXT_TITLE_ABOVE_TABLE = '';
$HD_Form -> CV_DISPLAY_FILTER_ABOVE_TABLE = false;


if ($popup_select) {
	$HD_Form -> FG_LIMITE_DISPLAY = 7;
	$HD_Form -> CV_FOLLOWPARAMETERS = "&popup_select=" . $_GET['popup_select'] . "&popup_formname=" . $_GET['popup_formname'] . "&popup_fieldname=" . $_GET['popup_fieldname'];

	$HD_Form -> FG_OTHER_BUTTON1 = true;
	$HD_Form -> FG_OTHER_BUTTON1_ALT = '<font color="red">&lt;select&gt;</font>';
	$HD_Form -> FG_OTHER_BUTTON1_IMG = '';

	//echo $popup_select;
	if ($popup_select == 1)
		$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|param|');";
	elseif ($popup_select == 2)
		$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|col0|');";
}
else {
	$HD_Form -> FG_EDITION = true;
	$HD_Form -> FG_DELETION = true;
}


$HD_Form -> AddEditElement (gettext("VOIP-PROVIDER"),
	"id_provider",
	'$value',
	"SELECT",
	"", "", "",
	"sql",
	"cc_provider",
	"provider_name, id",
	"", "", "%1","", "", '', '<OPTION  value="-1" selected>NOT DEFINED</OPTION>');
	
$HD_Form -> AddEditElement (gettext("LABEL"),
	"trunkcode",
	"$value",
	"INPUT",
	"size=45 maxlength=40",
	"3",
	gettext("Insert the trunkcode"),
	"" , "", "", "", "" , "" , "", gettext("Unique and friendly name for the trunk"));

$HD_Form -> AddEditElement (gettext("ADD PREFIX"),
	"trunkprefix",
	"$value",
	"INPUT",
	"size=30 maxlength=20",
	"",
	gettext("Insert the trunkprefix"),
	"" , "", "", "", "" , "" , "", gettext("Add a prefix to the dialled digits."));
	
$HD_Form -> AddEditElement (gettext("REMOVE PREFIX"),
	"removeprefix",
	"$value",
	"INPUT",
	"size=30 maxlength=20",
	"",
	gettext("Insert the removeprefix"),
	"" , "", "", "", "" , "" , "", gettext("in case of the voip provider or the gateway doesnt want a dialed prefix (can be useful with local gateway)"));
	
$HD_Form -> AddEditElement (gettext("PROVIDER TECH"),
	"providertech",
	"$value",
	"INPUT",
	"size=20 maxlength=15",
	"0",
	gettext("Insert the providertech"),
	"" , "", "", "", "" , "" , "", gettext("Technology used on the trunk (SIP,IAX2,ZAP,H323)"));
	
$HD_Form -> AddEditElement (gettext("PROVIDER IP"),
	"providerip",
	"$value",
	"INPUT",
	"size=80 maxlength=140",
	"9",
	gettext("Insert the providerip"),
	"" , "", "", "", "" , "" , "", gettext("Set the IP or URL of the VoIP provider. Alternatively, put in the name of a previously defined trunk in Asterisk. (MyVoiPTrunk, ZAP4G etc.) You can use the following tags to as variables: %dialingnumber%, %cardnumber%. ie g2/1644787890wwwwwwwwww%dialingnumber%"));

$HD_Form -> AddEditElement (gettext("ADDITIONAL PARAMETER"),
	"addparameter",
	"$value",
	"INPUT",
	"size=60 maxlength=100",
	"",
	gettext("Insert Additional parameters"),
	"" , "", "", "", "" , "" , "", gettext("Define any additional parameters that will be used when running the Dial Command in Asterisk. Use the following tags as variables  %dialingnumber%, %cardnumber%. ie 'D(ww%cardnumber%wwwwwwwwww%dialingnumber%)'"));
	
$HD_Form -> AddEditElement (gettext("FAILOVER TRUNK"),
	"failover_trunk",
	'$value',
	"SELECT",
	"", "", "",
	"sql",
	"cc_trunk",
	"trunkcode, id_trunk",
	"", "", "%1", "", gettext("You can define an other trunk in case of failover!"), '', '<OPTION  value="-1" selected>NOT DEFINED</OPTION>');


$HD_Form -> FieldEditElement ('id_provider, trunkcode, trunkprefix, removeprefix, providertech, providerip, addparameter, failover_trunk');


if (DB_TYPE == "postgres"){
	$HD_Form -> FG_QUERY_ADITION_HIDDEN_FIELDS = "";
	$HD_Form -> FG_QUERY_ADITION_HIDDEN_VALUE  = "";	
}else{
	$HD_Form -> FG_QUERY_ADITION_HIDDEN_FIELDS = "creationdate ";
	$HD_Form -> FG_QUERY_ADITION_HIDDEN_VALUE  = "now()";	
}




$HD_Form -> FG_INTRO_TEXT_EDITION= gettext("Modify the properties of the trunk below.");
$HD_Form -> FG_INTRO_TEXT_ASK_DELETION = gettext("If you really want remove this")." ".$HD_Form->FG_INSTANCE_NAME.", ".gettext("click on the delete button.");
$HD_Form -> FG_INTRO_TEXT_ADD = gettext("you can add easily a new")." ".$HD_Form->FG_INSTANCE_NAME.".<br>".gettext("Fill the following fields and confirm by clicking on the button add.");





$HD_Form -> FG_INTRO_TEXT_ADITION = '';
$HD_Form -> FG_TEXT_ADITION_CONFIRMATION = gettext("Your new")." ".$HD_Form->FG_INSTANCE_NAME." ".gettext("has been inserted.")."<br>";


$HD_Form -> FG_BUTTON_EDITION_SRC = $HD_Form -> FG_BUTTON_ADITION_SRC  = Images_Path . "/cormfirmboton.gif";
$HD_Form -> FG_BUTTON_EDITION_BOTTOM_TEXT = $HD_Form -> FG_BUTTON_ADITION_BOTTOM_TEXT = 'Fill correctly the fields to add a new '.$HD_Form->FG_INSTANCE_NAME;



$HD_Form -> FG_GO_LINK_AFTER_ACTION_ADD = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_GO_LINK_AFTER_ACTION_EDIT = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_GO_LINK_AFTER_ACTION_DELETE = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
?>
