<?php session_start();
/****************************************************************************************************************************
    moduleWizard/index.php - main page for handling creating/editing modules
    --------------------------------------------------------------------------------------
    Main method for a user to create, edit, or create new version of a module.
 
  Version: 1.0
  Author: Jon Thompson 
  Date: 6 June 2011
 
  Notes: Takes several key REQUEST variables (sent by the form/URL) :
              - moduleAction : dictates whether user is editing, creating, or creating new version; sent by URL (link) or by form
                                         (possible values: 'createNewVersion', 'edit', 'create' and 'error')
              - action : handles saving, deleting and submitting for moderation/publishing; sent by module wizard form only
                            action also handles jumping between form steps
                                 (possible values: 'save', 'delete', 'Submit for Moderation', 'Publish';
                                  for jumping: 'Basic Info', 'Materials', 'References', 'Submit')                     
              - moduleID : ID of module to edit or create new version of; sent by URL or form
              - step : step number user is on in the wizard (1 by default)
                          1 - Basic Info,  2 - Materials,  3 - References,  4 - Submit
              
             Functions:
              - refreshFields($smarty, $moduleID) - Sends fields with multiple values (e.g. objectives, topics) to Smarty 
 ******************************************************************************************************************************/
  
  require("../lib/config.php");
  require("../lib/moduleEditUploadHelpers.php");

  $smarty->assign("title", $COLLECTION_NAME . " - Module Wizard");
    // title of this page. For most pages: &COLLECTION . " - Title" , default: $COLLECTION_NAME
  $smarty->assign("tab", "modules"); // active nav tab. default:  "home"
  $smarty->assign("baseDir", getBaseDir() ); // should always be getBaseDir() 
  
  $smarty->assign("pageName", "Module Wizard");
  
  $smarty->assign("alert", array("type"=>"", "message"=>"") );
                  // default empty alert message (type can be either positive or negative)
                  
  // send all of the collection's available categories to Smarty                
  $smarty->assign("allCategories", getAllCategories() );
  
/**
    refreshFields($smarty, $moduleID)
    Sends fields with multiple values (e.g. objectives, topics) 
    to Smarty template in respective arrays.
     @param  $smarty  Smarty object variable (will always be $smarty)
     @param  $moduleID  ID of the module whose field to refresh
*/
  function refreshFields($smarty, $moduleID) {
    global $backendCapabilities; // get the global variable $backendCapabilities
        
    $savedAuthors=getModuleAuthors($moduleID);
    for($i=0; $i<count($savedAuthors); $i++) {
        $savedAuthors[$i] = $safeJSString=preg_replace('/"/', '\"', $savedAuthors[$i]);
    }
    $smarty->assign("savedAuthors", $savedAuthors);
    
    $savedObjectives=getModuleObjectives($moduleID);
    for($i=0; $i<count($savedObjectives); $i++) {
        $savedObjectives[$i] = $safeJSString=preg_replace('/"/', '\"', $savedObjectives[$i]["text"]);
    }
    $smarty->assign("savedObjectives", $savedObjectives);
    
    $savedTopics=getModuleTopics($moduleID);
    for($i=0; $i<count($savedTopics); $i++) {
        $savedTopics[$i] = $safeJSString=preg_replace('/"/', '\"', $savedTopics[$i]["text"]);
    }
    $smarty->assign("savedTopics", $savedTopics);
    
    $savedPrereqs=getModulePrereqs($moduleID);
    for($i=0; $i<count($savedPrereqs); $i++) {
        $savedPrereqs[$i] = $safeJSString=preg_replace('/"/', '\"', $savedPrereqs[$i]["text"]);
    }
    $smarty->assign("savedPrereqs", $savedPrereqs);
            
    $savedCategories=getModuleCategoryIDs($moduleID);
    for($i=0; $i<count($savedCategories); $i++) {
        $category=getCategoryByID($savedCategories[$i]);
        $savedCategories[$i] = $category["ID"];
    }
    $smarty->assign("savedCategories", $savedCategories);
    
    // Note that references have two values each that need to be sent 
    // (description and link). These are combined into one string, separated
    // by '$$$delim$$$' and our later separated by JavaScript code.
    
    if(in_array("CrossReferenceModulesInternal", $backendCapabilities["read"]) && in_array("CrossReferenceModulesInternal", $backendCapabilities["write"])) {
        $savedIReferences=getInternalReferences($moduleID);
        for($i=0; $i<count($savedIReferences); $i++) {
            $safeJSString1=preg_replace('/"/', '\"', $savedIReferences[$i]["description"]);            
            $safeJSString2=preg_replace('/"/', '\"', $savedIReferences[$i]["referencedModuleID"]);
            $savedIReferences[$i] = $safeJSString1 . '$$$delim$$$' . $safeJSString2;
        }
        $smarty->assign("savedIRefs", $savedIReferences);
    }
    if(in_array("CrossReferenceModulesExternal", $backendCapabilities["read"]) && in_array("CrossReferenceModulesExternal", $backendCapabilities["write"])) {
        $savedEReferences=getExternalReferences($moduleID);
        for($i=0; $i<count($savedEReferences); $i++) {
            $safeJSString1=preg_replace('/"/', '\"', $savedEReferences[$i]["description"]);
            $safeJSString2=preg_replace('/"/', '\"', $savedEReferences[$i]["link"]);
            $savedEReferences[$i] = $safeJSString1 . '$$$delim$$$' . $safeJSString2;
        }
        $smarty->assign("savedERefs", $savedEReferences);
    }    
  }

  // 'moduleAction' handles 'createNewVersion', 'edit', and 'create'
  // can also be set to error if something fails
  // if moduleAction isn't set, default to 'create'
  $moduleAction = "create";
  if ( isset($_REQUEST["moduleAction"]) ) {
    
    if ( isset($_REQUEST["moduleID"]) ) {
        $moduleID=$_REQUEST["moduleID"];
        $moduleInfo=getModuleByID($moduleID);        
    } else {
        $moduleInfo = FALSE;
    }
    
    // check for valid module action, else throw error
    if ($_REQUEST["moduleAction"] == "create" || $_REQUEST["moduleAction"] == "createNewVersion" || 
        $_REQUEST["moduleAction"] == "edit")
    {
        $moduleAction = $_REQUEST["moduleAction"];
        // check if edit or createNewVersion came with no moduleID (which is necessary)
        if ( ($moduleAction=="edit" || $moduleAction=="createNewVersion")
              && !isset($moduleInfo["moduleID"]) )
        {
            $moduleAction = "error";
            if ($moduleInfo=="NotImplemented") {
                $smarty->assign("alert", array("type"=>"negative", 
                        "message"=>"Sorry, the backend does not support creating/editing modules.") );
            } else {
                $smarty->assign("alert", array("type"=>"negative", 
                        "message"=>"Error retrieving specified module. 
                        If this error persists, contact the collection manager.") );
            }            
        }
    } else {
      $moduleAction = "error";
      $smarty->assign("alert", array("type"=>"negative", 
                        "message"=>"Unknown action specified. 
                        Be sure to only use provided links to this page. 
                        If this error persists, contact the collection manager.") );
    }        
  }
  $smarty->assign("moduleAction", $moduleAction);  
              
  
  
  // 'action'  controls saving and deleting, and jumping between steps
  // if none of these two are requested, leave action empty ("")
  $action = "";
  if ( isset($_REQUEST["action"]) ) {    
    if ( $_REQUEST["action"] == "save" || $_REQUEST["action"] == "Save" ) {
        $action = "save";
    } elseif ( $_REQUEST["action"] == "delete" || $_REQUEST["action"] == "Delete" ) {
        $action = "delete";
    } else {
        $action = $_REQUEST["action"];        
    }
  }
  $smarty->assign("action", $action);
  
  
  $NUMBER_STEPS = 4; // total number of steps in the module wizard
  $smarty->assign("NUMBER_STEPS", $NUMBER_STEPS);  
  
  // if 'step' request variable is set and valid, use that step
  // otherwise use first step
  $step = 1;
  if ( isset($_REQUEST["step"]) 
       && $_REQUEST["step"] > 0 && $_REQUEST["step"] <= $NUMBER_STEPS )
  {    
    $step = $_REQUEST["step"];
  }
  //  "create" and "createNewVersion" are only handled by the first step, 
  //  then the new module is created and action is changed to edit for remaining steps
  if ($moduleAction == "create" || $moduleAction == "createNewVersion") {
    $step = 1;
  }
  // if the REQUEST variables 'next' or 'back' are set, change step appropriately 
  if ( isset($_REQUEST["next"]) && ($step + 1) <= $NUMBER_STEPS ) {
    $step++;
    $action = "save"; // save before going to next/previous page
  } elseif ( isset($_REQUEST["back"]) && ($step - 1) > 0 ) {
    $step--;
    $action = "save"; // save before going to next/previous page
  }
  // change step if action was set to jump between steps
  // then change action to save so changes are saved before changing steps
  if ( $action == "Basic Info" ) {
    $step = 1;
    $action = "save"; // this is so module is saved before making the jump
  } elseif ( $action == "Materials" ) {
    $step = 2;
    $action = "save"; // this is so module is saved before making the jump
  } elseif ( $action == "References" ) {
    $step = 3;
    $action = "save"; // this is so module is saved before making the jump
  } elseif ( $action == "Submit" ) {
    $step = 4;
    $action = "save"; // this is so module is saved before making the jump
  }
  $smarty->assign("step", $step);
  $smarty->assign("action", $action);
  
  
  // 'hasPermission' determines whether the user has permission to perform this action
  $hasPermission = false;
  if ( isset($userInformation) ) {
    $type = $userInformation["type"];
    // user must be logged in and have sufficient privileges
    if ($type=="Submitter" || $type=="Editor" || $type=="Admin") {
        // if edit or createNewVersion, check to make sure user isn't submitter (who can't edit)
        // and check to make sure this user is the one who originally submitted
        if ( ($moduleAction == "createNewVersion" || $moduleAction == "edit")
            && $type!="Submitter" && isset($moduleInfo["submitterUserID"]) 
            && $moduleInfo["submitterUserID"]==$userInformation["userID"] )
        {
            $hasPermission = true;
        } elseif ($moduleAction == "create") {
            $hasPermission = true;
        } elseif ($action == "save") {
            $hasPermission = true;        
        } elseif ($moduleAction != "error") {
            $smarty->assign("alert", array("type"=>"negative", 
                "message"=>"Sorry, you don't have permission to edit this module!") );
        }
    } else {
        $smarty->assign("alert", array("type"=>"negative", 
                "message"=>"Sorry, you don't have permissions to create/edit modules!") );
    }
  } else {
    $smarty->assign("alert", array("type"=>"negative", 
                "message"=>"Sorry, you must be logged in to create/edit modules!") );
  }
  $smarty->assign("hasPermission", $hasPermission);
  
  
  // if user has permission, and no error was found, continue
  if ($hasPermission == true && $moduleAction != "error")
  {
    // step 2 is the materials step, so materials need to be retrieved
    if ($step == 2) {
        $materials = array();
        $allModuleMaterials=getAllMaterialsAttatchedToModule($moduleInfo["moduleID"]);
        if($allModuleMaterials===FALSE) {
            $smarty->assign("alert", array("type"=>"negative",
                "message"=>"Error retrieving this module's materials!") );
        } else {
            for($i=0; $i<count($allModuleMaterials); $i++) {
                $materials[$i]=getMaterialByID($allModuleMaterials[$i]);                
            }
        }
        $smarty->assign("materials", $materials);
    }
    
    if ($action == "save") {
        // first check to see if module needs to be created/create new version
        // if not, save changes to existing module
        if($moduleAction=="create") {
         
          // the following is mostly creation code from original EdRepo
            // Gets all enetered data, valid or not, and stores it.
            $resourceTitle = $_REQUEST["moduleTitle"];      // Required length 3
            $abstract=$_REQUEST["moduleAbstract"];          // Must not be blank
            $lectureSize=$_REQUEST["moduleLectureSize"];    // Optional
            $labSize=$_REQUEST["moduleLabSize"];            // Optional
            $exerciseSize=$_REQUEST["moduleExerciseSize"];  // Optional
            $homeworkSize=$_REQUEST["moduleHomeworkSize"];  // Optional
            $otherSize=$_REQUEST["moduleOtherSize"];        // Optional
          
          // Check to see if title is valid //  
          if(validateFieldLength( $resourceTitle, 2)) {
            $smarty->assign("alert", array("type"=>"negative", 
                "message"=>"Error: No module title given. The module could not be created because its name was not specified.") );
            $smarty->assign("step", 1);
          } 
          // Check to see if abstract is valid //
          else if(validateFieldLength( $abstract, 0 )){ 
            $smarty->assign("alert", array("type"=>"negative", 
                "message"=>"Error: No abstract given. The module could not be created because its abstract was not specified.") );
            $smarty->assign("step",1);
          }
          else {
            $moduleID=createModule($_REQUEST["moduleTitle"], $abstract, $lectureSize, $labSize, $exerciseSize, $homeworkSize, $otherSize, "", "InProgress", "", $userInformation["userID"], "");
            if($moduleID===FALSE) {
              $smarty->assign("alert", array("type"=>"negative", 
                "message"=>"An unknown error occurred while attempting to create the module.  This is most likely a back-end problem.") );
              $smarty->assign("step", 1);
            } else {
              $moduleInfo=getModuleByID($moduleID);
              $userAuthor=$userInformation["firstName"]." ".$userInformation["lastName"];
              $result=setModuleAuthors($moduleInfo["moduleID"], array($userAuthor)); //By default, set the module author to the currently logged in user (the creator).
              $customWarning=$result;
              $moduleAction="edit";
              $smarty->assign("moduleID", $moduleID);
              $smarty->assign("moduleAction", "edit");
              $smarty->assign("pageName", "Module Wizard - Editing \"".$moduleInfo["title"]."\"");
              // once new module is created, save all possible data for it so that fields like authors get saved
                if (saveAllPossible($_REQUEST, $userInformation, $moduleInfo)===TRUE) {
                  $smarty->assign("alert", array("type"=>"positive", 
                        "message"=>"Module successfully created and saved.") );
                } else {
                    $smarty->assign("alert", array("type"=>"negative", 
                        "message"=>"Unable to create new module.") );
                }
              $smarty->assign("moduleInfo", $moduleInfo);
              refreshFields($smarty, $moduleInfo["moduleID"]);
            }
          }
          
        } elseif($moduleAction=="createNewVersion") {
              $originalModuleID=$moduleInfo["moduleID"];
                $abstract = $moduleInfo["abstract"];
                $lectureSize = $moduleInfo["lectureSize"];
                $labSize = $moduleInfo["labSize"];
                $exerciseSize = $moduleInfo["exerciseSize"];
                $homeworkSize = $moduleInfo["homeworkSize"];
                $otherSize = $moduleInfo["otherSize"];
                if(isset($_REQUEST["moduleAbstract"])) {
                  $abstract=$_REQUEST["moduleAbstract"];
                }
                if(isset($_REQUEST["moduleLectureSize"])) {
                  $lectureSize=$_REQUEST["moduleLectureSize"];
                }
                if(isset($_REQUEST["moduleLabSize"])) {
                  $labSize=$_REQUEST["moduleLabSize"];
                }
                if(isset($_REQUEST["moduleExerciseSize"])) {
                  $exerciseSize=$_REQUEST["moduleExerciseSize"];
                }
                if(isset($_REQUEST["moduleHomeworkSize"])) {
                  $homeworkSize=$_REQUEST["moduleHomeworkSize"];
                }
                if(isset($_REQUEST["moduleOtherSize"])) {
                  $otherSize=$_REQUEST["moduleOtherSize"];
                }                    
              $moduleID=editModuleByID($moduleID, $abstract, $lectureSize, $labSize, $exerciseSize, $homeworkSize, $otherSize, $moduleInfo["authorComments"], $moduleInfo["checkInComments"], $userInformation["userID"], "InProgress", $moduleInfo["minimumUserType"], TRUE);
              $moduleInfo=getModuleByID($moduleID); //Refresh module information with the newly created version (probably only the version changed).
              /* Copy the authors from the old module version into the new module version. */
              $old=getModuleAuthors($originalModuleID);
              $result=setModuleAuthors($moduleInfo["moduleID"], $old);
              /* The next several lines copy topics, prereqs, objectives, categories, materials, and internal/external references from the old module 
                to the new version.  However, they'll only be copied if the backend says it supports the feature being copied. */
              $old=getModuleTopics($originalModuleID);
              $result=setModuleTopics($moduleInfo["moduleID"], $old);
              $old=getModulePrereqs($originalModuleID);
              $result=setModulePrereqs($moduleInfo["moduleID"], $old);
              $old=getModuleObjectives($originalModuleID);
              $result=setModuleObjectives($moduleInfo["moduleID"], $old);
              if(in_array("UseCategories", $backendCapabilities["read"]) && in_array("UseCategories", $backendCapabilities["write"])) {
                $old=getModuleCategoryIDs($originalModuleID);
                $result=setModuleCategories($moduleInfo["moduleID"], $old);
              }
              if(in_array("UseMaterials", $backendCapabilities["read"]) && in_array("UseMaterials", $backendCapabilities["write"])) {
                $old=getAllMaterialsAttatchedToModule($moduleInfo["moduleID"]);
                for($i=0; $i<count($old); $i++) {
                  $m=getMaterialByID($old[$i]);
                  $materialID=createMaterial($m["linkToMaterial"], $m["linkType"], $m["readableFileName"], $m["type"], $m["title"], $m["rights"], $m["language"], $m["publisher"], $m["description"], $m["creator"]);
                  if($materialID!==FALSE && $materialID!=="NotImplimented") {
                    $result=attatchMaterialToModule($materialID, $moduleInfo["moduleID"]);
                  }
                }
              }
              if(in_array("CrossReferenceModulesInternal", $backendCapabilities["read"]) && in_array("CrossReferenceModulesInternal", $backendCapabilities["write"])) {
                $old=getInternalReferences($moduleInfo["moduleID"]);
                $result=setInternalReferences($moduleInfo["moduleID"], $old);
              }
              if(in_array("CrossReferenceModulesExternal", $backendCapabilities["read"]) && in_array("CrossReferenceModulesExternal", $backendCapabilities["write"])) {
                $old=getExternalReferences($moduleInfo["moduleID"]);
                $result=setExternalReferences($moduleInfo["moduleID"], $old);
              }
              /* End copying topics/prereqs/categories/objectives/materials/internal refs/external refs into new version. */
              $moduleAction="edit"; //Set the moduleAction to edit, since we want to edit the newly created version.
              
              $smarty->assign("moduleID", $moduleID);
              $smarty->assign("moduleAction", "edit");
              $smarty->assign("pageName", "Module Wizard - Editing \"".$moduleInfo["title"]."\"");
              // once new module is created, save all possible data for it so that fields like authors get saved
                if (saveAllPossible($_REQUEST, $userInformation, $moduleInfo)===TRUE) {
                  $smarty->assign("alert", array("type"=>"positive", 
                        "message"=>"New version created and saved.") );
                } else {
                    $smarty->assign("alert", array("type"=>"negative", 
                        "message"=>"Unable to create new version.") );
                }
              $smarty->assign("moduleInfo", $moduleInfo);
              refreshFields($smarty, $moduleInfo["moduleID"]);      
        } else {
        
            // exectue save function
            if (saveAllPossible($_REQUEST, $userInformation, $moduleInfo)===TRUE) {
                $smarty->assign("alert", array("type"=>"positive", 
                    "message"=>"Module saved.") );
            } else {
                $smarty->assign("alert", array("type"=>"negative", 
                    "message"=>"Unable to save module progress.") );
            }
            
            // refresh module information
            $moduleInfo=getModuleByID($moduleID);
            
            // after saving, continue editing
            $smarty->assign("moduleAction", "edit");
            $smarty->assign("pageName", "Module Wizard - Editing \"".$moduleInfo["title"]."\"");
            $smarty->assign("moduleInfo", $moduleInfo);
            refreshFields($smarty, $moduleInfo["moduleID"]);
        }
    } elseif ($action == "Submit for Moderation" || $action == "Publish") {
        
        if (submitModule($_REQUEST, $userInformation, $moduleInfo, $_REQUEST["moduleCheckInComments"], $NEW_MODULES_REQUIRE_MODERATION)===TRUE) {
            $smarty->assign("alert", array("type"=>"positive", 
                "message"=>"Module sucessfully submitted for moderation.") );
        } else {
            $smarty->assign("alert", array("type"=>"negative", 
                "message"=>"Unable to save/submit module.") );
        }
        
        // refresh module information
        $moduleInfo=getModuleByID($moduleID);
        
        // after saving, continue editing
        $smarty->assign("moduleAction", "edit");
        $smarty->assign("pageName", "Module Wizard - Editing \"".$moduleInfo["title"]."\"");
        $smarty->assign("moduleInfo", $moduleInfo);
        refreshFields($smarty, $moduleInfo["moduleID"]);
            
    } elseif ($action == "delete") {
        header('Location: delete.php?moduleID='.$moduleID.'&action=delete');
  
    } elseif ($moduleAction == "create") {
        $smarty->assign("pageName", "Module Wizard - Create New Module");
    } elseif ($moduleAction == "createNewVersion") {
        $smarty->assign("pageName", "Module Wizard - Create New Version of \"".$moduleInfo["title"]."\"");
        $smarty->assign("moduleInfo", $moduleInfo);
        refreshFields($smarty, $moduleInfo["moduleID"]);
    } elseif ($moduleAction == "edit") {
        $smarty->assign("pageName", "Module Wizard - Editing \"".$moduleInfo["title"]."\"");
        $smarty->assign("moduleInfo", $moduleInfo);
        refreshFields($smarty, $moduleInfo["moduleID"]);
    }
  
  }
  
  
  
  $smarty->display('moduleWizard.tpl');                  
?>

