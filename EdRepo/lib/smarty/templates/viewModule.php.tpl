{*****************************************************************************
    File:       viewModule.php.tpl
    Purpose:    Smarty template for EdRepo's "View Module" page
    Author:     Jon Thompson
    Date:       13 May 2011
*****************************************************************************}

{include file="header.tpl"}

<div id="content">

<h1>{$pageName|default:"404 Error"}</h1>

{if $alert.message != ""}
    <p class="alert {$alert.type|default:"positive"}">
      {if $alert.type == "negative"}
        <img src="{$baseDir}lib/look/{$LOOK_DIR}/failure.png" alt="Failure: " />
      {else}
        <img src="{$baseDir}lib/look/{$LOOK_DIR}/success.png" alt="Success: " />
      {/if}
      
        {$alert.message}
    </p>
{/if}

{if isset($noModule) && $noModule == true}
<h1>Module Not Found</h1>

<p>No module with the specified ID was found.  Please try again with a different ID.</p>

<form name="moduleIDSubmission" action="viewModule.php" method="get">
    <input type="text" name="moduleID"></input>
    <input type="submit" class="button" name="sub" value="View Module"></input>
</form>

{else} {* have a module to view *}

{if $canViewModule == true}
<h2>General Information</h2>

{if $module.status == "InProgress"} {* If the module's status is InProgress, print a warning that the module is not active in the collection. *}
    <p><span class="note">Notice:  This module is not yet active in this collection.</span>  This module has not yet been published 
    to this collection, and can not be searched for or viewed by most users.  To activate this module, it must be submitted to 
    the collection via the module submission wizard{if $NEW_MODULES_REQUIRE_MODERATION == TRUE} and approved by a moderator{/if}.</p>
{/if}

<table class="MIV">
    <tr><td class="highlight">Title</td><td>{$module.title}</td></tr>
    <tr><td class="highlight">Abstract</td><td>{$module.abstract}</td></tr>

    {if $showAuthors == true}
    <tr><td class="highlight" rowspan="{count($authors)}">Authors</td>
    <td>{$authors[0]}</td></tr> {* display the first author *}
    {foreach $authors as $author} {* Loop through any additional authors and display them. *}
      {if $author@index > 0}
      <tr><td>{$author}</td></tr>
      {/if}
    {/foreach}
    {/if}

    <tr><td class="highlight">Last Modified</td><td>{$module.date}</td></tr>
    <tr><td class="highlight">Version</td><td>{$module.version}</td></tr>
    
    {if $showCategories == true}
    <tr><td class="highlight" rowspan="{count($categoryNames)}">Categories</td>
    <td>{$categoryNames[0]}</td></tr> {* display the first categoryName *}
    {foreach $categoryNames as $categoryName} {* Loop through any additional categoryNames and display them. *}
      {if $categoryName@index > 0}
      <tr><td>{$categoryName}</td></tr>
      {/if}
    {/foreach}
    {/if}
    
    <tr><td class="highlight">Comments</td><td>{$module.authorComments}</td></tr>
    
    {if $readRatings == true}
    <tr><td class="highlight">Module Rating</td><td>
      {* If there are no ratings, indicate that (don't try to determine a numerical rating) *}
      {if $ratings.numberOfRatings <= 0} 
          This module as not yet been rated.
      {else} {* This else runs if there is at least one rating for the module. *}
          {round($ratings.rating/$ratings.numberOfRatings , 2)} of 5 (out of {$ratings.numberOfRatings} total ratings)
      {/if}
      
      {if $writeRatings == true} <a href="rate.php?moduleID={$module.moduleID}">Leave a Rating</a>{/if}
    {/if}
    </td></tr>
</table>

<h2>Module Size</h2>
<p>A module's size refers to either how long each component of a module is expected to take, and/or how many people each 
component is designed for.</p>
<table class="MIV">
    <tr><td class="highlight">Lecture</td><td>{$module.lectureSize}</td></tr>
    <tr><td class="highlight">Exercise</td><td>{$module.exerciseSize}</td></tr>
    <tr><td class="highlight">Lab</td><td>{$module.labSize}</td></tr>
    <tr><td class="highlight">Homework</td><td>{$module.homeworkSize}</td></tr>
    <tr><td class="highlight">Other</td><td>{$module.otherSize}</td></tr>
</table>

<h2>Topics, Objectives, and Prerequisites</h2>
<table class="MIV">
{if count($topics) >= 1}
    <tr><td class="highlight" rowspan="{count($topics)}">Topics</td>
    <td>{$topics[0].text}</td></tr> {* display the first topic *}
    {foreach $topics as $topic} {* Loop through any additional topics and display them. *}
      {if $topic@index > 0}
      <tr><td>{$topic.text}</td></tr>
      {/if}
    {/foreach}
{/if}

{if count($objectives) >= 1}
    <tr><td class="highlight" rowspan="{count($objectives)}">Objectives</td>
    <td>{$objectives[0].text}</td></tr> {* display the first objective *}
    {foreach $objectives as $objective} {* Loop through any additional objectives and display them. *}
      {if $objective@index > 0}
      <tr><td>{$objective.text}</td></tr>
      {/if}
    {/foreach}
{/if}

{if count($prereqs) >= 1}
    <tr><td class="highlight" rowspan="{count($prereqs)}">Prerequisites</td>
    <td>{$prereqs[0].text}</td></tr> {* display the first prereq *}
    {foreach $prereqs as $prereq} {* Loop through any additional prereqs and display them. *}
      {if $prereq@index > 0}
      <tr><td>{$prereq.text}</td></tr>
      {/if}
    {/foreach}
{/if}
</table>

{if $showMaterials == true}
<h2>Materials</h2>
{if count($materials) <= 0}
<p>This module contains no materials.</p>

{else} {* has at least one material *}
<table class="MIV">
  {foreach $materialInfo as $material}
    <tr><td class="highlight" rowspan="{$material.rowspan}">{$material.title}</td>
    <td><strong>Title:</strong>  {$material.title}</td></tr>
    <tr><td><strong>Creator:</strong>  {$material.creator}</td></tr>
    <tr><td><strong>Type:</strong>  {$material.type}</td></tr>
    <tr><td><strong>Description:</strong>  {$material.description}</td></tr>
    <tr><td><strong>Publisher:</strong>  {$material.publisher}</td></tr>
    <tr><td><strong>Language:</strong>  {$material.language}</td></tr>
    <tr><td><strong>Rights/Liscense:</strong>  {$material.rights}</td></tr>
    
    {if $readRateMaterials == true}
    <tr><td><strong>Material Rating:</strong>  
      {if $material.numRatings <= 0}
      This material has not been rated yet.
      {else}
      {$material.averageRating} out of 5 (out of {$material.numRatings} total ratings).
      {/if}
      
      {if $writeRateMaterials == true}
      &nbsp;<a href="rate.php?materialID={$material.materialID}">Leave a Rating</a>
      {/if}
    </td></tr>
    {/if}
    
    <tr><td><a href="viewMaterial.php?materialID={$material.materialID}">
        {if $material.linkType == "LocalFile"}Download{else}View{/if} This Material
    </a></td></tr>
  {/foreach}
</table>
{/if} {* end 'count materials' if *}

{/if} {* end 'show materials' if *}

{else} {* can't view module *}
<h1>Insufficient Privileges To View This Module</h1>
<p>You do not have enough permissions to view this module.  Log out and log back in at a higher privilege level to view this 
module.</p>

{/if} {* end 'can view module' if *}


{/if} {* end 'have module' if *}

</div>

{include file="footer.tpl"}