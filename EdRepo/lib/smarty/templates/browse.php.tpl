{*****************************************************************************
    File:       browse.php.tpl
    Purpose:    Smarty template for EdRepo's browse.php
    Author:     Jon Thompson
    Date:       2 May 2011
*****************************************************************************}

{include file="header.tpl"}

<div id="content">

<h1>{$pageName}</h1>

{if $moduleError=="true"} {* Did searching for records return an error or a "NotImplimented"? *}
<p>This collection does not currently support browsing.</p>

{else} {* This else block runs if searching for records to browse by did not return an error. *}
<form name="browseCriteria" action="browse.php" method="get">
    Browse Modules Alphabetically:<br />    
  {foreach $alphabet as $letter}  
    <a href="browse.php?browseBy=titleStartsWith&parm={$letter}">{$letter}</a>
    {if $letter neq "Z"} | {/if}
  {/foreach}
  
  {if $useCategories eq "true"}
    <br><br>Browse by category:
    <input type="hidden" name="browseBy" value="category" readonly="readonly"></input>
    <select name="parm">
        <option value="*">All</option>
      {foreach $categories as $category}
        {strip}
        <option value="{$category.ID}"
        {if $browseBy eq "category" && $parm eq $category.ID} selected="selected"{/if}
        >{$category.name}</option>
        {/strip}
      {/foreach}
    </select>

    <input type="submit" class="button" name="sub" value="Go"></input>
  {/if} {* end useCategories if *}
</form>

{if $numRecords lte 0}
<p>No modules were found.</p>

{else} {* At least one module found *}

<table class="sortable">
    <thead><tr>
        <th>ID</th>
        <th>Title</th>
        <th>Author</th>
        <th>Date</th>
        <th>Version</th>
        {if $useCategories eq "true"}<th>Category</th>{/if}
    </thead><tbody>
    
    {foreach $records as $record} {* loop through found modules *}
    {if $record@index gte $lowerLimit && $record@index lt $upperLimit && $record@index lt $numRecords}
    <tr>
        <td>{$record.moduleID}</td>
        <td><a href="viewModule.php?moduleID={$record.moduleID}">{$record.title}</a></td>
        <td>{$record.authorFirstName} {$record.authorLastName}</td>
        <td>{$record.date}</td>
        <td>{$record.version}</td>
      {if $useCategories eq "true"}
        <td>
        {foreach $recordCategories[$record@index] as $category}
         {$category}
        {/foreach}
        </td>
      {/if}
    </tr>
    {/if}
    {/foreach}
</table>

{* Print any needed "Previous Page" or "Next Page" links *}
<p>
  {if $page gt 1}
    <a href="browse.php?browseBy={$browseBy}&page={$page-1}&parm={$parm}&recordsPerPage={$recordsPerPage}">&lt; Previous Page</a>
  {else}
    <a class="disabled">&lt; Previous Page</a>
  {/if}
  
    | Page <strong>{$page}</strong> of <strong>{$numPages}</strong> |
    
  {if $numRecords gt ($page * $recordsPerPage)}
    <a href="browse.php?browseBy={$browseBy}&page={$page+1}&parm={$parm}&recordsPerPage={$recordsPerPage}">Next Page &gt;</a>
  {else}
    <a class="disabled">Next Page &gt;</a>
  {/if}
</p>

{/if} {* end at least one module found if *}

{/if} {* end record error if *}

</div>

{include file="footer.tpl"}
