{*****************************************************************************
    File:       moduleWizard.tpl
    Purpose:    Smarty template for EdRepo's module wizard form
    Author:     Jon Thompson
    Date:       6 June 2011
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

{if $hasPermission == true && $moduleAction != "error"}

{if $moduleAction == "createNewVersion"}
<p><strong>Note:</strong> You are about to create a new version of a previous module. 
If you didn't intend to do this, click the "Cancel" button at the bottom of this form or simply 
<a href="../showMyModules.php">return to My Modules</a>.</p>
{/if}


<script type="text/javascript">
{literal}

var fieldCounter = new Array();
fieldCounter["Authors"] = 0;
fieldCounter["Topics"] = 0;
fieldCounter["Objectives"] = 0;
fieldCounter["Prereqs"] = 0;
fieldCounter["Categories"] = 0;
fieldCounter["IRefs"] = 0;
fieldCounter["ERefs"] = 0;

var limit = 99;
function add(field, value){
    var counter = fieldCounter[field];
    if (counter === limit)  {
        alert("You have reached the limit of adding " + counter + " inputs");
    }
    else {
        var parent = field + "List";
        var newDiv = document.createElement('div');
        newDiv.setAttribute("id", "module"+field+"Div"+counter);
        newDiv.setAttribute("class", "fieldItem");
        switch (field)
        {
        case "Authors":
            newDiv.innerHTML = '<button class="button" type="button"'
                + ' onclick="remove(\''+field+'\', '+counter+')">X</button>'
                + '<input type="text" name="module' + field + counter + '"'
                + ' value="' + value + '" />';
            break;
        case "Categories":
            newDiv.innerHTML = '<button class="button" type="button"'
                + ' onclick="remove(\''+field+'\', '+counter+')">X</button>'
                + '<select name="moduleCategory' + counter + '">'
                + printCategorySelectInnerHTML(value) + '</select>';
            break;
        case "IRefs":
        case "ERefs":  
            var val = value.split('$$$delim$$$');
            if (val.length != 2) {
                val[0] = "";
                val[1] = "";
            }
            var inner = '<button class="button" type="button"'
                + ' onclick="remove(\''+field+'\', '+counter+')">X</button>'
                + '<strong>Description of resource:</strong><br />'
                + '<textarea id="module' + field + 'Textarea' + counter + '"'
                + ' name="module' + field + counter + '">' + val[0] + '</textarea><br />'
                + '<strong>';
            if (field == "IRefs") {
                inner += 'Module ID of reference:';
            } else {
                inner += 'Citation for external reference:';
            }
            inner += '</strong><br />'
                + '<input id="module' + field + 'Input' + counter + '" type="text"'
                + ' name="module' + field + 'Link' + counter + '" size="10" value="' + val[1] + '" />';
            newDiv.innerHTML = inner;
            break;
        case "Topics":
        case "Objectives":
        case "Prereqs":
            newDiv.innerHTML = '<button class="button" type="button"'
                + ' onclick="remove(\''+field+'\', '+counter+')">X</button>'
                + '<textarea name="module' + field + counter + '">'
                + value + '</textarea>';
            break;
        default:
            alert("Error: Don't know how to handle the field: " + field);
        }
        document.getElementById(parent).appendChild(newDiv);
        fieldCounter[field]++;
        
        var noModule = document.getElementById("noModule" + field);
        if (fieldCounter[field] > 0) {            
            noModule.value="false";
        } else {
            noModule.value="true"
        }
    }
}

function remove(field, id){
    var parent = document.getElementById(field + "List");
    var child = document.getElementById("module" + field + "Div" + id);
    if (parent != child) {
        parent.removeChild(child);
        
        var i;
        for (i=id+1; i < fieldCounter[field]; i++) {
            var child = document.getElementById("module" + field + "Div" + i);
            var newID = i - 1;
            child.setAttribute("id", "module" + field + "Div" + newID );            
            if (field == "Categories") {
                child.lastChild.setAttribute("name", "moduleCategory" + newID);
            } else if (field == "IRefs" || field == "ERefs") {
                var child_textarea = document.getElementById('module' + field + 'Textarea' + i);
                var child_input = document.getElementById('module' + field + 'Input' + i);
                child_textarea.setAttribute("id", 'module' + field + 'Textarea' + newID);
                child_textarea.setAttribute("name", 'module' + field + newID);
                child_input.setAttribute("id", 'module' + field + 'Input' + newID);
                child_input.setAttribute("name", 'module' + field + 'Link' + newID);
            } else {
                child.lastChild.setAttribute("name", "module" + field + newID);
            }
            // set button's onclick action
            child.firstChild.setAttribute("onclick", 'remove(\''+field+'\', '+newID+')')
        }
        
        fieldCounter[field]--;
        
        var noModule = document.getElementById("noModule" + field);
        if (fieldCounter[field] > 0) {            
            noModule.value="false";
        } else {
            noModule.value="true"
        }
    }
    
}



function createBoxes(savedAuthors, savedObjectives, savedTopics, savedPrereqs, savedCategoriesIDs, savedIRefs, savedERefs) {
  var i;
  //Create a box for all saved authors
  for(i=0; i<savedAuthors.length; i++) {
    add("Authors", savedAuthors[i]);
  }
  //Create a box for all saved objectives
  for(i=0; i<savedObjectives.length; i++) {
    add("Objectives", savedObjectives[i]);
  }
  //Create a box for all saved topics
  for(i=0; i<savedTopics.length; i++) {
    add("Topics", savedTopics[i]);
  }
  //Create a box for all saved prereqs
  for(i=0; i<savedPrereqs.length; i++) {
    add("Prereqs", savedPrereqs[i]);
  }
  //Create a box for all saved categories
  for(i=0; i<savedCategoriesIDs.length; i++) {
    add("Categories", savedCategoriesIDs[i]);
  }
  //Create a box for all saved internal references
  for(i=0; i<savedIRefs.length; i++) {
    add("IRefs", savedIRefs[i]);
  }
  //Create a box for all saved external references
  for(i=0; i<savedERefs.length; i++) {
    add("ERefs", savedERefs[i]);
  }
}
    
{/literal}    

// function from original EdRepo, adapted for Smarty by Jon Thompson
function printCategorySelectInnerHTML(preSelectedID) {literal}{{/literal}
  var tempIDs=new Array();
  var tempNames=new Array();
  var i;
  var r=""; //r will be what is returned.  It is a series of <option> tags.
  {foreach $allCategories as $category}
    tempIDs.push("{$category.ID}");
    tempNames.push("{$category.name}");
  {/foreach}
  {literal}
  for(i=0; i<tempIDs.length; i++) {
    if(preSelectedID===tempIDs[i]) {
      r=r+"<option value=\""+tempIDs[i]+"\" selected=\"selected\">"+tempNames[i]+"</option>";
    } else {
      r=r+"<option value=\""+tempIDs[i]+"\">"+tempNames[i]+"</option>";
    }
  }
  return r;
}{/literal}

    var savedAuthors=new Array();
    var savedTopics=new Array();
    var savedObjectives=new Array();
    var savedPrereqs=new Array();
    var savedCategoriesIDs=new Array();
    var savedIRefs=new Array();
    var savedERefs=new Array();
    
    // initial fill array 
    {if $moduleAction == "create"}
        savedAuthors.push("{$user.firstName} {$user.lastName}");
    {else}
        {if $step == "1"}
            {foreach $savedAuthors as $savedAuthor}
                savedAuthors.push("{$savedAuthor}");
            {/foreach}
            {foreach $savedObjectives as $savedObjective}
                savedObjectives.push("{$savedObjective}");
            {/foreach}
            {foreach $savedTopics as $savedTopic}
                savedTopics.push("{$savedTopic}");
            {/foreach}
            {foreach $savedPrereqs as $savedPrereq}
                savedPrereqs.push("{$savedPrereq}");
            {/foreach}
            {foreach $savedCategories as $savedCategory}
                savedCategoriesIDs.push("{$savedCategory}");
            {/foreach}
        {elseif $step == "3"}
            {foreach $savedIRefs as $savedIRef}
                savedIRefs.push("{$savedIRef}");
            {/foreach}
            {foreach $savedERefs as $savedERef}
                savedERefs.push("{$savedERef}");
            {/foreach}
        {/if}
    {/if}
    
    {literal}
    $(document).ready(function() {
        createBoxes(savedAuthors, savedObjectives, savedTopics, savedPrereqs, savedCategoriesIDs, savedIRefs, savedERefs);
    });
    {/literal}
</script>

<form method="post" name="mainForm" id="mainForm" action="index.php" class="tabular">
    <div id="wizard-nav">
        <input{if $step == 1} class="active"{/if} type="submit" name="action" value="Basic Info" />
        <input{if $step == 2} class="active"{/if} type="submit" name="action" value="Materials" />
        <input{if $step == 3} class="active"{/if} type="submit" name="action" value="References" />
        <input{if $step == 4} class="active"{/if} type="submit" name="action" value="Submit" />
    </div>
    
    <input type="hidden" readonly="readonly" name="moduleID" value="{$moduleInfo.moduleID|default:''}"></input>
    <input type="hidden" readonly="readonly" name="moduleAction" value="{$moduleAction}"></input>
    <input type="hidden" readonly="readonly" name="step" value="{$step}"></input>
    
{if $step == 1} {* STEP 1 : Basic Info **************************************}
    
    <input type="hidden" name="noModuleAuthors" value="true" id="noModuleAuthors"></input>
    <input type="hidden" name="noModuleTopics" value="true" id="noModuleTopics"></input>
    <input type="hidden" name="noModuleObjectives" value="true" id="noModuleObjectives"></input>
    <input type="hidden" name="noModulePrereqs" value="true" id="noModulePrereqs"></input>
    <input type="hidden" name="noModuleCategories" value="true" id="noModuleCategories"></input>
    
    <div class="fieldRow">
        <label><h3>Module Title</h3></label>
        <div class="fieldInput"><input type="text" value="{$moduleInfo.title|default:''}" name="moduleTitle" /></div>
    </div>

    <div class="fieldRow">
        <label><h3>Abstract</h3><p>A description of this module.</p></label>
        <div class="fieldInput"><textarea name="moduleAbstract" style="width: 100%;">{$moduleInfo.abstract|default:''}</textarea></div>
    </div>

    <div class="fieldRow">
        <label>
            <h3>Authors</h3>
            <p>You may add as many authors to this module as you wish.  By default, you are the only author.</p>
        </label>
        <div class="fieldInput">
            <div id="AuthorsList"></div>
            <a class="button" onclick="add('Authors', '');">Add Author</a>
        </div>
	</div>

    <div class="fieldRow">
	<label>
        <h3>Module Size</h3>
        <p>You may specify up to four different sizes for a module.  Each size should indicate how long each portion of the module is intended to take, and how many people it can accommodate.
        <br><br>Modules may include sizes for as many or as few components as it makes sense to include.</p>
    </label>
    <div class="fieldInput">
        <strong>Lecture Size</strong><br />
        <input type="text" maxlength="150" name="moduleLectureSize" value="{$moduleInfo.lectureSize|default:''}"></input><br />
        <br />
        <strong>Exercise Size</strong><br />
        <input type="text" maxlength="150" name="moduleExerciseSize" value="{$moduleInfo.exerciseSize|default:''}"></input><br />
        <br />
        <strong>Lab Size</strong><br />
        <input type="text" maxlength="150" name="moduleLabSize" value="{$moduleInfo.labSize|default:''}"></input><br />
        <br />
        <strong>Homework Size</strong><br />
        <input type="text" maxlength="150" name="moduleHomeworkSize" value="{$moduleInfo.homeworkSize|default:''}"></input><br />
        <br />
        <strong>Other Size</strong><br />
        <input type="text" maxlength="150" name="moduleOtherSize" value="{$moduleInfo.otherSize|default:''}"></input></div>
	</div>
    
    
    <div class="fieldRow">
		<label><h3>Categories</h3></label>
		<div class="fieldInput">
            <div id="CategoriesList"></div>
            <a class="button" onclick="add('Categories', 0);">Add Category</a>
        </div>
	</div>
    <div class="fieldRow">
		<label><h3>Topics</h3></label>
		<div class="fieldInput">
            <div id="TopicsList"></div>
            <a class="button" onclick="add('Topics', '');">Add Topic</a>
        </div>
	</div>
    <div class="fieldRow">
		<label><h3>Objectives</h3></label>
		<div class="fieldInput">
            <div id="ObjectivesList"></div>
            <a class="button" onclick="add('Objectives', '');">Add Objective</a>
        </div>
	</div>
    <div class="fieldRow">
		<label><h3>Prerequisites</h3></label>
		<div class="fieldInput">
            <div id="PrereqsList"></div>
            <a class="button" onclick="add('Prereqs', '');">Add Prerequisite</a>
        </div>
	</div>
    
{elseif $step == 2} {* STEP 2 : Materials ***********************************}
    {if count($materials) > 0}
    <br />
    <table>
        <thead><th>Title</th><th>Description</th><th></th></thead>
        <tbody>
        {foreach $materials as $material}
        <tr>
            <td><a href="material.php?action=edit&moduleID={$moduleInfo.moduleID}&materialID={$material.materialID}">{$material.title}</a></td>
            <td>{$material.description}</td>
            <td>
                <a class="button" href="material.php?action=edit&moduleID={$moduleInfo.moduleID}&materialID={$material.materialID}">Edit</a>
                <a class="button" href="material.php?action=delete&moduleID={$moduleInfo.moduleID}&materialID={$material.materialID}">Delete</a>
            </td>
        </tr>
        {/foreach}
        </tbody>
    </table>
    {/if}
    
    <br /><a class="button" href="material.php?action=add&moduleID={$moduleInfo.moduleID}">Add Material</a><br /><br />
    
{elseif $step == 3} {* STEP 3 : References **********************************}
    <input id="noModuleERefs" type="hidden" value="true" name="noModuleERefs">
    <input id="noModuleIRefs" type="hidden" value="true" name="noModuleIRefs">
    
    <div class="fieldRow">
        <label>
            <h3>Related Modules</h3>
            <p>If other modules in this collection relate to this module, add them here, along with a brief description of the relation.</p>
        </label>
		<div class="fieldInput">
            <div id="IRefsList"></div>
            <button class="button" onclick="add('IRefs', '')" type="button">Add Related Module</button>
        </div>
    </div>
    
    <div class="fieldRow">
        <label>
            <h3>External References</h3>
            <p>External references are references to sources outside this collection that viwers of your module may be interested in. It is reccomended you provide these references in the form of a citation (for example, in APA or MLA style).</p>
        </label>
		<div class="fieldInput">
            <div id="ERefsList"></div>
            <button class="button" onclick="add('ERefs', '')" type="button">Add External Reference</button>
        </div>
    </div>
    
{elseif $step == 4} {* STEP 4 : Submit/Publics ******************************}
    <div class="fieldRow">
        <label>
            <h3>Minimum User Level To View Module</h3>
            <p>Specifies the minimum level a user must be to view your module. The lowest level is "No Restrictions", which will allow everyone, including unregistered users, to view your module. Other possible values coorespond to privilege levels of registered users. It is reccomended you set this as low as possible, to prevent unintended blocking of your module. In addition, please note that everyone can search for and see basic information about your module (such as title, author, etc). Restricting access here will only prevent restricted users from viewing details about your module or the module's materials.</p>
        </label>
        
        <div class="fieldInput">
            <select name="moduleMinimumUserType">
                <option{if (isset($moduleInfo.minimumUserType) && $moduleInfo.minimumUserType == "Unregistered") || isset($moduleInfo.minimumUserType) == false} selected="selected"{/if} value="Unregistered">Unregistered Users (do not restrict access to anyone) [Reccomended]</option>
                <option{if isset($moduleInfo.minimumUserType) && $moduleInfo.minimumUserType == "Viewer"} selected="selected"{/if} value="Viewer">Viewers or higher</option>
                <option{if isset($moduleInfo.minimumUserType) && $moduleInfo.minimumUserType == "SuperViewer"} selected="selected"{/if} value="SuperViewer">SuperViewers or higher</option>
                <option{if isset($moduleInfo.minimumUserType) && $moduleInfo.minimumUserType == "Submitter"} selected="selected"{/if} value="Submitter">Submitters or higher</option>
                <option{if isset($moduleInfo.minimumUserType) && $moduleInfo.minimumUserType == "Editor"} selected="selected"{/if} value="Editor">Editors or higher</option>
                <option{if isset($moduleInfo.minimumUserType) && $moduleInfo.minimumUserType == "Admin"} selected="selected"{/if} value="Admin">Administrators Only</option>
            </select>
        </div>
    </div>
    
    <div class="fieldRow">
        <label>
            <h3>Comments</h3>
            <p>Comments about this module. These comments are viewable by anyone who can view details about this module.</p>
        </label>
        
        <div class="fieldInput">
            <textarea name="moduleAuthorComments">{$moduleInfo.authorComments|default:''}</textarea>
        </div>
    </div>

    {if $moduleInfo.status != "Active" && $moduleInfo.status != "PendingModeration"}
    <div class="fieldRow">
        <label>
            <h3>Check-In Comments</h3>
            <p>Any final comments you have regarding this module, the submission process, or any information you wish to share with any moderators. These comments are only visable to moderators.</p>
        </label>
        
        <div class="fieldInput">
            <textarea name="moduleCheckInComments">{$moduleInfo.checkInComments|default:''}</textarea>
        </div>
    </div>
    
    <br />
    
    {* {if $user.type != "Editor" && $user.type != "Admin"} *}
        <input type="submit" class="button" name="action" value="Submit for Moderation" ></input>
    {* {else}
        <input type="submit" class="button" name="action" value="Publish" ></input>
    {/if} *}
    <br /><br />
    {/if} {* end if not active *}
    
{/if} {* end step if *}
    <div>
        <div id="left">
            {if $step > 1}
            <input type="submit" class="button" name="back" value="&lt; Back" />
            {/if}
        </div>
        <div id="center">
            <input type="submit" class="button" name="action" value="Save" />
            {if $moduleAction == "create" || $moduleAction == "createNewVersion"}
            <a class="button" href="../showMyModules.php">Cancel</a>
            {else}
            <input type="submit" class="button" name="action" value="Delete" />
            {/if}
        </div>
        <div id="right">
            {if $step < $NUMBER_STEPS}
            <input type="submit" class="button" name="next" value="Next &gt;" />
            {/if}
        </div>
    </div>
</form>
{/if} {* end $hasPermission == true && $moduleAction != "error" if *}
    
    
</div>

{include file="footer.tpl"}
