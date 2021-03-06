<?php session_start();
/****************************************************************************************************************************
 *    editStaticPages.php - Allows editing of static content pages (such as the about page) used by the system.
 *    ---------------------------------------------------------------------------------------------------------
 *  Allows administrators to easily edit pages whcih contain static content.
 *
 *  Version: 1.0
 *  Author: Ethan Greer
 *
 *  Notes: - Only Admins may use this page.
 *         - This page uses the following GET/POST parameters:
 *            action : One of "displayEdit" (default) which will display the specified static content for editing.
 *                            "doEdit" will attempt to save the edited progress.
 *            page : The page of static HTML to edit.  This parameter should be the name of a file in the lib/staticContent
 *              directory and must be recognized by this page (see the inilitilzing code).
 ******************************************************************************************************************************/
  
  require("../lib/backends/backend.php");
  require("../lib/look/look.php");
  require("../lib/config/config.php");
  require("../lib/frontend-ui.php");
  require("../lib/config/header.php");
  $backendInformation=getBackendBasicInformation();
  $backendCapabilities=getBackendCapabilities();
?>
<?php
  function logout() {
    if(isset($_SESSION["authenticationToken"])) {
      $logOutResult=logUserOut($_SESSION["authenticationToken"]);
    }
    unset($_SESSION["authenticationToken"]);
  }
  
  if(isset($_SESSION["authenticationToken"])) { //Check if we think someone is already logged in.
    $userInformation=checkIfUserIsLoggedIn($_SESSION["authenticationToken"]);
    if(count($userInformation)==0) { //If true, than the user wasn't found
      logout();
      unset($userInformation);
    }
  }
  
  $action="displayEdit"; //Default action is to display an editing panel.
  if(isset($_REQUEST["action"])) {
    $action=$_REQUEST["action"];
  }
  /* To prevent against people abusing this page to edit (or possibly create) any page in the lib/staticContent subdirectory, we'll check to make 
    sure the page given in the $_REQUEST["page"] parameter is a valid page we can edit.  This means you MUST add all pages which can be edited here!
    If the page given isn't found here, or nothing ws given, $pagename will be left as FALSE, indicating no valid page was found.  Otheriwse, 
    $pagename will be set to a friendly, human-readable name for the page. */
  $pagename="Header"; //By default a valid page wasn't given.
  $file="header.html";
?>
<html>
<head>
  <link rel="stylesheet" href="<?php echo "../lib/look/".$LOOK_DIR."/main.css"; ?>"></link>
  <title><?php if(isset($userInformation)) { echo "Edit Content Pages on ".$COLLECTION_NAME; } else { echo "You Must Be Logged In To View This Page"; } ?></title>
  <script type="text/javascript" src="../lib/tiny_mce/tiny_mce.js"></script>
  <script type="text/javascript">
    tinyMCE.init({
      // General options
      mode : "textareas",
      theme : "advanced",
      plugins : "pagebreak,style,layer,table,advhr,advimage,advlink,emotions,inlinepopups,insertdatetime,searchreplace,paste,fullscreen,visualchars,nonbreaking,xhtmlxtras",
            
      // Theme options
      theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
      theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
      theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,ltr,rtl,|,fullscreen",
      theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,blockquote,pagebreak,|,insertfile,insertimage",
      theme_advanced_toolbar_location : "top",
      theme_advanced_toolbar_align : "left",
      theme_advanced_statusbar_location : "bottom",
      theme_advanced_resizing : true,

      // Example content CSS (should be your site CSS)
      content_css : "../lib/look/default/main.css",

      // Drop lists for link/image/media/template dialogs
      template_external_list_url : "js/template_list.js",
      external_link_list_url : "js/link_list.js",
      external_image_list_url : "js/image_list.js",
      media_external_list_url : "js/media_list.js",

      // Replace values for the template plugin
      //template_replace_values : {
      //  username : "Some User",
      //  staffid : "991234"
      //}
    });
  </script>
</head>
<body>
<div id="header">
  <?php
    showHeader();
  ?>
  <div id="top-nav-bar">
    <?php showTopNavMenu(); ?>
  </div>
</div>
<div id="content-body-wrapper">
  <div id="content-body">
    <div id="left-sidebar">
      <?php
        if(isset($userInformation)) {
          if($userInformation["type"]=="Viewer") {
            showViewerMenu();
          } elseif($userInformation["type"]=="SuperViewer") {
            showSuperViewerMenu();
          } elseif($userInformation["type"]=="Submitter") {
            showSubmitterMenu();
          } elseif($userInformation["type"]=="Editor") {
            showEditorMenu();
          } elseif($userInformation["type"]=="Admin") { //We are logged in as an admin.
            showAdminMenu();
          }
        } else { //We aren't logged in.
          showGuestMenu();
        }
      ?>
    </div> <!-- End left-sidebar div -->
    <div id="mainContentArea">
      <div id="mainContentAreaTopInfoBar">
        <?php
          if(isset($userInformation)) {
            echo "You are logged in as ".$userInformation["firstName"]." ".$userInformation["lastName"].'. &nbsp;<a href="userManageAccount.php">Manage Your Account</a> ';
            echo 'or <a href="../loginLogout.php?action=logout">log out</a>.';
          } else {
            echo 'Welcome. &nbsp;Please <a href="../loginLogout.php?action=login">login</a> to your account, or <a href="../createAccount.php">create a new account</a>.';
          }
        ?>
      </div>
      <?php
        if(!isset($userInformation)) {
          echo "<h1>You Must Be Logged In To Continue</h1>";
          echo "<p>You are not logged in.  You must be logged in to view this page.</p";
          echo '<p>Please <a href="../loginLogout.php">log in</a> to access this page.</p';
        } elseif($userInformation["type"]!="Admin") { //We are not an admin...
          echo '<h1>Insufficient Privileges To Perform This Action</h1>';
          echo '<p>You do not have sufficient privileges to view or use this page.  Please log out and log back in as a user with greater privileges to use this page.</p>';
        } else { //This else block is if we're logged in as an admin.
          if($action=="displayEdit") {
            if($pagename===FALSE) { //A $pagename of FALSE means the page given to edit isn't valid, or no page was given to edit.
              echo '<h1>Invalid or Nonexistant Page Given To Edit</h1>';
              echo '<p class="alert negative"><img src="../lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> The page specified can not be edited.</p>';
            } else { //This indicates a valid page was given to edit.  The filename should be in $file and a human-readable name should be in $pagename.
              echo "<h1>Edit This Collection's ".$pagename."</h1>";
              echo '<form name="editStaticContentForm" method="post" action="configureHeader.php">';
              echo '<input type="hidden" readonly="readonly" name="action" value="doEdit"></input>';
			  echo "<h3>Basic Settings</h3>";
			  echo '<p>';
			  echo '<input type="checkbox" name="name" value="TRUE"';
			  if ($HEADER_SHOW_NAME==TRUE) { echo ' checked="checked"'; }
			  echo ' /> Show collection name: <strong>'.$COLLECTION_NAME.'</strong>';
			  echo '</p>';
			  echo '<h3>Header Logo</h3>';
			  echo '<p>Logo filename: <input type="text" name="logo" value="'.$HEADER_LOGO_NAME.'" /><br />';
			  echo '<strong>Note:</strong> Logo file must exist in active look directory. (Leave blank to hide logo.)</p>';
			  echo "<h3>Other Content</h3>";
              echo "<p>Use the editor below to make changes to the content which will be displayed on this collection's ".$pagename.'.  Click ';
              echo '"Save Changes" to save your changes, or "Cancel" to return to the collection configuration panel without saving changes.</p>';
              echo '<textarea name="content" style="width: 90%; height: 200px; margin-left: 10px">'.htmlspecialchars(file_get_contents("../lib/staticContent/".$file), ENT_NOQUOTES).'</textarea><br>';
              echo '<fieldset class="buttons" style="margin-left: 0"><input type="submit" class="button" name="sub" value="Save Changes"></input>';
              echo '<a href="index.php" class="button">Cancel</a></fieldset></form>';
            }
          } elseif($action=="doEdit" && $pagename!==FALSE && isset($_REQUEST["content"])) { //If the action is doEdit and a valid pagename and some content was passed, try to update the page.
            echo '<h1>Save Changes to '.$pagename.'</h1>';
            /* To write the content: Open the file specified, making a file handle $wf.  Write the content gotten to $wf.  Then close $wf. */
            $fContent=fopen("../lib/staticContent/".$file, "w"); //It is safe to trust $file because it was verified when determining a $pageanme (and we've already check to make sure $pagename isn't FALSE above.
			$fSettings=fopen("../lib/config/header.php", "w");
            if($fContent!==FALSE && $fSettings!==FALSE) { //Successfully opened file.            
              if (!isset($_REQUEST['name'])) { $set['name']='FALSE'; } else { $set['name']='TRUE'; } /* set show name value */
			  if (!isset($_REQUEST['logo'])) { $set['logo']=''; } else { $set['logo'] = $_REQUEST['logo']; }
			  /* write settings file, with new settings */
			  $settings='<?php
/****************************************************************************************************************************
*	header.php - Settings for displaying the collection\'s header.
******************************************************************************************************************************/

/* Show collection name in header */
$HEADER_SHOW_NAME='.$set['name'].';

/* File name of header logo (in current theme directory), logo doesn\'t show if blank ("") */
$HEADER_LOGO_NAME="'.$set['logo'].'";

?>';
			  $result1=fwrite($fContent, $_REQUEST["content"]);
			  $result2=fwrite($fSettings, $settings);
              if($result1!==FALSE && $result2!==FALSE) { //Successful writing file.
                echo '<p class="alert positive"><img src="../lib/look/'.$LOOK_DIR.'/success.png" alt="Success"></img> Successfully updated '.$pagename.'.</p>';
              } else { //Failed to write to file.
                echo '<p class="alert negative"><img src="../lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> Unable to update '.$pagename.' due to error writing to file.</p>';
                echo '<p>Please report this error to the collection maintainer.</p>';
              }
              fclose($fContent); //Close file.
			  fclose($fSettings);
            } else { //Failed to open file
              echo '<p class="alert negative"><img src="../lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> Unable to update '.$pagename.' due to ';
              echo 'error opening file.</p>';
              echo '<p>Please report this error to the collection maintainer.</p>';
            }
          } else { //Unknown/unhandled action specified.
            echo '<h1>Unknown or Unhandled Action Specified</h1>';
          }
        }
      ?>
    </div> <!-- End mainContentArea div -->
    <div id="right-sidebar"></div>
  </div>
</div>
<div id="footer">
  <?php showFooter(); ?>
</div>
</body>
</html>