<?php session_start();
/****************************************************************************************************************************
 *    viewModule.php - Displays the contents of a module.
 *    --------------------------------------------------------------------------------------
 *  Displays the contents of a module (metadata, materials, material metadata, etc).
 *
 *  Version: 1.0
 *  Author: Ethan Greer
 *	Edited by: Andrew Hagner 2/12/2011
 *			- Made Module Ranking and Material Ranking labels distinguishable 
 *                      Jon Thompson 5/18/2011
 *                        - Converted to new interface and to use Smarty templates
 *  Notes: - This page can take the following GET or POST parameters:
 *          forceView: If set to "true", will attempt to view modules, even if they are not active in the collection.  Only Editors,
 *                     Admins, and the module's creator may force view modules.
 *          moduleID: The ID of the module to view.  Required, or the page will prompt for an ID.
 ******************************************************************************************************************************/
  
  require("lib/config.php");

  $smarty->assign("title", $COLLECTION_NAME . " - Browse - View Resource");
    // title of this page. For most pages: &COLLECTION . " - Title" , default: $COLLECTION_NAME
  $smarty->assign("tab", "browse"); // active nav tab. default:  "home"
  $smarty->assign("baseDir", getBaseDir() ); // should always be getBaseDir() 
  
  $smarty->assign("pageName", "View Resource");
  
  $smarty->assign("alert", array("type"=>"", "message"=>"") );
                  // default empty alert message (type can be either positive or negative)

  
  function userCanViewModule($userType, $minType) {
    if($minType=="Unregistered" || $userType=="Admin") { //Everyone can view modules if the minimum level is Unregistered, and Admins can view every module.
      return TRUE;
    }
    if($userType=="Disabled" || $userType=="Deleted" || $userType=="Pending") { //Disabled, deleted, and pending users can not view any modules.
      return FALSE;
    }
    if($userType=="Viewer" && $minType=="Viewer") {
      return TRUE;
    }
    if($userType=="SuperViewer" && ($minType=="Viewer" || $minType=="SuperViewer")) {
      return TRUE;
    }
    if($userType=="Submitter" && ($minType=="Viewer" || $minType=="SuperViewer" || $minType=="Submitter")) {
      return TRUE;
    }
    if($userType=="Editor" && ($minType=="Viewer" || $minType=="SuperViewer" || $minType="Submitter" || $minType=="Editor")) {
      return TRUE;
    }
    return FALSE;
  }
  

if(isset($_REQUEST["moduleID"])) { //Did we get a module ID?
  /* Gather information about this module (including materials, categories, prereqs, etc). */
  $module=getModuleByID($_REQUEST["moduleID"]);
  if($module===FALSE || count($module)<=0) { //If the backend reported an error getting the module, or returned an empty array, assume the module doesn't exist.
    $smarty->assign("noModule", true);
  } else { //This else block runs is a module with the specified ID was found.  It determines if the user may view the module, and if they can, displays it.
    $smarty->assign("noModule", false);
    $smarty->assign("module", $module);
    
    if(in_array("UseMaterials", $backendCapabilities["read"])) { //Does the back-end support reading modules?
      $materials=getAllMaterialsAttatchedToModule($module["moduleID"]);
    } else { //This else blcok runs if the back-end doesn't support reading materials.
      $materials=FALSE;
    }
    $prereqs=getModulePrereqs($module["moduleID"]);
    $topics=getModuleTopics($module["moduleID"]);
    $objectives=getModuleObjectives($module["moduleID"]);
    $authors=getModuleAuthors($module["moduleID"]);
    if(in_array("UseCategories", $backendCapabilities["read"])) { //Only get categories if the back-end supports reading module categories.
      $categories=getModuleCategoryIDs($module["moduleID"]); //Grab all category IDs which are attatched to the module.
    } else { //This else block runs if the back-end doesn't support categories.
      $categories=FALSE; //Set the category to FALSE to alert later code not to display any category.
    }
    /* Done gathering module/meterial/etc information. */
    
    /* To determine if the user can view the module, first create a variable $canViewModule which will be FALSE if the user can't view 
      the module, and TRUE if they can.  Then, perform two checks to see if the user can view the module:  (1) check if the user
      is not logged in, and if they aren't, then check if the module can be viewed by unregistered users.  (2) If the user is
      logged in, check to see if they can view the module, based on their pirvilege level.
    The reason for this cumbersome two-part check (with the first check actually being two parts) is because if the user is not
      logged in, that the $userInformation variable will not exist.  However, this variable is needed to check if a user has sufficient
      privileges to view the module, so to avoid a "variable does nto exist" error, we must check if $userInformation does NOT exist and
      handle that, and only interact with the variable if we determine it really does exist. */
    $canViewModule=FALSE; //Set to true if we determine the user can view the module.
    if(!isset($userInformation)) {
      if($module["minimumUserType"]=="Unregistered") {
        $canViewModule=TRUE;
      }
    } elseif(userCanViewModule($userInformation["type"], $module["minimumUserType"]===TRUE)) {
      $canViewModule=TRUE;
    }
    if($module["status"]=="InProgress" || $module["status"]=="PendingModeration") { //If the module exists, check to see if its status is "InProgress" or "PendingModeration".  These modules can't normally be viewed, unless overridden.
      /*Check to see if the user is logged in and if they have requested a force override to view InProgress modules.  If they have, 
        check to make sure the user is an Editor or Admin or is the owner of the module and if they are one of these, allow the force
        over-ride.  Otherwise, deny it. */
      if(isset($userInformation) && isset($_REQUEST["forceView"]) && $_REQUEST["forceView"]=="true" && ($userInformation["type"]=="Editor" || $userInformation["type"]=="Admin" || $module["submitterUserID"]==$userInformation["userID"])) {
        $canViewModule=TRUE;
      } else {
        $canViewModule=FALSE;
      }
    }
    
    $smarty->assign("canViewModule", $canViewModule);
    
    if($canViewModule===TRUE) { //Did we determine that the user can view the module?
      $smarty->assign("pageName", "View Module '".$module["title"]."'");
      
      $smarty->assign("NEW_MODULES_REQUIRE_MODERATION", $NEW_MODULES_REQUIRE_MODERATION);
      
      $smarty->assign("showAuthors", false);
      if($authors!==FALSE && $authors!=="NotImplimented" && count($authors)>=1) { //display found authors, but only if we successfully found some.
        $smarty->assign("showAuthors", true);
        $smarty->assign("authors", $authors);
      }
      
      
      $smarty->assign("showCategories", false);
      if($categories!=="NotImplimented" && $categories!==FALSE && count($categories)>=1) { //Only display a category if we earlier determined the back-end actually supported categories.
        $smarty->assign("showCategories", true);
        
        $categoryNames = array();        
        for($i=0; $i<count($categories); $i++) {
          $category=getCategoryByID($categories[$i]);
          $categoryNames[$i] = $category["name"];
        }
        $smarty->assign("categoryNames", $categoryNames);
      }
      
      $smarty->assign("readRatings", false);
      $smarty->assign("writeRatings", false);
      if(in_array("RateModules", $backendCapabilities["read"])) { //Show the module's rating, if the backend supports reading module ratings.
        $smarty->assign("readRatings", true);
        $ratings=getModuleRatings($module["moduleID"]);
        $smarty->assign("ratings", $ratings);
        
        if(in_array("RateModules", $backendCapabilities["write"])) { //Does the backend support writing module ratings?  If so, display a link to do so.
          $smarty->assign("writeRatings", true);
        }
      }
      
      /* Displaying the cells/rows for topics, objectives, and prereqs works like this:
        (1) Make sure there is at least one topic/objective/prereq to display.
        (2) If (1) is true, than create a row with the proper lable (Topics/Objectives/Prerequisites) and make it span n number of rows, 
          where n is the total number of topics/objectives/prereqs.  Also, on the same line, print the cell for the first
          topic/objective/prereq and end the row.
        (3) Print new rows and cells with the rest of the topics/objectives/prereqs, starting with the 2nd one (index [1]), since the 
          first was already printed. */
      $smarty->assign("topics", $topics);
      $smarty->assign("objectives", $objectives);
      $smarty->assign("prereqs", $prereqs);
      
      $smarty->assign("showMaterials", false);
      if($materials!==FALSE && $materials!=="NotImplimented") { //Only show materials if we previously determined the back-end actually supports materials.
        $smarty->assign("showMaterials", true);
        $smarty->assign("materials", $materials);
        if(count($materials)>0) { //This block runs if the module contains at least one material.
          $materialInfo[] = array();
          for($i=0; $i<count($materials); $i++) { //Loop through every material ID found, fetch the actual material, and display its metadata.
            
            $materialInfo[$i]=getMaterialByID($materials[$i]); //Get the actual information about the material.
            //The number of rows the left column of the material details table must span is dependent on if we can show ratings or not.  So, 
            //set the rowspan ($rowspan) to initially be the number to display if we can show ratings, and then decrease if if we can't 
            //show ratings (becuase of the backend) and hence won't show the "ratings" row.
            $materialInfo[$i]["rowspan"]=9;
            $readRateMaterials = true;
            if(!in_array("RateMaterials", $backendCapabilities["read"])) {
              $materialInfo[$i]["rowspan"]--;
              $readRateMaterials = false;
            }
            $smarty->assign("readRateMaterials", $readRateMaterials);
            
            if(in_array("RateMaterials", $backendCapabilities["read"])) { //Display the material's rating if the backend supports reading material ratings.
              $ratings=getMaterialRatingsAndComments($materialInfo[$i]["materialID"]);
              $materialInfo[$i]["numRatings"] = 0;
              if(count($ratings)>0) {
                $totalRating=0;
                $numRatings=0;
                for($j=0; $j<count($ratings); $j++) {
                  $totalRating=$totalRating+$ratings[$j]["rating"];
                  $numRatings++;
                }
                
                $materialInfo[$i]["averageRating"] = round(($totalRating/$numRatings),2);
                $materialInfo[$i]["numRatings"] = $numRatings;
              }
              
              $writeRateMaterials = false;
              if(in_array("RateMaterials", $backendCapabilities["write"])) { //If the backend supports writing ratings, give a link to rate the material.
                $writeRateMaterials = true;
              }
              $smarty->assign("writeRateMaterials", $writeRateMaterials);
            }
          }
          $smarty->assign("materialInfo", $materialInfo);
        } // end count(materials) if
      } // end 'backend supports' modules if
    } // end 'user can view' if
  }
} else { //This else block runs if no moduleID was given
  $smarty->assign("noModule", true);
}

  $smarty->display('viewModule.php.tpl');

?>