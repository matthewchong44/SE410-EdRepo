<?php session_start();
/****************************************************************************************************************************
 *    configureFooter.php - Allows editing of footer display settings.
 *    ---------------------------------------------------------------------------------------------------------
 *  Allows administrators to easily edit pages whcih contain static content.
 *
 *  Version: 1.0
 *  Author: Ethan Greer
 *  Modified by: Jon Thompson (5/10/2011 - implemented new interface/Smarty)
 *
 *  Notes: - Only Admins may use this page.
 *         - This page uses the following GET/POST parameters:
 *            action : One of "displayEdit" (default) which will display the specified static content for editing.
 *                            "doEdit" will attempt to save the edited progress.
 *            page : The page of static HTML to edit.  This parameter should be the name of a file in the lib/staticContent
 *              directory and must be recognized by this page (see the inilitilzing code).
 ******************************************************************************************************************************/
  
  require("../lib/config.php");

  $smarty->assign("title", $COLLECTION_NAME . " - Admin - Configure Footer");
    // title of this page. For most pages: &COLLECTION . " - Title" , default: $COLLECTION_NAME
  $smarty->assign("tab", "admin"); // active nav tab. default:  "home"
  $smarty->assign("baseDir", getBaseDir() ); // should always be getBaseDir() 
  
  $smarty->assign("pageName", "Admin - Configure Footer");
  
  $smarty->assign("alert", array("type"=>"", "message"=>"") );
  
  
  $action="displayEdit"; //Default action is to display an editing panel.
  if(isset($_REQUEST["action"])) {
    $action=$_REQUEST["action"];
  }
  $smarty->assign("action", $action);
  
  /* To prevent against people abusing this page to edit (or possibly create) any page in the lib/staticContent subdirectory, we'll check to make 
    sure the page given in the $_REQUEST["page"] parameter is a valid page we can edit.  This means you MUST add all pages which can be edited here!
    If the page given isn't found here, or nothing ws given, $pagename will be left as FALSE, indicating no valid page was found.  Otheriwse, 
    $pagename will be set to a friendly, human-readable name for the page. */
  $pagename="Footer"; //By default a valid page wasn't given.
  $file="footer.html";


if (isset($userInformation) && $userInformation["type"]=="Admin") { //This else block is if we're logged in as an admin.

  if($action=="displayEdit") {
      // clean up HTML tags for proper display in textarea/database storage
      $smarty->assign("cleanFooterContent", htmlspecialchars($FOOTER["CONTENT"], ENT_NOQUOTES) );
      
  } elseif($action=="doEdit" && $pagename!==FALSE && isset($_REQUEST["content"])) { //If the action is doEdit and a valid pagename and some content was passed, try to update the page.
    /* To write the content: Open the file specified, making a file handle $wf.  Write the content gotten to $wf.  Then close $wf. */
    $fContent=fopen("../lib/staticContent/".$file, "w"); //It is safe to trust $file because it was verified when determining a $pageanme (and we've already check to make sure $pagename isn't FALSE above.
    $fSettings=fopen("../lib/config/footer.php", "w");
    if($fContent!==FALSE && $fSettings!==FALSE) { //Successfully opened file.            
      if (!isset($_REQUEST['name'])) { $set['name']='FALSE'; } else { $set['name']='TRUE'; }
      if (!isset($_REQUEST['links'])) { $set['links']='FALSE'; } else { $set['links']='TRUE'; }
      /* write settings file, with new settings */
      $settings='<?php
/****************************************************************************************************************************
*	footer.php - Settings for displaying the collection\'s footer.
*                WARNING: Any changes made in this file must also be made in the write command in configureFooter.php!
******************************************************************************************************************************/

/* Show collection name in footer */
$FOOTER["SHOW_NAME"]='.$set['name'].';

/* Show menu links in footer */
$FOOTER["SHOW_LINKS"]='.$set['links'].';

/* Call static footer content from file */
$file_location = dirname(__DIR__) . "/staticContent/footer.html";
// dirname(__DIR__) puts you in the directory above this one 
$FOOTER["CONTENT"] = file_get_contents($file_location);

?>';
      $result1=fwrite($fContent, $_REQUEST["content"]);
      $result2=fwrite($fSettings, $settings);
      if($result1!==FALSE && $result2!==FALSE) { //Successful writing file.
        $smarty->assign("alert", array("type"=>"positive", "message"=>"Successfully updated ".$pagename.".") );
        // clean up HTML tags for proper display in textarea/database storage
        $smarty->assign("cleanFooterContent", htmlspecialchars($FOOTER["CONTENT"], ENT_NOQUOTES) );
        //require("../lib/config/footer.php"); // require footer config again to get new settings  << DOESN'T WORK
      } else { //Failed to write to file.
        $smarty->assign("alert", array("type"=>"negative", "message"=>"Unable to update ".$pagename." due to error opening file.<br />
        Please report this error to the collection maintainer.</p>") );
      }
      fclose($fContent); //Close file.
      fclose($fSettings);
    } else { //Failed to open file
      $smarty->assign("alert", array("type"=>"negative", "message"=>"Unable to update ".$pagename." due to error opening file.<br />
      Please report this error to the collection maintainer.</p>") );
    }
  } else { //Unknown/unhandled action specified.
    $smarty->assign("alert", array("type"=>"negative", "message"=>"Unknown or Unhandled Action Specified</h1>") );
  }
}
   
  $smarty->display('configureFooter.php.tpl');   