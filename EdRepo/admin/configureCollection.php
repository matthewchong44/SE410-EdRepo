<?php session_start();
/****************************************************************************************************************************
 *    configureHeader.php - Allows editing of collection settings.
 *    ---------------------------------------------------------------------------------------------------------
 *  Allows administrators to easily edit pages whcih contain static content.
 *
 *  Version: 1.0
 *  Author: Jon Thompson 
 *
 *  Notes: - Only Admins may use this page.
 *         - This page uses the following GET/POST parameters:
 *            action : One of "displayEdit" (default) which will display the specified static content for editing.
 *                            "doEdit" will attempt to save the edited progress.
 *            page : The page of static HTML to edit.  This parameter should be the name of a file in the lib/staticContent
 *              directory and must be recognized by this page (see the inilitilzing code).
 ******************************************************************************************************************************/
  
  require("../lib/config.php");

  $smarty->assign("title", $COLLECTION_NAME . " - Admin - Configure Collection");
    // title of this page. For most pages: &COLLECTION . " - Title" , default: $COLLECTION_NAME
  $smarty->assign("tab", "admin"); // active nav tab. default:  "home"
  $smarty->assign("baseDir", getBaseDir() ); // should always be getBaseDir() 
  
  $smarty->assign("pageName", "Admin - Configure Collection");
  
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
  $pagename="Collection"; //By default a valid page wasn't given.
  $file="config.php";


if (isset($userInformation) && $userInformation["type"]=="Admin") { //This else block is if we're logged in as an admin.

  if($action=="displayEdit") {
      
  } elseif($action=="doEdit" && $pagename!==FALSE) { //If the action is doEdit and a valid pagename and some content was passed, try to update the page.
  
    $fSettings=fopen("../lib/config/config.php", "w");
    if($fSettings!==FALSE) { //Successfully opened file.            
      if (!isset($_REQUEST['name'])) { $set['name']='EdRepo'; } else { $set['name']=$_REQUEST['name']; }
      /* write settings file, with new settings */
      $settings='<?php
/****************************************************************************************************************************
 *    config.php - Collection-wide configuration settings.
 *    -----------------------------------------------------
 *  Contains settings which are used collection-wide by the front-end.
 *
 *  Version: 1.0
 *  Author: Ethan Greer
 *
 *  Notes: - Do NOT put functions in this file, since it is imported by many files which may themselves be imported by files 
 *         which have already imported this.  Placeing functions in this file is likely to result in "Can not redeclare 
 *         <function> errors" !!
 ******************************************************************************************************************************/


/* COLLECTION_NAME is the name of your collection. */
$COLLECTION_NAME="'.$set['name'].'";

/* COLLECTION_SHORTNAME is a simple, short name for your collection, used with harvesting.  It may only contain letters and 
  number (no  spaces, puncuation, etc) */
$COLLECTION_SHORTNAME="EdRepoTestCollection";

/* COLLECTION_BASE_URL is the base address users access EdRepo from on your server.  So, for example, if your EdRepo homepage 
  was at http://wwww.example.com/edrepo/index.php, the base URL would be /edrepo/ */
$COLLECTION_BASE_URL="/swenet3/";

/* NEW_ACCOUNTS_REQUIRE_APPROVAL determines if new accounts are automatically activated or if they must first be approved.
    Set to TRUE to activate new accounts immedietly.
           FALSE to disable new accounts until an administrator activates them.
   NEW ACCOUNTS_ACCOUNT_TYPE determines the account time new accounts are by default.  The account type determines the privilege lebvel.
    Valid levels (from lowest privileges to heighest) are: Viewer, SuperViewer, Submitter, Editor, Admin
  The default action is to automatically activate new users, but place them in the lowest privilege level. */
$NEW_ACCOUNTS_REQUIRE_APPROVAL=TRUE;
$NEW_ACCOUNTS_ACCOUNT_TYPE="Viewer";

$EMAIL_MODERATORS_ON_NEW_USERS_PENDING_APPROVAL=FALSE;
$EMAIL_MODERATORS_ON_NEW_USERS_PENDING_APPROVAL_CLASS=array("Admin");
$EMAIL_MODERATORS_ON_NEW_USERS_PENDING_APPROVAL_LIST=array();

/* NEW_MODULES_REQUIRE_MODERATION determines if modules are automatically made active to the collection when submitted, or if they 
  must first be approved by a moderator.
    Set to TRUE to require moderation of submitted modules.  Modules will not become active or visible in the collection until
      they have been approved by a moderator (Editor or above privilege level).
           FALSE to publish and activate modules as soon as they are submitted, without requiring moderation. */
$NEW_MODULES_REQUIRE_MODERATION=TRUE;

/* EMAIL_MODERATORS_ON_NEW_MODULE_PENDING_MODERATION determines if one or more email addresses should be notified when a new module is submitted 
      for moderation by a user.
    Set to TRUE to enable this behavior.
           FALSE to disable this behavior. (default) */
$EMAIL_MODERATORS_ON_NEW_MODULE_PENDING_MODERATION=FALSE;

/* EMAIL_MODERATORS_ON_NEW_MODULE_PENDING_MODERATION_CLASS determines any classes of users which should receive email alerts when new modules are 
      pending moderation.  Sending emails when modules are pending moderation must be enabled seperatly for this to have any effect.
    This configuration variable should be set to an array of user types.  Every user of each type specified in the array will receive an email 
      alerting them of new modules pending moderation.  To prevent sending emails to every member of one or more class(es), set this to an empty
      array ( array() ).
    Default value:  array("Editor", "Admin")
        which will sending email to all users of type Editor or Admin. */
$EMAIL_MODERATORS_ON_NEW_MODULE_PENDING_MODERATION_CLASS=array("Editor", "Admin");

/* EMAIL_MODERATORS_ON_NEW_MODULE_PENDING_MODERATION_LIST allows you to send email alerts of new modules pending moderation to a specific set of 
      email addresses.  These email addresses to not have to belong to users on the system.  For these emails to be send, sending emails when 
      new modules are pending moderation must be enabled speretly.
    This configuration variable should be an array, with each element a valid email address which alerts will be send to.
    Default value:  array()
        which is an empty list. */
$EMAIL_MODERATORS_ON_NEW_MODULE_PENDING_MODERATION_LIST=array();

/* MATERIAL_STORAGE_DIR should be set to the path on your file system which will store uploaded materials.  This directory needs to be 
    readable and writable by your web server (or whatever process is running PHP). */
$MATERIAL_STORAGE_DIR="/materials/";

?>
';
      $result=fwrite($fSettings, $settings);
      if($result!==FALSE) { //Successful writing file.
        $smarty->assign("alert", array("type"=>"positive", "message"=>"Successfully updated ".$pagename.".") );
        // clean up HTML tags for proper display in textarea/database storage
        $smarty->assign("cleanCollectionContent", htmlspecialchars($FOOTER["CONTENT"], ENT_NOQUOTES) );
        //require("../lib/config/footer.php"); // require footer config again to get new settings  << DOESN'T WORK
      } else { //Failed to write to file.
        $smarty->assign("alert", array("type"=>"negative", "message"=>"Unable to update ".$pagename." due to error opening file.<br />
        Please report this error to the collection maintainer.</p>") );
      }
      //Close file.
      fclose($fSettings);
    } else { //Failed to open file
      $smarty->assign("alert", array("type"=>"negative", "message"=>"Unable to update ".$pagename." due to error opening file.<br />
      Please report this error to the collection maintainer.</p>") );
    }
  } else { //Unknown/unhandled action specified.
    $smarty->assign("alert", array("type"=>"negative", "message"=>"Unknown or Unhandled Action Specified</h1>") );
  }
}
   
  $smarty->display('configureCollection.php.tpl');   