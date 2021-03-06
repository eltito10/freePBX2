<?php

getpost_ifset(array('id', 'lastname', 'firstname', 'email', 'address','city', 'state', 'country', 'zipcode', 'phone', 'fax','message'));


$HD_Form = new FormHandler("cc_card","Card");

$HD_Form -> FG_DEBUG = 0;
$HD_Form -> FG_TABLE_ID="id";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";
$HD_Form -> FG_HTML_TABLE_WIDTH = "60%";
$HD_Form -> FG_EDITION_CLAUSE = " id = ".$_SESSION["card_id"];
$HD_Form -> FG_INTRO_TEXT_EDITION = '';
$HD_Form -> AddEditElement (gettext("LASTNAME"),
			   "lastname",
			   '$value',
			   "INPUT",
			   "size=30 maxlength=50",
			   "11",
			   gettext("Insert the lastname of the customers"),
			   "" , "", "",	"", "", "" , "NO", "");

$HD_Form -> AddEditElement (gettext("FIRSTNAME"),
			   "firstname",
			   '$value',
			   "INPUT",
			   "size=30 maxlength=50",
			   "11",
			   gettext("Insert the firstname of the customers"),
			   "" , "", "",	"", "", "" , "NO", "");


$HD_Form -> AddEditElement (gettext("ADDRESS"),
			   "address",
			   '$value',
			   "INPUT",
			   "size=30 maxlength=100",
			   "0",
			   gettext("Insert the address of the customers"),
			   "" , "", "",	"", "", "" , "NO", "");


$HD_Form -> AddEditElement (gettext("CITY"),
			   "city",
			   '$value',
			   "INPUT",
			   "size=30 maxlength=40",
			   "0",
			   gettext("Insert the city"),
			   "" , "", "",	"", "", "" , "NO", "");


$HD_Form -> AddEditElement (gettext("STATE/PROVINCE"),
			   "state",
			   '$value',	
			   "INPUT",	
			   "size=30 maxlength=40",	
			   "11",	
			   gettext("Insert the state"),
			   "" , "", "",	"", "", "" , "NO", "");


$HD_Form -> AddEditElement (gettext("COUNTRY"),
			   "country",
			   "",
			   "SELECT",
			   "", "", "",
			   "sql",
			   "cc_country",
			   "countryname, countrycode",
			   "", "", "%1", "", "", "", '' );


$HD_Form -> AddEditElement (gettext("ZIP/POSTAL CODE"),
			   "zipcode",			
			   '$value',
			   "INPUT",	
			   "size=30 maxlength=20",	
			   "0",
			   gettext("Insert the zipcode"),
			   "" , "", "",	"", "", "" , "NO", "");


$HD_Form -> AddEditElement (gettext("PHONE NUMBER"),
			   "phone",			
			   '$value',	
			   "INPUT",	
			   "size=30 maxlength=20",	
			   "7",	
			   gettext("Insert the phone number of the customers"),
			   "" , "", "",	"", "", "" , "NO", "");


$HD_Form -> AddEditElement (gettext("FAX NUMBER"),
			   "fax",			
			   '$value',	
			   "INPUT",	
			   "size=30 maxlength=20",
			   "",	
			   gettext("Insert the fax number of the customers"),
			   "" , "", "",	"", "", "" , "NO", "");

$HD_Form -> FieldEditElement ('lastname, firstname, address, city, state, country, zipcode, phone,'.
'fax');

$HD_Form -> FG_INTRO_TEXT_ADD = gettext("you can add easily update your information clicking on the button.");

$HD_Form -> FG_INTRO_TEXT_ADITION = '';
$HD_Form -> FG_TEXT_ADITION_CONFIRMATION = gettext("Your record has been updated.")."<br>";

$HD_Form -> FG_GO_LINK_AFTER_ACTION_EDIT = $_SERVER['PHP_SELF']."?message=success";

?>
