<?php session_start();
/****************************************************************************************************************************
 *    showMyModules.php - Displays a user's modules.
 *    --------------------------------------------------------------------------------------
 *  Display's modules owened by a user.  This page will automatically find modules owned by the logged in user and display them,
 *  along with options to edit/continue work on modules, and searching modules owned by the user.
 *
 *  Version: 1.0
 *  Author: Ethan Greer
 *  Modified by: Jon Thompson (5/20/2011 - implemented new interface/Smarty)
 *
 *  Notes: (none) 
 ******************************************************************************************************************************/
  
  require("lib/config.php");

  $smarty->assign("title", $COLLECTION_NAME . ' - My Resources');
    // title of this page. For most pages: &COLLECTION . " - Title" , default: $COLLECTION_NAME
  $smarty->assign("tab", "modules"); // active nav tab. default:  "home"
  $smarty->assign("baseDir", getBaseDir() ); // should always be getBaseDir() 
  
  $smarty->assign("pageName", "My Modules");
    // name of page to placed in <h1> tag
  
  $page=1; //The "page" we are on (default, 1 (first)
  $recordsPerPage=15; //The number of records to display per page (default, 15)
  if(isset($_REQUEST["page"])) {
    $page=$_REQUEST["page"];
  }
  if(isset($_REQUEST["recordsPerPage"])) {
    $recordsPerPage=$_REQUEST["recordsPerPage"];
  }
  $smarty->assign("recordsPerPage", $recordsPerPage);
  
  $action="display";
  $wasFiltered=FALSE; //This determines if the modules fetched were filtered or not, for a nicer display if nothing was found.
  if(isset($_REQUEST["action"])) {
    $action=$_REQUEST["action"];
  }
  $smarty->assign("action", $action);
  
  $loggedIn = false;
  $backendCapable = false;
  
  if(isset($userInformation)) { //Only do any filtering/etc. if we're logged in,
    $loggedIn = true;
    if($action=="filter" && isset($_REQUEST["filterText"])) { //If we are suppose to filter the results, do so here (but only if we have enough information to filter with).  Build a list of modules owned by this user, but only with the filtered titles.
      $modules=searchModules(array("userID"=>$userInformation["userID"], "title"=>$_REQUEST["filterText"]));
      $wasFiltered=TRUE;
      $action="display"; //Tell future parts of the program to display what we just got.
    } else { //No filter was specified, so build a list of all modules owned by this user.
      $modules=searchModules(array("userID"=>$userInformation["userID"])); //Get a list of all modules which the user owns.
      $action="display"; //Tell future parts of the program to display what we just got.
    }
  }

  if (in_array("UseModules", $backendCapabilities["read"]) && in_array("SearchModulesByUserID", $backendCapabilities["read"]) ) {
    $backendCapable = true;
  }
  
  $smarty->assign("loggedIn", $loggedIn);
  $smarty->assign("backendCapable", $backendCapable);
  $smarty->assign("wasFiltered", $wasFiltered);
  
if($loggedIn === true && $backendCapable === true) {
  
  if($wasFiltered===TRUE) { //The user had a filter, so be nice and automatically place that in the filter bar.
    $smarty->assign("filterText", preg_replace('/"/', '&quot;', $_REQUEST["filterText"]) );
  } else { //The user didn't have a filter, so display default text in the filter view.
    $smarty->assign("filterText", "");
  }
  
  if($action=="display") {
    //We'll use the $modules list of modules to display built earlier
    $smarty->assign("modules", $modules);
    
    $lowerLimit=$recordsPerPage*($page-1); //The lowest index in the $records array which will be printed (based on $page and $recordsPerPage
    $upperLimit=$lowerLimit+$recordsPerPage; //The highest index in the $records array which will be printed (based on $page and $recordsPePage
    /* It is possible that records were found but the page/recordsPerPage combination is beyond the number of records (meaning no records would be displayed).  If this is true,
      decrease the page until it is small enough to show some results. */
    while(count($modules)<$lowerLimit) {
      $page=$page-1;
      $lowerLimit=$recordsPerPage*($page-1); //Calculate new lowerLimit based on new page.
      $upperLimit=$lowerLimit+$recordsPerPage; //Calculate new upperLimit based on new page.
    }
    
    $smarty->assign("page", $page);
    $smarty->assign("lowerLimit", $lowerLimit);
    $smarty->assign("upperLimit", $upperLimit);
    
    // Calculate number of pages needed by dividing Number of records by Records per page and rounding up with ceil()
    $numPages = ceil(count($modules)/$recordsPerPage); 
    $smarty->assign("numPages", $numPages);
  }
}

  $smarty->display('showMyModules.php.tpl');
?>