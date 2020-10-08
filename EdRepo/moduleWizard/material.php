<?php session_start();
/****************************************************************************************************************************
 *    material.php - main page for handling creating/editing materials
 *    --------------------------------------------------------------------------------------
 *    Main method for a user to add, edit, or delete a material
 *
 *  Version: 1.0
 *  Author: Jon Thompson 
 *  Date: 23 June 2011
 *
 *  Notes: Handles actions: add, doAdd, edit, doEdit, delete, and doDelete
 *              "edit" is not currently capable of saving changes (needs backend support)
 ******************************************************************************************************************************/
  
  require("../lib/config.php");
  require("../lib/moduleEditUploadHelpers.php");

  $smarty->assign("title", $COLLECTION_NAME . " - Material Management");
    // title of this page. For most pages: &COLLECTION . " - Title" , default: $COLLECTION_NAME
  $smarty->assign("tab", "modules"); // active nav tab. default:  "home"
  $smarty->assign("baseDir", getBaseDir() ); // should always be getBaseDir() 
  
  $smarty->assign("pageName", "Material Management");
  
  $smarty->assign("alert", array("type"=>"", "message"=>"") );
                  // default empty alert message (type can be either positive or negative)
                  
                  
  $action = "error";
  if ( isset($_REQUEST["moduleID"]) ) {
      $moduleID=$_REQUEST["moduleID"];
      $moduleInfo=getModuleByID($moduleID);        
  } else {
      $moduleInfo = FALSE;
  }
  // check if 'action' is set, and whether the module info is valid
  if ( isset($_REQUEST["action"]) && ($moduleInfo != FALSE && $moduleInfo != "NotImplemented") ) {
    $action = $_REQUEST["action"];
  
    if ($action == "add" || $action == "doAdd") {
        $smarty->assign("pageName", "Add Material");
    } else if ($action == "edit" || $action == "doEdit") {
        $smarty->assign("pageName", "Edit Material");
    } else if ($action == "doDelete" || $action == "delete") {
        $smarty->assign("pageName", "Delete Material");
    }
    
    // if it's any of these actions, we need a value material as well
    if ($action == "edit" || $action == "doEdit" || $action == "doDelete" || $action == "delete") {
        if ( isset($_REQUEST["materialID"]) ) {
            $materialID=$_REQUEST["materialID"];
            $materialInfo=getMaterialByID($materialID);        
        } else {
            $materialInfo = FALSE;
        }
        $smarty->assign("materialInfo", $materialInfo);  
        
        $allModuleMaterials=getAllMaterialsAttatchedToModule($moduleID);
        if ($allModuleMaterials==FALSE || !in_array($materialInfo["materialID"], $allModuleMaterials) ) {
            $smarty->assign("alert", array("type"=>"negative", "message"=>"This material doesn't belong to this module!") );
            $action = "error";
        }
        
        if ($materialInfo == FALSE) {
            $smarty->assign("alert", array("type"=>"negative", "message"=>"Not enough valid information to continue.") );
            $action = "error";
        }
    }
  } else {
    $smarty->assign("alert", array("type"=>"negative", "message"=>"Not enough valid information to continue.") );
  }
  $smarty->assign("action", $action);
  $smarty->assign("moduleInfo", $moduleInfo); 
  
  // 'hasPermission' determines whether the user has permission to perform this action
  $hasPermission = false;
  if ( isset($userInformation) ) {
    $type = $userInformation["type"];
    // user must be logged in and have sufficient privileges
    if ($type=="Submitter" || $type=="Editor" || $type=="Admin") {
        // user must own the module to add/modify materials
        if ( $moduleInfo["submitterUserID"]==$userInformation["userID"] )
        {
            $hasPermission = true;  
        } elseif ($action != "error") {
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
  if ($hasPermission && $action != "error") {
    
    if ($action == "doAdd") {
        // check all fields are set
        // if not, overwrite given fields to show user
        if ( !(isset($_REQUEST["moduleID"]) && isset($_REQUEST["materialType"]) && isset($_REQUEST["materialTitle"]) && isset($_REQUEST["materialRights"]) && isset($_REQUEST["materialLanguage"]) && isset($_REQUEST["materialPublisher"]) && isset($_REQUEST["materialDescription"]) && isset($_REQUEST["materialCreator"]) && isset($_REQUEST["materialSourceType"]) && (($_REQUEST["materialSourceType"]=="LocalFile" && isset($_FILES["materialFile"])) || ($_REQUEST["materialSourceType"]=="ExternalURL" && isset($_REQUEST["materialURL"])))) ) {
            $materialInfo = array();
            // overwrite given fields to show smarty
            if ( isset($_REQUEST["materialType"]) ) {
                $materialInfo["type"] = $_REQUEST["materialType"];
            }
            if ( isset($_REQUEST["materialTitle"]) ) {
                $materialInfo["title"] = $_REQUEST["materialTitle"];
            }
            if ( isset($_REQUEST["materialRights"]) ) {
                $materialInfo["rights"] = $_REQUEST["materialRights"];
            }
            if ( isset($_REQUEST["materialLanguage"]) ) {
                $materialInfo["language"] = $_REQUEST["materialLanguage"];
            }
            if ( isset($_REQUEST["materialPublisher"]) ) {
                $materialInfo["publisher"] = $_REQUEST["materialPublisher"];
            }
            if ( isset($_REQUEST["materialDescription"]) ) {
                $materialInfo["description"] = $_REQUEST["materialDescription"];
            }
            if ( isset($_REQUEST["materialCreator"]) ) {
                $materialInfo["creator"] = $_REQUEST["materialCreator"];
            }
            $smarty->assign("materialInfo", $materialInfo);
            $smarty->assign("alert", array("type"=>"negative", 
                "message"=>"Unable to add a material to this module.  
                            Some information necessary to add the material was missing.") );
        } else {
        // fields are set, so attempt to add material
            if($_REQUEST["materialSourceType"]=="LocalFile") { //Is the material type a file to store?
              $materialLink=storeMaterialLocally($_FILES["materialFile"], '..'.$MATERIAL_STORAGE_DIR); //Try to store the material file, and get a link to it.
              $readableFileName=$_FILES["materialFile"]["name"]; //Set the "human-readable" file name to save to be the name of the file uploaded.
            } else { //Run this block if the material source type isn't a file to upload (ie its a URL)
              $materialLink=$_REQUEST["materialURL"]; //Get the link (URL) from what was submitted.
              $readableFileName=""; //There is no "human-readable" file name for URLs.
            }
            if($materialLink===FALSE) { //Error storing material file?
              $smarty->assign("alert", array("type"=>"negative", 
                    "message"=>"<strong>Unable to upload material file.</strong><br />Check to ensure the file fits the minimum upload requirements (size, type, and virus-free) and try again.  If this problem persists, 
                                please contact the collection maintainer.") );
            } else {
              $materialID=createMaterial($materialLink, $_REQUEST["materialSourceType"], $readableFileName, $_REQUEST["materialType"], $_REQUEST["materialTitle"], $_REQUEST["materialRights"], $_REQUEST["materialLanguage"], $_REQUEST["materialPublisher"], $_REQUEST["materialDescription"], $_REQUEST["materialCreator"]); //Add the material to the database
              if($materialID===FALSE) { //Error adding material?
                $smarty->assign("alert", array("type"=>"negative", 
                    "message"=>"<strong>Unable to create material.</strong><br />
                                Please contact the collection maintainer to report this error.</p>") );
              } else {
                $result=attatchMaterialToModule($materialID, $moduleInfo["moduleID"]); //Attatch the material to the module
                if($result===FALSE) { //Error attatching material to module?
                  $smarty->assign("alert", array("type"=>"negative", 
                    "message"=>"<strong>Unable to attatch material to module.</strong><br />
                                Please contact the collection maintaier to report this error.") );
                } else { //Material successfully uploaded, added to database, and attatched to module!
                  //$smarty->assign("alert", array("type"=>"positive", "message"=>"Material successfully added.") ); 
                  // on success, redirect to materials step in wizard:
                  header( 'Location: index.php?moduleAction=edit&moduleID='.$moduleInfo["moduleID"].'&step=2' ) ;
                }
              }
            }  
        }
    } elseif ($action == "doDelete") {
      if(!isset($_REQUEST["materialID"])) { //Can't remove a material if we don't know the ID.
        $smarty->assign("alert", array("type"=>"negative", 
				"message"=>"<strong>The ID of the material to delete was not specified.  Unable to remove unknown material.</strong>") );
      } else {
        $result=deattatchMaterialFromModule($_REQUEST["materialID"], $moduleInfo["moduleID"]); //Deattatch the material from the module.
        if($result!==TRUE) {
          $smarty->assign("alert", array("type"=>"negative", 
				"message"=>"<strong>Error removing material (at deattatchMaterialFromModule).</strong>") );
        } else {
          $result=removeMaterialsByID(array($_REQUEST["materialID"]), $MATERIAL_STORAGE_DIR); //Actually remove the material.
          if($result!==TRUE) {
            $smarty->assign("alert", array("type"=>"negative", 
				"message"=>"<strong>Error removing material (at removeMaterialsByID).</strong>") );
          } else {
            //$smarty->assign("alert", array("type"=>"positive", "message"=>"Successfully removed material.") );
            // on success, redirect to materials step in wizard:
            header( 'Location: index.php?moduleAction=edit&moduleID='.$moduleInfo["moduleID"].'&step=2' ) ;
          }
        }
      }    
    } // end action if
  }
  
  
  $smarty->display("material.php.tpl");
?>
  
  
  