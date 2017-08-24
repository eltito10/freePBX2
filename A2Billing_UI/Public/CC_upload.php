<?php
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/smarty.php");


if (! has_rights (ACX_FILE_MANAGER)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}

getpost_ifset(array('acc'));

  //Show the number of files to upload
  $files_to_upload = 1;

  //Directory where the uploaded files have to come
  //RECOMMENDED TO SET ANOTHER DIRECTORY THEN THE DIRECTORY WHERE THIS SCRIPT IS IN!!
  # the upload store directory (chmod 777)
  $upload_dir = DIR_STORE_AUDIO; //"/var/www/html/all/divers/simpleupload/upload";
  



  # Handle the MusicOnHold
	if (isset($acc) && ($acc>0)){
		$file_ext_allow = $file_ext_allow_musiconhold;
		$pass_param = "acc=$acc";
		
		$upload_dir = DIR_STORE_MOHMP3."/acc_$acc";
	}
	
  # individual file size limit - in bytes (102400 bytes = 100KB)
  $file_size_ind = MY_MAX_FILE_SIZE_AUDIO;

  # PHP.INI
  # ; Maximum allowed size for uploaded files.
  # upload_max_filesize = 8M


  # the images directory
  $dir_img= "img";
  
 
  
  // -------------------------------- //
  //     SCRIPT UNDER THIS LINE!      //
  // -------------------------------- //
  
function getlast($toget)
{
	$pos=strrpos($toget,".");
	$lastext=substr($toget,$pos+1);
	echo "lastext=	$lastext<br>";
	return $lastext;
}

function arr_rid_blank($my_arr)
{
	if (is_array($my_arr)){
		for($i=0;$i<count($my_arr);$i++)
		{
			$my_arr[$i] = trim($my_arr[$i]);
		}
		return $my_arr;
	}
}

$file_ext_allow = arr_rid_blank($file_ext_allow);

  // print_r ($_FILES);  

  //When REGISTERED_GLOBALS are off in php.ini
  /*
  $_POST    = $HTTP_POST_VARS;
  $_GET     = $HTTP_GET_VARS;
  $_SESSION = $HTTP_SESSION_VARS;
  */
  
  //Any other action the user must be logged in!
  
  if($_GET['method'])
  {
    session_register('message');

    //Upload the file
    if($_GET['method'] == "upload")
    {

      $file_array = $_FILES['file'];
      $_SESSION['message'] = "";
      $uploads = false;
	  
	  //print_r($file_ext_allow);
	  //echo "<br>files_to_upload=$files_to_upload</br>";
			
      for($i = 0 ; $i < $files_to_upload; $i++)
      { 
        if($_FILES['file']['name'][$i])
        {
          $uploads = true;
          if($_FILES['file']['name'][$i])
          {
		  
		    $fileupload_name=$_FILES['file']['name'][0];
		  	
		  	for($i=0;$i<count($file_ext_allow);$i++)
			{
				//if (getlast($fileupload_name)!=$file_ext_allow[$i]){
				if (strcmp(getlast($fileupload_name),$file_ext_allow[$i])!=0){
					$test.="~~";
					echo "<br>'".getlast($fileupload_name)."' - '".$file_ext_allow[$i]."'<br>";
				}
			}
			$exp=explode("~~",$test);
			if (count($exp)==(count($file_ext_allow)+1))
			{
				$_SESSION['message'] .= "<br><img src=\"$dir_img/error.gif\" width=\"15\" height=\"15\">&nbsp;<b><font size=\"2\">".gettext("ERROR: your file type is not allowed")." (".getlast($fileupload_name).")</font>, ".gettext("or you didn't specify a file to upload").".</b><br>";				
			}
			else
			{		  		
				if ($_FILES['file']['size'][0] > $file_size_ind)
				{
					$_SESSION['message'] .= "<br><img src=\"$dir_img/error.gif\" width=\"15\" height=\"15\">&nbsp;<b><font size=\"2\">".gettext("ERROR: please get the file size less than")." ".$file_size_ind." BYTES  (".round(($file_size_ind/1024),2)." KB)</font></b><br>";
				}else{
					$file_to_upload = $upload_dir."/".$_FILES['file']['name'][0];					
					move_uploaded_file($_FILES['file']['tmp_name'][0],$file_to_upload);
					//echo "<br>::$file_to_upload</br>";
					//chmod($file_to_upload,0777);
					$_SESSION['message'] .= $_FILES['file']['name'][0]." uploaded.<br>";
				}
			}
          } 
        }
      }
      if(!$uploads)  $_SESSION['message'] = gettext("No files selected!");
    }

    //Logout 
    elseif($_GET['method'] == "logout")
    {
      session_destroy();
    }

    //Delete the file
    elseif($_GET['method'] == "delete" && $_GET['file'])
    {
      if(!@unlink($upload_dir."/".$_GET['file']))
        $_SESSION['message'] = "File not found!";
      else
        $_SESSION['message'] = $_GET['file'] . " deleted";
    }

    //Download a file 
    elseif($_GET['method'] == "download" && $_GET['file'])
    {
      $file = $upload_dir . "/" . $_GET['file'];
      $filename = basename( $file );
      $len = filesize( $file );
      header( "content-type: application/stream" );
      header( "content-length: " . $len );
      header( "content-disposition: attachment; filename=" . $filename );
      $fp=fopen( $file, "r" );
      fpassthru( $fp );
      exit;
    }
    
    //Rename a file
    elseif( $_GET['method'] == "rename" )
    {
      rename( $upload_dir . "/" . $_GET['file'] , $upload_dir . "/" . $_GET['to'] );
      $_SESSION['message'] = "Renamed " . $_GET['file'] . " to " . $_GET['to'];
    }
    // Redirect to the script again
    Header("Location: " . $_SERVER['PHP_SELF']."?acc=$acc" );
  }


	$smarty->display('main.tpl');
?>
 


<center>
<table width="560" cellspacing="0" cellpadding="0" border="0">
  <tr>
    <td><font size="3"><b><i><?php echo gettext("File Upload");?></i></b></font>&nbsp;<font style="text-decoration: bold; font-size: 9px;">  (upload_dir: <?php echo $upload_dir?>)</font>&nbsp;
	<br><br>
    </td>
   </tr>
</table>



<table width="560" cellspacing="0" cellpadding="0" border="0" class="table_decoration" style="padding-top:5px;padding-left=5px;padding-bottom:5px;padding-right:5px">
  <form method='post' enctype='multipart/form-data' action='<?php echo $_SERVER['PHP_SELF'];?>?method=upload'>
  
  
  <input type="hidden" value="<?php echo $acc?>" name="acc"/>
 <?php
        for( $i = 0; $i < $files_to_upload; $i++ )
        {
    ?>
                     <tr>
                       <td>file:</td><td><input type='file' name='file[]' class="textfield" size="30"></td>
                     </tr>
    <?php
        }
    ?>
	<tr>
    <td><?php echo gettext("file types allowed");?>:</td><td>

	<?php
	for($i=0;$i<count($file_ext_allow);$i++)
	{
		if (($i<>count($file_ext_allow)-1))$commas=", ";else $commas="";
		list($key,$value)=each($file_ext_allow);
		echo $value.$commas;
	}
	?>   </td>
  </tr>

  <tr>
    <td><?php echo gettext("file size limit");?>:</td>
	<td>
		<b><?php 
			if ($file_size_ind >= 1048576) 
			{
				$file_size_ind_rnd = round(($file_size_ind/1024000),3) . " MB";
			} 
			elseif ($file_size_ind >= 1024) 
			{	
				$file_size_ind_rnd = round(($file_size_ind/1024),2) . " KB";
			} 
			elseif ($file_size_ind >= 0) 
			{
				$file_size_ind_rnd = $file_size_ind . " bytes";
			} 
			else 
			{
				$file_size_ind_rnd = "0 bytes";
			}
			
			echo "$file_size_ind_rnd";
		?></b>
	</td>

  </tr>
  <tr>
    <td colspan="2"><input type="submit" value="<?php echo gettext("Upload");?>" class="button">&nbsp;<input type="reset" value="<?php echo gettext("Clear");?>" class="button"></td>
  </tr>
  </form>
</table>

      <?php
	  
        //When there is a message, after an action, show it
        if(session_is_registered('message'))
        {
          echo "<br></br><font color='red'>" . $_SESSION['message'] . "</font>";
        }
      ?>
<br><br><table width="560" cellspacing="0" cellpadding="0" border="0" class="table_decoration" style="padding-left:6px">
  <tr  class="bgcolor_014">
    <td align="left" width="46%"><?php echo gettext("FILE NAME");?></td>

    <td align="center" width="12%"><?php echo gettext("FILE TYPE");?></td>
    <td align="center" width="12%"><?php echo gettext("FILE SIZE");?></td>
    <td align="center" width="30%"><?php echo gettext("FUNCTIONS");?></td>
  </tr>
  
  <?php
        //Handle for the directory
        if (!$handle = @opendir($upload_dir)){
          echo "<span style=\"font-size: 11px;\"><strong style=\"color: red;\">".gettext("Error")."!!</strong> ".gettext("Cannot open directory").": <strong>" . $upload_dir . "</strong>. ".gettext("Check if this directory exists and/or the CHMod rights are properly set")."...</span>";
        }

        //Walk the directory for the files
        while($entry = @readdir($handle))
        {
          if($entry != ".." && $entry != "." && !is_dir($entry))
          {

            //Set the filesize type (bytes, KiloBytes of MegaBytes)
            $filesize = filesize($upload_dir . "/" . $entry);
            $type = Array ('b', 'KB', 'MB');
            for ($i = 0; $filesize > 1024; $i++)
              $filesize /= 1024;
            $filesize = round ($filesize, 2)." $type[$i]";
    ?>
    					  
		<tr>
			<td width="30%"><A href='<?php echo $_SERVER['PHP_SELF'];?>?method=download&amp;file=<?php echo $entry;?>&<?php echo $pass_param?>'><?php echo $entry;?></a></td>	
			<td align="center" width="5%"><?php  echo strtoupper(substr($entry,-3));?></td>	
			<td align="center" width="5%"><?php echo $filesize;?></td>
			<td align="center" width="5%">
				<A href="javascript:if(confirm('<?php echo gettext("Are you sure to delete ");?> <?php echo $entry;?>?')) location.href='<?php echo $_SERVER['PHP_SELF'];?>?method=delete&amp;file=<?php echo $entry;?>&<?php echo $pass_param?>';"><img src='<?php echo $dir_img?>/cross.gif' alt='Delete <?php echo $entry;?>' border=0></a>
				<A href='<?php echo $_SERVER['PHP_SELF'];?>?method=download&amp;file=<?php echo $entry;?>&<?php echo $pass_param?>'><img src='<?php echo $dir_img?>/dl.gif' alt='Download <?php echo $entry;?>' border=0></a>
				<A href="javascript: var inserttext = ''; if(inserttext = prompt('Rename <?php echo $entry;?>. Fill in the new name for the file.','<?php echo $entry;?>')) location.href='<?php echo $_SERVER['PHP_SELF'];?>?method=rename&<?php echo $pass_param?>&amp;file=<?php echo $entry;?>&amp;to='+inserttext; "><img src='<?php echo $dir_img?>/edit.gif' alt='Rename <?php echo $entry;?>' border=0></a>
			</td>
		</tr>
    <?php
          }
       }
    ?>

</table></center>
<br><br>
<?php
        $smarty->display('footer.tpl');
?>
