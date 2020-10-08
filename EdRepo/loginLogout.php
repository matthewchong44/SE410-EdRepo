<?php session_start();
/****************************************************************************************************************************
 *    loginLogout.php - Allows users to log into the system and voluntarily log out.
 *    --------------------------------------------------------------------------------------
 *  Allows users to log in using a log-in form, and also provides a page users can access to voluntarily log out of the system with.
 *
 *  Version: 1.0
 *  Author: Ethan Greer
 *
 *  Notes: - Every front-end page should check for a valid log-in, and if an invalid log-in is detected, log the user out.  This
 *        file is just to present a nice page allowing users to log in and out, but every front-end page must validate the login.
 ******************************************************************************************************************************/
  
  require("lib/config.php");

  $smarty->assign("title", $COLLECTION_NAME . " - Login");
    // title of this page. For most pages: &COLLECTION . " - Title" , default: $COLLECTION_NAME
  $smarty->assign("tab", "home"); // active nav tab. default:  "home"
  $smarty->assign("baseDir", getBaseDir() ); // should always be getBaseDir() 
  
  $smarty->assign("pageName", "Login");
  
  $smarty->assign("alert", array("type"=>"", "message"=>"") );
                  // default empty alert message (type can be either positive or negative)
  
  if ( isset($_REQUEST["alert"]) ) {
    if ($_REQUEST["alert"] == "loggedOff") {
        $smarty->assign("alert", array("type"=>"positive", 
                                       "message"=>"You have been successfully logged out of ".$COLLECTION_NAME.".") );
    } elseif ($_REQUEST["alert"] == "loggedIn") {
        $smarty->assign("alert", array("type"=>"positive", "message"=>"You successfully logged in to ".$COLLECTION_NAME.".") );
    }
  }
  
  $loggedOff=FALSE; //TRUE is the system has logged a user off.  FALSE otherwise.
  if(isset($_REQUEST["action"]) && $_REQUEST["action"]=="logout") {
    logout($smarty);
    $loggedOff=TRUE;
  }
  
  $alreadyLoggedIn=FALSE; //TRUE if the user was logged in when they came to this page, FALSE otherwise.
  if(isset($_SESSION["authenticationToken"])) { //Check if we think someone is already logged in.
    $userInformation=checkIfUserIsLoggedIn($_SESSION["authenticationToken"]);
    $alreadyLoggedIn=TRUE;
    if(count($userInformation)==0) { //If true, than the user wasn't found
      logout($smarty);
      unset($userInformation);
      $alreadyLoggedIn=FALSE;
    }
  }
  
  $triedToLogIn=FALSE; //TRUE if the system attempted to log a user in.
  $loginSuccess=FALSE; //TRUE if the system successfully logged a user in.
  //If we are not already logged in, and all needed login parameters are present, and the action is to login, try to login.
  if((isset($_REQUEST["action"]) && isset($_REQUEST["email"]) && isset($_REQUEST["password"])) && ($_REQUEST["action"]=="login") && $alreadyLoggedIn==FALSE) {
    $loginResult=logUserIn($_REQUEST["email"], $_REQUEST["password"]);
    $triedToLogIn=TRUE;
    if($loginResult!==FALSE) {
      $_SESSION["authenticationToken"]=$loginResult;
      $userInformation=checkIfUserIsLoggedIn($loginResult);
      $loginSuccess=TRUE;
    }
  }
  $smarty->assign("loginSuccess", $loginSuccess);
  
//Start by making sure the back-end supports working with users in read mode.
if(!in_array("UseUsers", $backendCapabilities["read"]) || !in_array("UseUsers", $backendCapabilities["write"])) {
  die("The backend currently in use (".$backendInformation["name"]." version ".$backendInformation["version"].") does not support working with users.");
}


if($loggedOff==TRUE) { //If true, we just (intentially, by user's asking) logged someone off    // form action = login
  // redirect to this page to clear out REQUEST variables
  header("Location:" . $_SERVER['PHP_SELF'] . "?alert=loggedOff");
  //$smarty->assign("alert", array("type"=>"positive", "message"=>"You have been successfully logged out of ".$COLLECTION_NAME.".") );
  
} elseif($alreadyLoggedIn==FALSE) {
  if($triedToLogIn==TRUE) { //If here, we tried to log in.
    if($loginSuccess==TRUE) { //Successfully logged in.
      // test to make sure the referring page isn't this page, then redirect back
      $refererBase = basename($_SERVER['HTTP_REFERER']);
      if ($refererBase != "loginLogout.php" && $refererBase != "loginLogout.php?alert=loggedOff") {
        header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
      } else {
            // file = current url - everything after '?' (the arguments)
        /*$file = substr( $_SERVER['PHP_SELF'], 0, strrchr($_SERVER['PHP_SELF'], '?') );
        echo $file;
        header("Location:" . $file . "?alert=loggedIn");*/
        header( 'Location: userManageAccount.php' );
      }
    } else { //Failed to log in.
      $smarty->assign("alert", array("type"=>"negative", "message"=>"Login failed.  The email/password pair entered do not match.  Please try again.") );
      if(isset($_REQUEST["email"])) {
        $smarty->assign("userEmail", $_REQUEST["email"]);
      }
    }
  } else { //Didn't even try to log in, display blank login page.
    // handled by Smarty
  }
} else { //If we're here, we were already logged in.
  header( 'Location: userManageAccount.php' );
  //$smarty->assign("alert", array("type"=>"negative", "message"=>"You are already logged in on this computer!") );
}
        
  $smarty->display('loginLogout.php.tpl');

?>