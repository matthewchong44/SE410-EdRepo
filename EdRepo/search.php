<?php session_start();
/****************************************************************************************************************************
 *    search.php - Provides methods to search the collection.
 *    -------------------------------------------------------
 *  Handles all front-end searching of the collection, including collecting search parameters, interfacing with the backend, and
 *  displaying search results.
 *
 *  Version: 1.0
 *  Author: Ethan Greer
 *  Modified by: Jon Thompson (5/20/2011 - implemented new interface/Smarty)
 *
 *  Notes: (none)
 ******************************************************************************************************************************/
  
  require("lib/config.php");

  $smarty->assign("title", $COLLECTION_NAME . ' - Search');
    // title of this page. For most pages: &COLLECTION . " - Title" , default: $COLLECTION_NAME
  $smarty->assign("tab", "home"); // active nav tab. default:  "home"
  $smarty->assign("baseDir", getBaseDir() ); // should always be getBaseDir() 
  
  $smarty->assign("pageName", "Search");
    // name of page to placed in <h1> tag
    
  $smarty->assign("alert", array("type"=>"", "message"=>"") );
  
  
  $byTitle = FALSE;
  $byAuthor = FALSE;
  $byCategory = FALSE;
  if(in_array("SearchModulesByTitle", $backendCapabilities["read"])) {
    $byTitle=TRUE;
  }
  if(in_array("SearchModulesByAuthor", $backendCapabilities["read"])) {
    echo '';
    $byAuthor=TRUE;
  }
  if(in_array("SearchModulesByCategory", $backendCapabilities["read"])) {
    $categories=getAllCategories();
    $smarty->assign("categories", $categories);
    $byCategory=TRUE;
  }
  
  $smarty->assign("byTitle", $byTitle);
  $smarty->assign("byAuthor", $byAuthor);
  $smarty->assign("byCategory", $byCategory);
  
  $showResults = false;
  
  $title="";
  $category="*";
  $author="";
  if(isset($_REQUEST["title"])) {
    $title=$_REQUEST["title"];
  }
  if(isset($_REQUEST["category"])) {
    $category=$_REQUEST["category"];
  }
  if(isset($_REQUEST["author"])) {
    $author=$_REQUEST["author"];
  }
  $smarty->assign("searchTitle", $title);
  $smarty->assign("searchCategory", $category);
  $smarty->assign("searchAuthor", $author);
  
if(isset($_REQUEST["title"]) || isset($_REQUEST["author"]) || isset($_REQUEST["category"]) ) { //This block runs if we have enough info to actually search.  So, search and display the results.
  $showResults = true;
  
  $records=searchModules(array("title"=>$title, "author"=>$author, "category"=>$category, "status"=>"Active"));
  $smarty->assign("records", $records);
  if(count($records)>0) {
    if(in_array("UseCategories", $backendCapabilities["read"])) {
      $smarty->assign("useCategories", true);
    } else {
      $smarty->assign("useCategories", false);
    }
    
    $recordCategories = array(); // create empty array for string of categories for each module
    //Loop through records, starting at the lowest index and continuing as long as $i doesn't grow beyong the length of $records and doesn't exceed the upperLimit.
    for($i=0; $i<count($records); $i++) {
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
  }
}

$smarty->assign("showResults", $showResults);
 
 $smarty->display('search.php.tpl');
?>