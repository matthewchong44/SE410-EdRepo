<?php
/****************************************************************************************************************************
*	header.php - Settings for displaying the collection's header.
*                WARNING: Any changes made in this file must also be made in the write command in configureHeader.php!
******************************************************************************************************************************/

/* Show collection name in header */
$HEADER["SHOW_NAME"]=FALSE;

/* File name of header logo (in current theme directory), logo doesn't show if blank ("") */
$HEADER["LOGO_NAME"]="logo.png";

/* Call static header content from file */
$file_location = dirname(__DIR__) . "/staticContent/header.html";
// dirname(__DIR__) puts you in the directory above this one
$HEADER["CONTENT"] = file_get_contents($file_location);

?>