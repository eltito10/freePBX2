<?php
getpost_ifset(array('id', 'destination','dialprefix','popup_select','popup_formname','popup_fieldname' ));

$HD_Form = new FormHandler("cc_prefix","prefix");

$HD_Form -> FG_DEBUG = 0;
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "destination";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";


$HD_Form -> AddViewElement(gettext("Destination"), "destination", "40%", "left", "sort");
$HD_Form -> AddViewElement(gettext("Prefix"), "prefixe", "10%", "center", "sort");

// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form -> FieldViewElement ('destination, prefixe');


$HD_Form -> CV_NO_FIELDS  = gettext("THERE ARE NO")." ".strtoupper($HD_Form->FG_INSTANCE_NAME)." ".gettext("CREATED!");
$HD_Form -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = false;
$HD_Form -> CV_TEXT_TITLE_ABOVE_TABLE = '';
$HD_Form -> CV_DISPLAY_FILTER_ABOVE_TABLE = false;
if (!($popup_select==1)){
	$HD_Form -> FG_EDITION = true;
	$HD_Form -> FG_DELETION = true;
}else{
	$HD_Form -> CV_FOLLOWPARAMETERS= "&popup_select=".$_GET['popup_select']."&popup_formname=".$_GET['popup_formname']."&popup_fieldname=".$_GET['popup_fieldname'];
	$HD_Form ->FG_LIMITE_DISPLAY=7;
	$HD_Form -> FG_OTHER_BUTTON1 = true;
	$HD_Form -> FG_OTHER_BUTTON1_ALT = '<font color="red">&lt;select&gt;</font>';
	$HD_Form -> FG_OTHER_BUTTON1_IMG = '';
	
	//if ($popup_select==1){
	//	$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|param|');";
	//}elseif ($popup_select==2){					
		$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|col1|');";
	//}	
	
}


$HD_Form -> AddEditElement (gettext("DESTINATION"),
	"destination",
	'$value',
	"INPUT",
	"size=30 maxlength=300",
	"$destination",
	gettext("Insert the Destination"),
	"" , "", "", "", "" , "", "" ,"");

$HD_Form -> AddEditElement (gettext("PREFIX"),
	"prefixe",
	'$value',
	"INPUT",
	"size=30 maxlength=300",
	"$dialprefix",
	gettext("Insert the Prefixe"),
	"" , "", "", "", "" , "", "" ,"");

$HD_Form -> FieldEditElement ('destination, prefixe');



$HD_Form -> FG_FILTER_APPLY = true;
$HD_Form -> FG_FILTERFIELD = 'destination';
$HD_Form -> FG_FILTERFIELDNAME = 'destination';
$HD_Form -> FG_FILTER_FORM_ACTION = 'list';

if (isset($filterprefix)  &&  (strlen($filterprefix)>0)){
	if (strlen($HD_Form -> FG_TABLE_CLAUSE)>0) $HD_Form -> FG_TABLE_CLAUSE.=" AND ";
	$filterprefix = strtoupper(substr($filterprefix,0,1)).substr($filterprefix,1);
	$HD_Form -> FG_TABLE_CLAUSE.="destination like '$filterprefix%'";
}



$HD_Form -> FG_INTRO_TEXT_EDITION= '';
$HD_Form -> FG_INTRO_TEXT_ASK_DELETION = gettext("If you really want remove this")." ".$HD_Form->FG_INSTANCE_NAME.", ".gettext("click on the delete button.");
$HD_Form -> FG_INTRO_TEXT_ADD = gettext("you can add easily a new")." ".$HD_Form->FG_INSTANCE_NAME.".<br>".gettext("Fill the following fields and confirm by clicking on the button add.");


$HD_Form -> FG_INTRO_TEXT_ADITION = '';
$HD_Form -> FG_TEXT_ADITION_CONFIRMATION = gettext("Your new")." ".$HD_Form->FG_INSTANCE_NAME." ".gettext("has been inserted.")."<br>";


$HD_Form -> FG_BUTTON_EDITION_SRC = $HD_Form -> FG_BUTTON_ADITION_SRC  = Images_Path . "/cormfirmboton.gif";
$HD_Form -> FG_BUTTON_EDITION_BOTTOM_TEXT = $HD_Form -> FG_BUTTON_ADITION_BOTTOM_TEXT = gettext("Click 'Confirm Data' to continue");


$HD_Form -> FG_GO_LINK_AFTER_ACTION_ADD = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_GO_LINK_AFTER_ACTION_EDIT = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_GO_LINK_AFTER_ACTION_DELETE = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";

?>
