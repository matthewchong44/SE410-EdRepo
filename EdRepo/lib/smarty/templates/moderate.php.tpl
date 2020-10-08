{*****************************************************************************
    File:       moderate.php.tpl
    Purpose:    Smarty template for EdRepo's "Moderate" page
    Author:     Jon Thompson
    Date:       18May 2011
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

{if $error == "notLoggedIn"}
<h2>You Must Be Logged In To Continue</h2>
<p>You must be logged in to view this page.  You can do so at the <a href="loginLogout.php">log in page</a>.</p>

{elseif $error == "backendSupport"}
<h2>This Feature Is Not Supported</h2>
<p>The backend in use does not support the "UseModules" and/or "SearchModulesByUserID" features which are required by this page.</p>

{elseif $error == "priveleges"}
<h2>Insufficient Privileges To Perform This Action</h2>
<p>You do not have enough privileges to moderate modules.
Please log into an account with sufficient privileges to perform this action.</p>

{else} {* no immediate error found, continue *}

{if $action == "display" || $action == "Approve" || $action == "Deny"}
    {* always display list of modules pending moderation *}

<form name="filter" action="moderate.php" method="get">
    <input type="hidden" readonly="readonly" name="action" value="filter"></input>
    By title: <input type="text" name="filterText" value="{$filterText|default:''}" id="filterTextInput"></input>
    <input type="submit" class="button" name="submit" value="Filter"></input>
</form>

{if count($modules) <= 0}
    {if $wasFiltered == true}
    <p>No modules pending moderation were found matching the specified filter.</p>
    {else}
    <p>There are currently no modules pending moderation.</p>
    {/if}
{else} {* display found modules *}
<table class="sortable moduleInformationView">
    <thead>
        <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Author</th>
        <th>Version</th>
        <th>Date Created</th>
        {*<th>Status</th>*} {* showing status on this page is likely *}
                            {* redundant because all modules should be pending moderation *}
        <th class="sorttable_nosort">Action</th>
        </tr>
    </thead>
    <tbody>
     {foreach $modules as $module}
        <tr>
        <td>{$module.moduleID}</td>
        <td><a href="viewModule.php?moduleID={$module.moduleID}&forceView=true">{$module.title}</a></td>
        <td>{$module.authorFirstName} {$module.authorLastName}</td>
        <td>{$module.version}</td>
        <td>{$module.date}</td>
        {*<td>{$module.status}</td>*}
        <td>
            <form method="get" action="moderate.php">
                <input type="hidden" readonly="readonly" name="moduleID" value="{$module.moduleID}"></input>
                <input type="submit" class="button" name="action" value="Approve"></input> 
                <input type="submit" class="button" name="action" value="Deny"></input>
            </form>
        </td>
        </tr>
      {/foreach}
    </tbody>
    </table>
{/if} {* end 'count modules' if *}

{else}
<p><strong>Error.  An Unknown action was specified.</strong></p>

{/if} {* end 'action' if *}

{/if} {* end error if *}


</div>

{include file="footer.tpl"}