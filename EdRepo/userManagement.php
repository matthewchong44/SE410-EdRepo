<?php session_start();
/****************************************************************************************************************************
 *    userManagement.php - Allows managing all users of the system.
 *    -------------------------------------------------------------
 *  Displays an optionally filtered list of users on the system, and allows editing, creating, and removing users.
 *
 *  Version: 1.0
 *  Author: Ethan Greer
 *
 *  Notes: - Only Admins may use this page.
 ******************************************************************************************************************************/
  
  require("lib/backends/backend.php");
  require("lib/look/look.php");
  require("lib/config/config.php");
  require("lib/frontend-ui.php");
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
  
  /* By setting the action to "display" (harmless) and then only changing it if we have confirmed that we're actually logged in, we avoid the possibility of 
    someone not logged in setting an action to change something.  We also cna then assume, later, that if the action is anything but "display" we are 
    indeed logged in. */
  $wasFiltered=FALSE; //Set to true if the results were filtered.
  $action="display";
  if(isset($userInformation)) {
    if(isset($_REQUEST["action"])) {
      $action=$_REQUEST["action"];
    }
  }
  if($action=="display" && isset($userInformation) && $userInformation["type"]=="Admin") {
    if(isset($_REQUEST["filterName"])) {
      $users=searchUsers(array("name"=>$_REQUEST["filterName"])); //Get users based on filter
      $wasFiltered=TRUE;
    } else {
      $users=searchUsers(array()); //Get all users
    }
  }
  
?>
<html>
<head>
  <link rel="stylesheet" href="<?php echo "lib/look/".$LOOK_DIR."/main.css"; ?>"></link>
  <title><?php if(isset($userInformation)) { echo "User Management for ".$COLLECTION_NAME; } else { echo "You Must Be Logged In To View This Page"; } ?></title>
  <script type="text/javascript" src="lib/sorttable/sorttable.js"></script>
  <script type="text/javascript">
    function quickValidateNewAccountFields() {
      var email=document.getElementById("email").value;
      var firstName=document.getElementById("firstName").value;
      var lastName=document.getElementById("lastName").value;
      if(firstName.search("\"")!=-1 || lastName.search("\"")!=-1 || email.search("\"")!=-1) {
        alert("Sorry, but first names, last, names, and email addresses may not contain quote marks.");
        return false;
      }
      return true;
    }
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
            echo 'or <a href="loginLogout.php?action=logout">log out</a>.';
          } else {
            echo 'Welcome. &nbsp;Please <a href="loginLogout.php?action=login">login</a> to your account, or <a href="createAccount.php">create a new account</a>.';
          }
        ?>
      </div>
      <?php
        if(!isset($userInformation)) {
          echo "<h1>You Must Be Logged In To Continue</h1>";
          echo "<p>You are not logged in.  You must be logged in to view this page.</p";
          echo '<p>Please <a href="loginLogout.php">log in</a> to access this page.</p';
        } elseif($userInformation["type"]!="Admin") { //We are not an admin...
          echo '<h1>Insufficient Privileges To Perform This Action</h1>';
          echo '<p>You do not have sufficient privileges to view or use this page.  Please log out and log back in as a user with greater privileges to use this page.</p>';
        } else { //This else block is if we're logged in as an admin.
          echo '<h1>User Management</h1>';
          if($action=="display") { //Just show current account information
            if(!in_array("UseUsers", $backendCapabilities["read"])) {
              echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img>The backend in use does not support working with users in read mode, which is required to use this page.</p>';
            } else {
              echo '<form name="nameSearchForm" method="get" action="userManagement.php">';
              echo '<input type="hidden" readonly="readonly" name="action" value="display"></input>';
              echo '<a class="button" href="userManagement.php?action=displayCreateAccount">Add New User</a> | ';
              if(!isset($_REQUEST["filterName"])) {
                echo 'By name: <input type="text" name="filterName" id="filterTextInput" value="" onclick="document.getElementById(\'filterTextInput\').value=\'\';"></input>';
              } else {
                echo 'By name: <input type="text" name="filterName" id="filterTextInput" value="'.preg_replace('/"/', '&quot;', $_REQUEST["filterName"]).'" onclick="document.getElementById(\'filterTextInput\').value=\'\';"></input>';
              }
              echo '<input type="submit" class="button" name="sub" value="Filter"></input></form>';
              if($wasFiltered==FALSE) {
                //echo '<p>Users for this collection:</p>';   -- Is this necessary?
              } else {
                echo '<p>Users matching your search criteria:</p>';
              }
              if(count($users)<=0) {
                if($wasFiltered==FALSE) {
                  echo '<p>There are currently no users in the system.</p>';
                } else {
                  echo '<p>No users were found matching you search criteria.</p>';
                }
              } else {
                echo '<table class="sortable UserInformationView">';
                echo '<thead><tr><th>User ID</th><th>Name</th><th>Email</th><th>Type</th><th class="sorttable_nosort">Edit</th><th class="sorttable_nosort">Change Password</th><th class="sorttable_nosort">Delete</th></tr></thead><tbody>';
                for($i=0; $i<count($users); $i++) {
                  echo '<tr><td>'.$users[$i]["userID"].'</td><td>'.$users[$i]["firstName"].' '.$users[$i]["lastName"].'</td><td>'.$users[$i]["email"].'</td><td>'.$users[$i]["type"].'</td>';
                  echo '<td><a href="userManagement.php?action=displayEdit&userID='.$users[$i]["userID"].'">Edit</a></td>';
                  echo '<td><a href="userManagement.php?action=displayChangePassword&userID='.$users[$i]["userID"].'">Change Password</a></td>';
                  echo '<td><a href="userManagement.php?action=confirmAccountRemoval&userID='.$users[$i]["userID"].'">Delete</a></td></tr>';
                }
                echo '</body></table>';
              }
            }
          } elseif($action=="displayEdit" && isset($_REQUEST["userID"])) {
            if(!in_array("UseUsers", $backendCapabilities["read"]) || !in_array("UseUsers", $backendCapabilities["write"])) {
              echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img>The backend in use does not support working with users in read and/or write mode, which is required to use this page.</p>';
            } else {
              $editUserInfo=getUserInformationByID($_REQUEST["userID"]);
              echo '<p>Change any information you wish to below, and select "Apply" to save changes.</p>';
              echo '<form name="editAccountForm" method="post" action="userManagement.php">';
              echo '<input type="hidden" readonly="readonly" name="action" value="doEdit"></input>';
              echo '<input type="hidden" readonly="readonly" name="userID" value="'.$editUserInfo["userID"].'"></input>';
              echo '<fieldset>';
              echo '<div class="fieldRow"><label for="firstName"><strong>First Name:</strong></label><input type="text" name="firstName" value="'.$editUserInfo["firstName"].'"></input></div>';
              echo '<div class="fieldRow"><label for="lastName"><strong>Last Name:</strong></label><input type="text" name="lastName" value="'.$editUserInfo["lastName"].'"></input></div>';
              echo '<div class="fieldRow"><label for="email"><strong>Email Address:</strong></label><input type="text" name="email" value="'.$editUserInfo["email"].'"></input></div>';
              echo '<div class="fieldRow"><label for="firstName"><strong>Type:</strong></label>';
               echo '<select name="type">';
                if($editUserInfo["type"]=="Disabled") {
                  echo '<option value="Pending" selected="selected">Pending Approval</option>';
                } else {
                  echo '<option value="Pending">Pending Approval</option>';
                }
                if($editUserInfo["type"]=="Viewer") {
                  echo '<option value="Viewer" selected="selected">Viewer</option>';
                } else {
                  echo '<option value="Viewer">Viewer</option>';
                }
                if($editUserInfo["type"]=="SuperViewer") {
                  echo '<option value="SuperViewer" selected="selected">SuperViewer</option>';
                } else {
                  echo '<option value="SuperViewer">SuperViewer</option>';
                }
                if($editUserInfo["type"]=="Submitter") {
                  echo '<option value="Submitter" selected="selected">Submitter</option>';
                } else {
                  echo '<option value="Submitter">Submitter</option>';
                }
                if($editUserInfo["type"]=="Editor") {
                  echo '<option value="Editor" selected="selected">Editor</option>';
                } else {
                  echo '<option value="Editor">Editor</option>';
                }
                if($editUserInfo["type"]=="Admin") {
                  echo '<option value="Admin" selected="selected">Admin</option>';
                } else {
                  echo '<option value="Admin">Admin</option>';
                }
                if($editUserInfo["type"]=="Disabled") {
                  echo '<option value="Disabled" selected="selected">Disabled</option>';
                } else {
                  echo '<option value="Disabled">Disabled</option>';
                }
               echo '</select>';
              echo '</div>';
              echo '</fieldset>';
              echo '<fieldset class="buttons"><input type="submit" class="button" name="submit" value="Apply"></input><input type="reset" class="button" name="reset" value="Reset"></input><a href="userManagement.php" class="button">Cancel</a></fieldset>';
              echo '</form>';
            }
          } elseif($action=="displayChangePassword" && isset($_REQUEST["userID"])) {
            $editUserInfo=getUserInformationByID($_REQUEST["userID"]);
            if(!in_array("UseUsers", $backendCapabilities["read"]) || !in_array("UseUsers", $backendCapabilities["write"])) {
              echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> The backend in use does not support working with users in read and/or write mode, which is required to use this page.</p>';
            } else {
              echo '<p><span class="note">Changing password for user '.$editUserInfo["firstName"].' '.$editUserInfo["lastName"].'.</span></p>';
              if($editUserInfo["userID"]!=$userInformation["userID"]) { //Are we editing a password for a user who isn't the logged in user?
                echo '<p><span class="warning">It is not reccomended that you change this user\' pasword.</span>  Changing a user\'s password can make it impossible for the user to log in.  Unless you are ';
                echo 'resetting this user\'s password, it is recomended that you do not change their password and instead allow the user to change their password themselves from their "My Account" panel.</p>';
              } else {
                echo '<p><span class="note">You are changing your own account\'s password.</span>  You can also change your password, as well as other account details, from your "My Account" panel.</p>';
              }
              echo '<form name="changePasswordForm" action="userManagement.php" method="post"><fieldset>';
              echo '<input type="hidden" readonly="readonly" name="action" value="doChangePassword"></input>';
              echo '<input type="hidden" readonly="readonly" name="userID" value="'.$editUserInfo["userID"].'"></input>';
              echo '<div class="fieldRow"><label for="newPassword1"><strong>New Password:</strong></label><input name="newPassword1" type="password"></input></div>';
              echo '<div class="fieldRow"><label for="newPassword2"><strong>Retype New Password:</strong></label><input name="newPassword2" type="password"></input></div></fieldset>';
              echo '<fieldset class="buttons"><input type="submit" class="button" name="submit" value="Change Password"></input>';
			  echo '<a href="userManagement.php" class="button">Cancel</a></fieldset>';
              echo '</form>';
            }
          } elseif($action=="doEdit" && isset($_REQUEST["userID"])) {
            $editUserInfo=getUserInformationByID($_REQUEST["userID"]);
            if(!in_array("UseUsers", $backendCapabilities["read"]) || !in_array("UseUsers", $backendCapabilities["write"])) {
              echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img>The backend in use does not support working with users in read and/or write mode, which is required to use this page.</p>';
            } else {
              if(!isset($_REQUEST["firstName"]) || !isset($_REQUEST["lastName"]) || !isset($_REQUEST["email"]) || !isset($_REQUEST["type"])) { //If true, we don't have enough information to change anything
                echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> Error.  Not enough information given to change anything.</p>';
              } else {
                $result=editUserByID($_REQUEST["userID"], $_REQUEST["email"], $_REQUEST["firstName"], $_REQUEST["lastName"], "", $_REQUEST["type"], TRUE, FALSE);
                if($result!==TRUE) {
                  echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> Unable to update account.  Please check your changes below and try again.</p>';
                  echo '<form name="editAccountForm" method="post" action="userManagement.php">';
                  echo '<input type="hidden" readonly="readonly" name="action" value="doEdit"></input>';
				  echo '<input type="hidden" readonly="readonly" name="userID" value="'.$_REQUEST["userID"].'"></input>';
                  echo '<fieldset>';
                  echo '<div class="fieldRow"><label for="firstName"><strong>First Name:</strong>';
                  if($result=="BadFirstName") {
                    echo '<br><span class="error">The first name entered is invalid.</span>';
                  }
                  echo '</label><input type="text" name="firstName" value="'.$_REQUEST["firstName"].'"></input></div>';
                  echo '<div class="fieldRow"><label for="lastName"><strong>Last Name:</strong>';
                  if($result=="BadLastName") {
                    echo '<br><span class="error">The last name entered is invalid.</span>';
                  }
                  echo '</label><input type="text" name="lastName" value="'.$_REQUEST["lastName"].'"></input></div>';
                  echo '<div class="fieldRow"><label for="email"><strong>Email Address:</strong>';
                  if($result=="BadEmail") {
                    echo '<br><span class="error">The email addresses entered is invalid.</span>';
                  }
                  echo '</label><input type="text" name="email" value="'.$_REQUEST["email"].'"></input></div>';
                  /*if($userInformation["type"]=="Admin") {  // Not sure what purpose this serves
                    echo '<tr><td class="userInformationViewCategory">Type*</td><td>'.$userInformation["type"].'</td></tr>';
                  } else {
                    echo '<tr><td class="userInformationViewCategory">Type</td><td>'.$userInformation["type"].'</td></tr>';
                  }*/
                  echo '<div class="fieldRow"><label for="type"><strong>Type:</strong></label>';
                  echo '<select name="type">';
                    if($_REQUEST["type"]=="Disabled") {
                      echo '<option value="Pending" selected="selected">Pending Approval</option>';
                    } else {
                      echo '<option value="Pending">Pending Approval</option>';
                    }
                    if($_REQUEST["type"]=="Viewer") {
                      echo '<option value="Viewer" selected="selected">Viewer</option>';
                    } else {
                      echo '<option value="Viewer">Viewer</option>';
                    }
                    if($_REQUEST["type"]=="SuperViewer") {
                      echo '<option value="SuperViewer" selected="selected">SuperViewer</option>';
                    } else {
                      echo '<option value="SuperViewer">SuperViewer</option>';
                    }
                    if($_REQUEST["type"]=="Submitter") {
                      echo '<option value="Submitter" selected="selected">Submitter</option>';
                    } else {
                      echo '<option value="Submitter">Submitter</option>';
                    }
                    if($_REQUEST["type"]=="Editor") {
                      echo '<option value="Editor" selected="selected">Editor</option>';
                    } else {
                      echo '<option value="Editor">Editor</option>';
                    }
                    if($_REQUEST["type"]=="Admin") {
                      echo '<option value="Admin" selected="selected">Admin</option>';
                    } else {
                      echo '<option value="Admin">Admin</option>';
                    }
                    if($_REQUEST["type"]=="Disabled") {
                      echo '<option value="Disabled" selected="selected">Disabled</option>';
                    } else {
                      echo '<option value="Disabled">Disabled</option>';
                    }
                  echo '</select>';
                  echo '</div>';
                  echo '</fieldset>';
                  echo '<fieldset class="buttons"><input type="submit" class="button" name="submit" value="Apply"></input><input type="reset" class="button" name="reset" value="Reset"></input><a href="userManagement.php" class="button">Cancel</a></fieldset>';
                  echo '</form>';
                } else {
                  echo '<p class="alert positive"><img src="lib/look/'.$LOOK_DIR.'/success.png"></img> Information Successfully Updated.</p>';
                  echo '<p><a href="userManagement.php">Return to the User Management Panel</a>.</p>';
                }
              }
            }
          } elseif($action=="doChangePassword" && isset($_REQUEST["userID"])) {
            $editUserInfo=getUserInformationByID($_REQUEST["userID"]);
            if(!in_array("UseUsers", $backendCapabilities["read"]) || !in_array("UseUsers", $backendCapabilities["write"])) {
              echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> The backend in use does not support working with users in read and/or write mode, which is required to use this page.</p>';
            } else {
              if(!isset($_REQUEST["newPassword1"]) || !isset($_REQUEST["newPassword2"]) || !isset($_REQUEST["userID"])) {
                echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> Unable to change your password.  One or more required pieces of information is missing.</p>';
              } elseif($_REQUEST["newPassword1"]!=$_REQUEST["newPassword2"]) {
                echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> Unable to change your password.  The two passwords entered for your new password do not match.</p>';
				  echo '<form name="changePasswordForm" action="userManagement.php" method="post"><fieldset>';
				  echo '<input type="hidden" readonly="readonly" name="action" value="doChangePassword"></input>';
				  echo '<input type="hidden" readonly="readonly" name="userID" value="'.$editUserInfo["userID"].'"></input>';
				  echo '<div class="fieldRow"><label for="newPassword1"><strong>New Password:</strong></label><input name="newPassword1" type="password"></input></div>';
				  echo '<div class="fieldRow"><label for="newPassword2"><strong>Retype New Password:</strong></label><input name="newPassword2" type="password"></input></div></fieldset>';
				  echo '<fieldset class="buttons"><input type="submit" class="button" name="submit" value="Change Password"></input>';
				  echo '<a href="userManagement.php" class="button">Cancel</a></fieldset>';
				  echo '</form>';
              } else {
                $result=editUserByID($editUserInfo["userID"], $editUserInfo["email"], $editUserInfo["firstName"], $editUserInfo["lastName"], $_REQUEST["newPassword1"], "", FALSE, TRUE);
                if($result===TRUE) {
                  echo '<p class="alert positive"><img src="lib/look/'.$LOOK_DIR.'/success.png"> Your password has been successfully changed.</p>';
                } else {
                  if($result=="BadPassword") {
                    echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> The new password is not strong enough or otherwise invalid.</p>';
                    echo '<form name="changePasswordForm" action="userManagement.php" method="post">';
                    echo '<input type="hidden" readonly="readonly" name="action" value="doChangePassword"></input>';
                    echo '<input type="hidden" readonly="readonly" name="userID" value="'.$editUserInfo["userID"].'"></input>';
                    echo 'New Password: <input name="newPassword1" type="password"></input><br>';
                    echo 'New Password (again): <input name="newPassword2" type="password"></input><br>';
                    echo '<input type="submit" name="submit" value="Change Password"></input>';
                    echo '</form>';
                  } else {
                    echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> An unknown error occurred while trying to change your password.</p>';
                    echo '<p>If this problem persists, please contact the collection maintainers to report the issue.</p>';
                  }
                }
              }
            }
          } elseif($action=="displayCreateAccount") {
            if(in_array("UseUsers", $backendCapabilities["read"]) && in_array("UseUsers", $backendCapabilities["write"])) { //Only continue if the back-end supports working with users in read/write mode.
              echo '<h3>Create New Account</h3>';
              echo '<p>Enter all information about the user of the new account below and select "Create Account" to add the account.</p>';
              echo '<form name="newAccountForm" method="post" action="userManagement.php">';
              echo '<input type="hidden" name="action=" value="doCreateAccount" readonly="readonly"></input>';
              echo '<fieldset>';
              echo '<input type="hidden" readonly="readonly" name="action" value="doCreateAccount"></input>';
              echo '<div class="fieldRow"><label for="firstName"><strong>First Name:</strong></label><input type="text" name="firstName" id="firstName"></input></div>';
              echo '<div class="fieldRow"><label for="lastName"><strong>Last Name:</strong></label><input type="text" name="lastName" id="lastName"></input></div>';
              echo '<div class="fieldRow"><label for="email"><strong>Email Address:</strong></label><input type="text" name="email" id="email"></input></div>';
              echo '<div class="fieldRow"><label for="type"><strong>Type:</strong></label>';
              echo '<select name="type">';
               echo '<option value="Pending">Pending Approval</option>';
               echo '<option value="Viewer" selected="selected">Viewer</option>';
               echo '<option value="SuperViewer">SuperViewer</option>';
               echo '<option value="Submitter">Submitter</option>';
               echo '<option value="Editor">Editor</option>';
               echo '<option value="Admin">Admin</option>';
               echo '<option value="Disabled">Disabled</option>';
              echo '</select></div>';
              echo '<div class="fieldRow"><label for="password1"><strong>Password:</strong></label><input type="password" name="password1"></input></div>';
              echo '<div class="fieldRow"><label for="password2"><strong>Retype Password:</strong></label><input type="password" name="password2"></input></div>';
              echo '</fieldset><fieldset class="buttons">';
              echo '<input type="submit" class="button" name="sub" value="Create Account" onclick="return quickValidateNewAccountFields();"></input> ';
              echo '<a class="button" href="userManagement.php?action=display">Cancel</a></fieldset>';
              echo '</form>';
            } else { //This else block runs if the back-end doesn't support working with users in read/write mode, which is required.
              echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> The back-end in use does not support working ';
              echo 'with users in read and/or write mode.  Creating a new user/account requires the back-end to support working with users in read and ';
              echo 'write mode.</p>';
            }
          } elseif($action=="doCreateAccount") {
            if(in_array("UseUsers", $backendCapabilities["read"]) && in_array("UseUsers", $backendCapabilities["write"])) { //Only continue if the back-end supports working with users in read/write mode.
              if(!(isset($_REQUEST["type"]) && isset($_REQUEST["firstName"]) && isset($_REQUEST["lastName"]) && isset($_REQUEST["email"]) && isset($_REQUEST["password1"]) && isset($_REQUEST["password2"]))) {
                echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> Unable to create new user/account.  One ';
                echo 'or more required pieces of information is missing.</p>';
                echo '<p>If you are receiving this error after clicking a button or link from within this system, please report it to the collection ';
                echo 'maintainer.</p>';
              } else {
                $result=createUser($_REQUEST["email"], $_REQUEST["firstName"], $_REQUEST["lastName"], $_REQUEST["password1"], $_REQUEST["type"]);
                if($result===FALSE || $result=="BadEmail" || $result=="EmailAlreadyExists" || $result=="BadPassword" || $result=="BadFirstName" || $result=="BadLastName" || $result=="BadType") {
                  echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> Failed to create the user/account ';
                  echo 'due to one or more errors.  Please corrent any errors and try again.</p>';
                  echo '<form name="createAccountFrom" action="userManagement.php" method="post">';
                  echo '<fieldset>';
                  echo '<input type="hidden" readonly="readonly" name="action" value="doCreateAccount"></input>';
                  echo '<div class="fieldRow"><label for="firstName"><strong>First Name:</strong>';
                  if($result=="BadFirstName") {
                    echo '<br><span class="error">The first name entered is invalid.</span>';
                  }
                  echo '</label><input type="text" name="firstName" id="firstName" value="'.$_REQUEST["firstName"].'"></input></div>';
                  echo '<div class="fieldRow"><label for="lastName"><strong>Last Name:</strong>';
                  if($result=="BadLastName") {
                    echo '<br><span class="error">The last name entered is invalid.</span>';
                  }
                  echo '</label><input type="text" name="lastName" id="lastName" value="'.$_REQUEST["lastName"].'"></input></div>';
                  echo '<div class="fieldRow"><label for="email"><strong>Email Address:</strong>';
                  if($result=="BadEmail") {
                    echo '<br><span class="error">The email address entered is not valid.</span>';
                  }
                  if($result=="EmailAlreadyExists") {
                    echo '<br><span class="error">The email address entered already exists in the system.  Please choose a different email address.</span>';
                  }
                  echo '</label><input type="text" name="email" id="email" value="'.$_REQUEST["email"].'"></input></div>';
                  echo '<div class="fieldRow"><label for="firstName"><strong>Type:</strong>';
                  if($result=="BadType") {
                    echo '<br><span class="error">The type specified is invalid or not supported by the back-end.  Please choose a different type.</span>';
                  }
                  echo '</label>';
                  echo '<select name="type">';
                    if($_REQUEST["type"]=="Disabled") {
                      echo '<option value="Pending" selected="selected">Pending Approval</option>';
                    } else {
                      echo '<option value="Pending">Pending Approval</option>';
                    }
                    if($_REQUES["type"]=="Viewer") {
                      echo '<option value="Viewer" selected="selected">Viewer</option>';
                    } else {
                      echo '<option value="Viewer">Viewer</option>';
                    }
                    if($_REQUES["type"]=="SuperViewer") {
                      echo '<option value="SuperViewer" selected="selected">SuperViewer</option>';
                    } else {
                      echo '<option value="SuperViewer">SuperViewer</option>';
                    }
                    if($_REQUES["type"]=="Submitter") {
                      echo '<option value="Submitter" selected="selected">Submitter</option>';
                    } else {
                      echo '<option value="Submitter">Submitter</option>';
                    }
                    if($_REQUES["type"]=="Editor") {
                      echo '<option value="Editor" selected="selected">Editor</option>';
                    } else {
                      echo '<option value="Editor">Editor</option>';
                    }
                    if($_REQUES["type"]=="Admin") {
                      echo '<option value="Admin" selected="selected">Admin</option>';
                    } else {
                      echo '<opyion value="Admin">Admin</option>';
                    }
                    if($_REQUES["type"]=="Disabled") {
                      echo '<option value="Disabled" selected="selected">Disabled</option>';
                    } else {
                      echo '<option value="Disabled">Disabled</option>';
                    }
                  echo '</select></div>';
                  echo '<div class="fieldRow"><label for="password1"><strong>Password:</strong></label><input type="password" name="password1"></input></div>';
                  echo '<div class="fieldRow"><label for="password2"><strong>Retype Password:</strong></label><input type="password" name="password2"></input></div>';
                  echo '</fieldset>';
                  echo '<fieldset class="buttons"><input type="submit" class="button" name="submit" value="Create Account"></input>';
                  echo '<a href="userManagement.php?action=display" class="button">Cancel</a></fieldset>';
                  echo '</form>';
                } else { //The account was created okay.
                  echo '<p class="alert positive"><img src="lib/look/'.$LOOK_DIR.'/success.png" alt="Success"></img> User and account has been successfully created.</p>';
                  echo '<p><a href="userManagement.php?action=display">Return to user list</a></p>';
                }
              }
            } else { //This else block runs if the back-end doesn't support working with users in read/write mode, which is required.
              echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> The back-end in use does not support working ';
              echo 'with users in read and/or write mode.  Creating a new user/account requires the back-end to support working with users in read and ';
              echo 'write mode.</p>';
            }
          } elseif($action=="confirmAccountRemoval" && isset($_REQUEST["userID"])) {
            $editUserInfo=getUserInformationByID($_REQUEST["userID"]);
            echo '<p class="warning">Are you sure you want to delete the account belonging to '.$editUserInfo["firstName"].' '.$editUserInfo["lastName"].' ('.$editUserInfo["email"].')?</p>';
            echo '<p>Deleting this account is permanent, and can not be undone without manually changing the back-end storage engine.  Once this account has been deleted, the user who owns this account ';
            echo 'will no longer be able to modify, edit, or manage their account, modules, or materials.  In addition, even if a new account is create with the same name and email, it will be  ';
            echo 'considered a different account and will not have access to any modules, materials, or settings belonging to this account.';
            if(in_array("UsersSoftRemove", $backendCapabilities["write"])) {
              echo '<p><span class="note">Notice:  The account will be softly deleted.</span>  A softly deleted account may continue to store certain ';
              echo 'information in it, even once it has been deleted.  This information is necessary to maintain information about modules and materials submitted by the account.  All other information ';
              echo 'about the account, including as much personal information as possible, will be permently deleted.  To remove all information about this account, including information which may ';
              echo 'be require to maintain module and material consistancy, you will need to manually edit the back-end storage database.</p>';
            }
            echo '<a class="button" href="userManagement.php?action=doAccountRemoval&userID='.preg_replace('/"/', '\"', $_REQUEST["userID"]).'">Delete Account</a>';
            echo '<a class="button"  href="userManagement.php">Cancel</a>';
            echo '';
          } elseif($action=="doAccountRemoval" && isset($_REQUEST["userID"])) {
            if(in_array("UsersSoftRemove", $backendCapabilities["write"])) { //If true, the back-end is advertising soft-removal, so use that method.
              $result=removeUsersByID(array($_REQUEST["userID"]), TRUE);
            } else { //Back-end didn't advertise soft-removal, so don't user it.
              $result=removeUsersByID(array($_REQUEST["userID"]), FALSE);
            }
            if($result===TRUE) { //deletion successful
              echo '<p class="alert positive"><img src="lib/look/'.$LOOK_DIR.'/success.png" alt="Success"></img> The account has been successfully deleted.</p>';
              if($_REQUEST["userID"]==$userInformation["userID"]) { //Did we delete ourselves?
                logout(); //Log ourselves out if we just deleted ourselves.
                echo '<p>Since you deleted the account you were currently logged in to, you have also been logged out.</p>';
              }
            } else { //Error deleting account
              echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"> Account deletion failed.</p>';
              echo '<p>If this problem persists, please contact this collection\'s maintainer.</p>';
            }
          } elseif($action=="customError") {
            //Don't do anything.  An action of customError indicates any error handling/messages have already been taken care of.
          } else {
            echo '<p class="alert negative"><img src="lib/look/'.$LOOK_DIR.'/failure.png" alt="Failure"></img> Unknown error while processing request.</p>';
            echo '<p>An unknown error occurred while processing your request.  If you are receiving this error after clicking a link or button from within this system, please ';
            echo 'report this error to the collection maintainer.</p>';
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