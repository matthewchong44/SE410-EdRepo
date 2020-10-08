<?php session_start();
 /****************************************************************************************************************************
 *    forgotPassword.php - Allows users to recover their password/get a new password.
 *    --------------------------------------------------------------------------------------
 *  Allows users who have forgotten their password to get a new one or have their old one sent to their email address.
 *
 *  Version: 1.0
 *  Author: Ethan Greer (portions by Douglas Lovell)
 *  Modified by: Jon Thompson (5/20/2011 - implemented new interface/Smarty)
 *
 *  Notes: - Not very secure.  Better security would require some back-end changes.
 *         - This file accepts the following POST/GET parameters:
 *              action : One of "display" (default) to display a form to identify the user, or "recover" to actually recover/reset
 *                  the user's password.
 *              email : The email address of the user who forgot their password.
 ******************************************************************************************************************************/
  
require("lib/config.php");
//require("lib/backends/mysql/validate.php");
  $smarty->assign("title", $COLLECTION_NAME . ' - Forgot Password');
    // title of this page. For most pages: &COLLECTION . " - Title" , default: $COLLECTION_NAME
  $smarty->assign("tab", "home"); // active nav tab. default:  "home"
  $smarty->assign("baseDir", getBaseDir() ); // should always be getBaseDir() 
  
  $smarty->assign("pageName", "Forgot Password");
    // name of page to placed in <h1> tag
    
  $smarty->assign("alert", array("type"=>"", "message"=>"") );
  
 
  
  $action="display"; //Determines what action to take.  The default is to just display a "create account" form.
  if(isset($_REQUEST["action"]) && !isset($userInformation)) {
    $action=$_REQUEST["action"];
  }
  $smarty->assign("action", $action);
  
  $backendCapable = false;
  $loggedIn = false;
  
  if(in_array("UseUsers", $backendCapabilities["write"])) { //Make sure the back-end supports this feature.
    $backendCapable = true;
  }
  if(isset($userInformation)) { //Logged in users obviously remembered their passwords.  Tell them how to change it, but don't actually send/reset it.
    $loggedIn = true;
  }
  $smarty->assign("loggedIn", $loggedIn);
  $smarty->assign("backendCapable", $backendCapable);
  
if ($backendCapable == true && $loggedIn == false) { //The back-end supports account creation and we're not logged in, so we can create an account.
  if($action=="display") {
    // Smarty handles this
    
  } elseif($action=="recover") { //Actually try to recover/reset the password, and send the result to the user's email.
    if(!isset($_REQUEST["email"])) { //If true, no password was given.  Given error.
      $smarty->assign("alert", array("type"=>"negative", "message"=>"No email address was found while attempting to recover a lost password.  
      Unable to continue.") );
    } else { //We have an email, so check to make sure its a valid email and if it is, send the user's password to the email.
      if(!validEmail($_REQUEST["email"])) {
        $smarty->assign("alert", array("type"=>"negative", "message"=>"The email address entered is not a valid email address. 
        Please re-check the email address for accuracy.") );
        $smarty->assign("email", htmlspecialchars($_REQUEST["email"]) );
      } else { //A valid email address was given, so process it.
        /* NOTE:  We _ALWAYS_ report success, even if the email address doesn't exist in the collection!  This is to make it harder
          for attackers to abuse this password recovery system to detect emails which actually exist in the system! */
        $user=searchUsers(array("email"=>$_REQUEST["email"]));
        if($user!==FALSE && $user!="NotImplimented" && count($user)==1) { //If no error eas reported searching for the user by email, and the search was supported, and returned exactly one user was returned, send them an email with their password.
          //Build a message to send in the $message variable.
          $message=$user[0]["firstName"]." ".$user[0]["lastName"].", \n\n";
          $message=$message."You or somebody else requested that your password for your account on the ".$COLLECTION_NAME." collection be sent ";
          $message=$message."to you via the 'Forgot Password' tool.  Your account information is below:\n\n";
          $message=$message."Email Address: ".$user[0]["email"]."\n";
          $message=$message."     Password: ".$user[0]["password"]."\n\n\n";
          $message=$message."You can log into your account using the log-in information about.  Once logged in, you may modify your account ";
          $message=$message."if you wish.\n--------------------------\n";
          $message=$message."This is an automatically generated email.  Please do not reply.\n";
          $message=$message."For security purposes, it is reccomended that your delete this email once you are able to log into your account.";
          $message=wordwrap($message, 70); //Word-Wrap the message at 70 lines.
          $subject="Your password for the ".$COLLECTION_NAME." collection";
          mail($user[0]["email"], $subject, $message); //Send email to the user with the message and subject built above.
        }
        /* Report success, no matter if an email was actually sent.  This helps avoid attacks which use this feature to discover the email addresses
          of users of this system based on if a password recovery email could be sent or not. */
        $smarty->assign("alert", array("type"=>"positive", "message"=>"An email has been sent to the ".$_REQUEST["email"]." with 
        directions on how to recovering your password.") );
      }
    }
  }
}

 $smarty->display('forgotPassword.php.tpl');
?>