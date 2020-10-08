{*****************************************************************************
    File:       search.php.tpl
    Purpose:    Smarty template for EdRepo's "Search" page
    Author:     Jon Thompson
    Date:       20 May 2011
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


<p>You can search by all criteria shown.  Leaving a field blank or, if applicable, setting it to "All" will not limit search results 
by that criteria.</p>
<form name="advancedSearchForm" action="search.php" method="get"><fieldset>
    {if $byTitle == true}
    <div class="fieldRow"><label for="title"><strong>Title:</strong></label>
    <input type="text" name="title" value="{$searchTitle|default:''}"></input></div>
    {/if}
    {if $byAuthor == true}
    <div class="fieldRow"><label for="author"><strong>Author:</strong></label>
    <input type="text" name="author" value="{$searchAuthor|default:''}"></input></div>
    {/if}
    {if $byCategory == true}
    <div class="fieldRow"><label for="title"><strong>Category:</strong></label><select name="category">
        <option value="*"{if $searchCategory == "*"} selected="selected"{/if}>All</option>
        {foreach $categories as $category}
          <option value="{$category.ID}"{if $searchCategory == $category.ID} selected="selected"{/if}>{$category.name}</option>
        {/foreach}
        </select></div>
    {/if}
    </fieldset>
    
    {if $byTitle != true && $byAuthor != true && $byCategory != true}
    <p>Sorry, the back-end in use does not support searching for modules.</p>
    {else}
    <fieldset class="buttons"><input type="submit" class="button" name="sub" value="Search"></input></fieldset>
    {/if}
</form>

{if $showResults == true}

<h2>Results</h2>

{if count($records) <= 0}
<p>Your search returned no results.</p>
{else}
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
    {/foreach}
</table>
{/if} {* end count if *}

{/if} {* end show results if *}

</div>

{include file="footer.tpl"}
