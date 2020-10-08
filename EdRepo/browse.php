<?php session_start();
/****************************************************************************************************************************
 *    browse.php - Provides methods to browse the collection.
 *    -------------------------------------------------------
 *  Allows browsing the collection (searching with set criteria) by various parameters.
 *
 *  Version: 1.0
 *  Author: Ethan Greer
 *
 *  Modified: 2011-05-02 by Jon Thompson (implementing new interface)
 *
 *  Notes: - This page can take the following GET/POST parameters:
 *            browseBy : What to limit browsed results by.  May be any valid parameter for searchModules, but is suggested to 
 *              use only "titleStartsWith" (for browsing by title) or "category" (for browsing by category).
 *              Default is "titleStartsWith".  Note that what is specified here will affect valid values for the parm parameter.
 *            page : If more than recordsPerPage results are found, than they are displayed in multiple pages, with each page
 *              containing up to resultsPerPage number of records.  The page parameter determines which page to display.  It will
 *              be automatically decreased to the largest page with records on it.  Default is 1 (the fitst page).  Only positive
 *              numbers should be passed to this parameter (0 or negative numbers will result in no records being displayed, regardless
 *              of the value of this parameter or how many records actually match the criteria given with browseBy and/or parm).
 *            parm : The parameter to search whatever is given in browseBy with.  Default is "A".
 *              For a browseBy of "titleStartsWith", parm may be anything (but is suggested to be a single letter/number/symbol).
 *              For a browseBy of "category", parm should be the categoryID to browse by, and should be one of the IDs returned by
 *                a call to getAllCategories()
 *            recordsPerPage : The number of results to show per page.  Default is 15.
 ******************************************************************************************************************************/
  
  require("lib/config.php");

  $smarty->assign("title", $COLLECTION_NAME . ' - Browse');
    // title of this page. For most pages: &COLLECTION . " - Title" , default: $COLLECTION_NAME
  $smarty->assign("tab", "browse"); // active nav tab. default:  "home"
  $smarty->assign("baseDir", getBaseDir() ); // should always be getBaseDir() 
  
  $smarty->assign("pageName", "Browse " . $COLLECTION_NAME);
    // name of page to placed in <h1> tag
  
  
  /* Set default and (if possible) override default values for browsing parameters, then build a list of records to 
    display on the page. */
  $browseBy="category"; //The field to browse by.  This must be a valid parameter to pass to searchModules() (default, "titleStartsWith") (new default, 'category')
  $parm="*"; //The parameter to search the field given in $browseBy with (default, "A") (new default, '*')
  $page=1; //The "page" we are on (default, 1 (first)
  $recordsPerPage=15; //The number of records to display per page (default, 15)
  
  if(isset($_REQUEST["browseBy"])) {
    $browseBy=$_REQUEST["browseBy"];
  }
  if(isset($_REQUEST["parm"])) {
    $parm=$_REQUEST["parm"];
  }
  if(isset($_REQUEST["page"])) {
    $page=$_REQUEST["page"];
  }
  if(isset($_REQUEST["recordsPerPage"])) {
    $recordsPerPage=$_REQUEST["recordsPerPage"];
  }
  
  $smarty->assign("browseBy", $browseBy);
  $smarty->assign("parm", htmlspecialchars($parm) );
  // $page is assigned to smarty later after being validated
  $smarty->assign("recordsPerPage", $recordsPerPage);
  
  $records=searchModules(array($browseBy=>$parm, "status"=>"Active")); //Get all records matching the query, but only records which are Active in the collection.
  $smarty->assign("records", $records);

if($records===FALSE || $records=="NotImplimented") { //Did searching for records return an error or a "NotImplimented"?
  $smarty->assign("moduleError", "true");
} else { //This else block runs if searching for records to browse by did not return an error.
  $smarty->assign("moduleError", "false");
  
  /* Load alphabet in array to print out a strip giving browse options.  Should look similar to this:
          Browse Modules Alphabetically: A | B | C | D .... X | Y | Z  or Browser Modules By Category <Category List><Submit Button> */
  //We need a form if we're going to allow browsing by categories.  Of course, we might not allow that, depending on if the back-end supports it or not.
  //However, creating a form also creates a new line, so to keep both browse options needing a form and those not needing one on the same line, open the
  //form here.  If no browse options end up being given which require a form, than they'll be no form elements in the form, but that won't hurt anything.
  $alphabet=array("A", "B", "C", "D", "E", "F", "G", "H", "I",
                  "J", "K", "L", "M", "N", "O", "P", "Q", "R",
                  "S", "T", "U", "V", "W", "X", "Y", "Z");
  $smarty->assign("alphabet", $alphabet);
  
  //If the backend supports using categories, also give options to browse by category.
  if(in_array("UseCategories", $backendCapabilities["read"])) {
    $smarty->assign("useCategories", "true");
    $categories=getAllCategories(); //Get a list of all categories.
    $smarty->assign("categories", $categories);
  } else {
    $smarty->assign("useCategories", "false");
  }

  /* Assign total number of records to smarty */
  $smarty->assign("numRecords", count($records) );
  
  if(count($records)>0) { //At least one record was found.
    
    $lowerLimit=$recordsPerPage*($page-1); //The lowest index in the $records array which will be printed (based on $page and $recordsPerPage
    $upperLimit=$lowerLimit+$recordsPerPage; //The highest index in the $records array which will be printed (based on $page and $recordsPePage
    /* It is possible that records were found but the page/recordsPerPage combination is beyond the number of records (meaning no records would be displayed).  If this is true,
      decrease the page until it is small enough to show some results. */
    while(count($records)<$lowerLimit) {
      $page=$page-1;
      $lowerLimit=$recordsPerPage*($page-1); //Calculate new lowerLimit based on new page.
      $upperLimit=$lowerLimit+$recordsPerPage; //Calculate new upperLimit based on new page.
    }
    
    $smarty->assign("page", $page);
    $smarty->assign("lowerLimit", $lowerLimit);
    $smarty->assign("upperLimit", $upperLimit);
    
    
    $recordCategories = array($lowerLimit=>""); // create empty array for string of categories for each module
    //Loop through records, starting at the lowest index and continuing as long as $i doesn't grow beyong the length of $records and doesn't exceed the upperLimit.
    for($i=$lowerLimit; ($i<$upperLimit && $i<count($records))  ; $i++) {
        $categories=getModuleCategoryIDs($records[$i]["moduleID"]);
        if($categories!==FALSE) {
          $myCategories = ""; // empty string to store module [i]' s categories
          for($j=0; $j<count($categories); $j++) {
            $category=getCategoryByID($categories[$j]);
            $myCategories .= $category["name"].' '; // add category to this module's string
          }
          
          $recordCategories[$i] = $myCategories; // add module [i]'s string of categories to array of all modules' categories
        } else { //Error getting categories for module.
          $recordCategories[$i] = 'Error getting module categories.  ';
        }
    }
    $smarty->assign("recordCategories", $recordCategories);
    
    
    
    // Calculate number of pages needed by dividing Number of records by Records per page and rounding up with ceil()
    $numPages = ceil(count($records)/$recordsPerPage); 
    $smarty->assign("numPages", $numPages);
    
  }
}
  $smarty->display('browse.php.tpl');
?>