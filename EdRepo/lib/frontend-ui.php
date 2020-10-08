<?php
 /****************************************************************************************************************************
 *    frontend-ui.php - Functions used throughout the front-end to display the user interface.
 *    --------------------------------------------------------------------------------------
 *  Contains functions used by most or all front-end files to assist with displaying the user interface.  This allows parts
 *  of the interface which may change across every page to be centralized in one location, eliminating the need to change every
 *  front-end file should part of the interface change.
 *
 *  Version: 1.0
 *  Author: Ethan Greer
 *
 *  Notes: (none)
 ******************************************************************************************************************************/

function getBaseDir() {
  $BASE_DIR="./";
  $currentBaseDir=basename(getcwd());
  /* You should now test to see if the current base directory is an known subdirectory of the actual base install directory.  If it is, 
    change $BASE_DIR to the relative path component needed to get from the subdirectory to the base install directory (probably just "../"). */
  if($currentBaseDir=="moduleWizard") { //Match against the moduleWizard subdirectory.
    $BASE_DIR="../";
  }
  if($currentBaseDir=="oaiProvider") { //Match against the oaiProvider subdirectory.
    $BASE_DIR="../";
  }
  if($currentBaseDir=="configureCollection") { //Match against the configureCollection subdirectory.
    $BASE_DIR="../";
  }
  if ($currentBaseDir=="admin") {
    $BASE_DIR="../";
  }
  return $BASE_DIR;
}

require(getBaseDir()."lib/config/config.php");

  /* showGuestMenu() - Prints a menu suitable for a guest (someone not logged in). */
  function showGuestMenu() {
	echo '<div class="guestMenu">';
	echo 'To contribute, <a href="'.getBaseDir().'loginLogout.php?action=login">log in</a> or <a href="createAccount.php">create an account</a>.';
	echo '</div>';
  }
  
  function showViewerMenu() {
    echo '<div class="viewerMenu">';
	echo 'Logged in as: <strong>username</strong><br />';
    echo '<a href="'.getBaseDir().'userManageAccount.php">My Account</a> | <a href="'.getBaseDir().'loginLogout.php?action=logout">Logout</a>';
    echo '</div>';
  }
  
  function showSuperViewerMenu() {
    //Currently, this is the same as the viewer menu.  The only difference is a Superviewer can possibly see more modules.
    showViewerMenu();
  }
  
  function showSubmitterMenu() {
    showSuperViewerMenu();
    echo '<div class="submitterMenu"><ul>';
    echo '<li><a href="'.getBaseDir().'showMyModules.php">My Modules</a></li>';
    echo '<li><a href="'.getBaseDir().'moduleWizard/welcome.php">Submit A Module</a></li>';
    echo '</ul></div>';
  }
  
  function showEditorMenu() {
    showSubmitterMenu();
    echo '<div class="editorMenu"><ul>';
    echo '<li><a href="'.getBaseDir().'moduleManagement.php">Module Management<a/></li>';
    echo '<li><a href="'.getBaseDir().'moderate.php">Pending Moderation Requests</a></li>';
    echo '</ul></div>';
  }
  
  /* showAdminMenu() - Displays a sidebar menu for an admin user. */
  function showAdminMenu() {
    showEditorMenu();
    echo '<div class="adminMenu">';
    echo 'Admin Menu';
	echo '<ul>';
    echo '<li><a href="'.getBaseDir().'userManagement.php">User Management</a></li>';
    echo '<li><a href="'.getBaseDir().'configureCollection/index.php">Configure this Collection</a></li>';
	echo '<li><a href="'.getBaseDir().'oaiProvider/index.php">Harvest with OAI-PMH</a></li>';
    echo '</ul></div>';
  }
  
  function showTopNavMenu() {
    require(getBaseDir()."lib/config/config.php");
    echo '<form name="searchForm" action="'.getBaseDir().'search.php" method="get">';
    echo '<a href="'.getBaseDir().'index.php">Home</a> &nbsp;|&nbsp; ';
    echo '<a href="'.getBaseDir().'about.php">About</a> &nbsp;|&nbsp; ';
    echo '<a href="'.getBaseDir().'browse.php">Browse</a> &nbsp;|&nbsp; ';
    echo '<input name="title" type="text"></input><input type="submit" class="button" name="sub" value="Search"></input>';
    echo '<span style="font-size: x-small;"><a href="'.getBaseDir().'search.php">Advanced Search</a></span></form>';
  }
  
  
  
  
  /*
        getHeader() echoes the standard header HTML for all EdRepo pages
            (like <html><head><title>...</head><body>, etc.)
        Author: Jon Thompson
        Date: 2011-04-22
        For: EdRepo's new interface
        
        @param $pageTitle   Title of the page calling getHeader()
        @param $tab             Name of active tab ("home", "about", "browse", "modules", "moderate", or "admin")
    */
  function getHeader($pageTitle, $tab)
  {
    require(getBaseDir()."lib/config/config.php");
    
    // Format content for <title> tage by using collection's name and pageTitle param (if length > 0)
    $title = $COLLECTION_NAME;
    
    if (strlen($pageTitle) > 0) {
      $title .= ' - ' . $pageTitle;
    }
    
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
   
    echo '<html>' . PHP_EOL

        .'<head>' . PHP_EOL
        .'    <title>'.$title.'</title>' . PHP_EOL
        .'    <link rel="stylesheet" type="text/css" href="'.getBaseDir().'lib/look/newInterface/main.css" />' . PHP_EOL
            
        .'    <script src="'.getBaseDir().'lib/sorttable/sorttable.js" type="text/javascript"></script>' . PHP_EOL
        .'    <script src="'.getBaseDir().'lib/jquery/jquery.js" type="text/javascript"></script>' . PHP_EOL
        .'    <script src="'.getBaseDir().'lib/jquery/jquery-functions.js" type="text/javascript"></script>' . PHP_EOL
        .'</head>' . PHP_EOL

        .'<body>' . PHP_EOL
        .'<div id="container">' . PHP_EOL

        .'<div id="header">' . PHP_EOL;
        
    getTopBar();   
    
    echo '    <a href="'.getBaseDir().'index.php" id="logo">' . PHP_EOL
        .'    <h1>'.$COLLECTION_NAME.'</h1>' . PHP_EOL
        .'    </a>' . PHP_EOL;
    
    if (isset($userInformation))
    {
        getNav($tab, $userInformation);
    } else {
        getNav($tab, "");
    }
    
    echo '</div>' . PHP_EOL // end header div

        .'<div id="content">' . PHP_EOL; // The main content section starts right after getHeader() is called
  }
  
  
  
  /*
        getTopBar() echoes the HTML for the top bar (search and login/account button
        Author: Jon Thompson
        Date: 2011-04-22
        For: EdRepo's new interface
    */
  function getTopBar() {
    $accountButtonText = "Login"; // Text for login/account button (default to "Login")
    $accountButtonLink = "loginLogout.php";
    if (isset($_SESSION["authenticationToken"])) // If logged in, set to "My Account"
    {
        $accountButtonText = "My Account";
        $accountButtonLink = "userManageAccount.php";
    }
    
    echo '    <div id="top-bar">' . PHP_EOL
        .'        <form method="get" action="search.php" name="search-form" id="search">' . PHP_EOL
        .'            <input type="text" name="title" />' . PHP_EOL
        .'            <input type="submit" class="button" value="Search" name="submit" />' . PHP_EOL
        .'        </form>' . PHP_EOL
                
        .'        <a href="'.getBaseDir().$accountButtonLink.'" id="account-btn">'.$accountButtonText
        .' <img src="'.getBaseDir().'lib/look/newInterface/down-arrow.png" alt="&darr;" /></a>' . PHP_EOL
                
        .'        <div id="account">' . PHP_EOL;
    if (!isset($_SESSION["authenticationToken"])) // If not logged in, display login form
    {
        echo '            <form name="loginForm" method="post" action="loginLogout.php">' . PHP_EOL
            .'                <input type="hidden" name="action" value="login" />' . PHP_EOL
            .'                <label><strong>Email address:</strong> </label><input name="email" type="text" /><br /><br />' . PHP_EOL
            .'                <label><strong>Password:</strong> </label><input name="password" type="password" /><br /><br />' . PHP_EOL
            .'                <input type="submit" class="button" value="Login" />' . PHP_EOL
            .'            </form>' . PHP_EOL;
    } else {  // Other wise
        echo '            <ul>' . PHP_EOL
            .'               <li><a href="'.getBaseDir().'userManageAccount.php">My Account</a></li>' . PHP_EOL
            .'              <li><a href="'.getBaseDir().'showMyModules.php">My Modules</a></li>' . PHP_EOL
            .'              <li><a href="'.getBaseDir().'moduleWizard/welcome.php">Create New Module</a></li>' . PHP_EOL
            .'             <hr />' . PHP_EOL
            .'              <li><a href="'.getBaseDir().'loginLogout.php?action=logout">Logout</a></li>' . PHP_EOL
            .'          </ul>' . PHP_EOL;
    }
    echo '        </div>' . PHP_EOL
        .'    </div>' . PHP_EOL;  
  }
  
  
  
  
  /*
        getNav() echoes the HTML for the nav menu
        Author: Jon Thompson
        Date: 2011-04-22
        For: EdRepo's new interface
        
        @param $tab     Name of active tab ("home", "about", "browse", "modules", "moderate", or "admin")
        @param $userInformation  Logged-in user information, send empty string ("") if not logged in
    */
  function getNav($tab, $userInformation) {
    // Setup navLi to handle which tab is active
    $navLi = array("home"=>"", "about"=>"", "browse"=>"",
                    "modules"=>"", "moderate"=>"", "admin"=>"");
    $navLi[$tab] = ' class="active"';
    
    echo '    <div id="nav"><ul>' . PHP_EOL
        .'        <li'.$navLi["home"].'><a href="'.getBaseDir().'index.php">Home</a></li>' . PHP_EOL
        .'        <li'.$navLi["about"].'><a href="'.getBaseDir().'about.php">About</a></li>' . PHP_EOL
        .'        <li'.$navLi["browse"].'><a href="'.getBaseDir().'browse.php">Browse</a></li>' . PHP_EOL;
    
    if ($userInformation == "")
    {
        unset($userInformation);
    }
    
    if(isset($userInformation)) {
          $type = $userInformation["type"];
          if($type=="Submitter" || $type=="Editor" || $type=="Admin") {
            echo '        <li'.$navLi["modules"].'><a href="'.getBaseDir().'showMyModules.php">My Modules</a></li>' . PHP_EOL;
          }
          if($type=="Editor" || $type=="Admin") {
            echo '        <li'.$navLi["moderate"].'><a href="'.getBaseDir().'moderate.php">Moderate</a></li>' . PHP_EOL;
          }
          if($type=="Admin") {
            echo '        <li'.$navLi["admin"].'><a href="#">Admin</a></li>' . PHP_EOL;
          }
    }
        
    echo '    </ul></div>' . PHP_EOL;
  }    
  
  
  
  
  /*
        getFooter() echoes the standard footer HTML for all EdRepo pages
            (like </body></html>, etc.)
        Author: Jon Thompson
        Date: 2011-04-22
        For: EdRepo's new interface
    */
  function getFooter() {
    echo '</div>' . PHP_EOL // End of main content section (getFooter() should be called at end of main content)

        .'<div id="footer">' . PHP_EOL
        .'    <p>Powered by <a href="http://sourceforge.net/projects/edrepo/">EdRepo</a>.</p>' . PHP_EOL

        .'</div>' . PHP_EOL
        .'</body>' . PHP_EOL

        .'</html>';
  }
  
  
  
  /* With new interface, these should eventually become deprecated. */
  
  function showHeader() {
    require(getBaseDir()."lib/config/config.php");
	require(getBaseDir()."lib/config/header.php");
    require(getBaseDir()."lib/look/look.php");
	if ($HEADER_LOGO_NAME!="") {
	  $path=getBaseDir().'lib/look/'.$LOOK_DIR.'/'.$HEADER_LOGO_NAME;
	  if (file_exists($path)) { echo '<img id="logo" alt="'.$COLLECTION_NAME.'" src="'.$path.'" />'; }
	}
	if ($HEADER_SHOW_NAME==TRUE) { echo '<h1>'.$COLLECTION_NAME.'</h1>'; }
	echo file_get_contents(getBaseDir()."lib/staticContent/header.html");
  }
  
  function showFooter() {
    require(getBaseDir()."lib/config/config.php");
	require(getBaseDir()."lib/config/footer.php");
    require(getBaseDir()."lib/look/look.php");
    if ($FOOTER_SHOW_NAME==TRUE || $FOOTER_SHOW_LINKS==TRUE) { echo '<p>'; }
	if ($FOOTER_SHOW_NAME==TRUE) { echo '<strong>'.$COLLECTION_NAME.'</strong><br>'; }
	if ($FOOTER_SHOW_LINKS==TRUE) {
	  echo '<a href="'.getBaseDir().'index.php">Home</a> &nbsp;|&nbsp; ';
	  echo '<a href="'.getBaseDir().'about.php">About</a> &nbsp;|&nbsp; ';
	  echo '<a href="'.getBaseDir().'browse.php">Browse</a> &nbsp;|&nbsp; ';
	  echo '<a href="'.getBaseDir().'search.php">Search</a>';
	}
	if ($FOOTER_SHOW_NAME==TRUE || $FOOTER_SHOW_LINKS==TRUE) { echo '</p>'; }
    echo file_get_contents(getBaseDir()."lib/staticContent/footer.html");
  }
?>