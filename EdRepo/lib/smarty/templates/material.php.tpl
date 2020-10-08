{*****************************************************************************
    File:       material.php.tpl
    Purpose:    Smarty template for EdRepo's "Material Management"
    Author:     Jon Thompson
    Date:       23 June 2011
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

{if $hasPermission == true && $action != "error"}

{if $action == "add" || $action == "doAdd" || $action == "edit" || $action == "doEdit"}
<script type="text/javascript">
    var materialType = "LocalFile"; // default
    var action = "{$action}";
    var materialLink = "{$materialInfo.linkToMaterial|default:''}";
    var materialName = "{$materialInfo.readableFileName|default:''}";
    {literal}
    // setProperMaterialSourceInput from original EdRepo, modified by Jon Thompson
    function setProperMaterialSourceInput() {
      var div=document.getElementById("materialSourceInputDiv");
      var input=document.getElementById("materialSourceInput");
      var selectObj=document.getElementById("materialSourceType");
      var fileOrURL=selectObj.options;
      if(fileOrURL[selectObj.selectedIndex].value=="LocalFile") {
        div.removeChild(input);
        var input2=document.createElement("input");
        input2.setAttribute("name", "materialFile");
        if (action=="edit") {
            input2.setAttribute("type", "text");
        } else {
            input2.setAttribute("type", "file");
        }
        input2.setAttribute("id", "materialSourceInput");
        div.appendChild(input2);
        materialType = "LocalFile";
      } else if(fileOrURL[selectObj.selectedIndex].value=="ExternalURL") {
        div.removeChild(input);
        var input2=document.createElement("input");
        input2.setAttribute("name", "materialURL");
        input2.setAttribute("type", "text");
        input2.setAttribute("id", "materialSourceInput");
        input2.setAttribute("value", "http://");
        div.appendChild(input2);
        materialType = "ExternalURL";
      } else {
        alert("Internal error: Unknown index selected.");
      }
    }
    
    $(document).ready(function() {
        setProperMaterialSourceInput();
        var input=document.getElementById("materialSourceInput");
        
        if (materialType=="ExternalURL") {
            input.value = materialLink;
        } else if (materialType=="LocalFile" && action=="edit") {  
            input.value = materialName;
        }
        
        // can't modify source when editing
        if (action=="edit" || action=="doEdit") {
            input.setAttribute("disabled", "disabled");
            var selectObj=document.getElementById("materialSourceType");
            selectObj.setAttribute("disabled", "disabled");
            
            // REMOVE THE FOLLOWING WHEN EDITING IS IMPLEMENTED
            var submit=document.getElementById("submit");
            submit.setAttribute("disabled", "disabled");
        }
    });
    {/literal}
</script>

{if $action == "edit"}
<p><strong>WARNING:</strong> This backend does not yet support editing, so clicking "Save" will not save any changes.</p>
{/if}

<form enctype="multipart/form-data" method="post" action="material.php" class="tabular">
    <input type="hidden" name="action" value="{if $action == "add" || $action == "doAdd"}doAdd{else}doEdit{/if}" />
    <input type="hidden" name="moduleID" value="{$moduleInfo.moduleID}" />
    
    <div class="fieldRow">
        <label>
            <h3>Material Title</h3>
            <p>A descriptive title for the material.</p>
        </label>
        <div class="fieldInput">
            <input id="materialTitle" type="text" name="materialTitle" value="{$materialInfo.title|default:''}">
        </div>
    </div>
    <div class="fieldRow">
        <label>
            <h3>Author</h3>
            <p>The author of the material. If this material is not your own work, this is the name of the person(s) or orginization(s) who created the material. If you created the material yourself, put your name here.</p>
        </label>
        <div class="fieldInput">
            <input type="text" name="materialCreator" value="{$materialInfo.creator|default:''}">
        </div>
    </div>
    <div class="fieldRow">
        <label>
            <h3>Material Type</h3>
            <p>Describes the type of the material.</p>
        </label>
        <div class="fieldInput">
            <select name="materialType">
                <option{if (isset($materialInfo.type) && $materialInfo.type == "text") || isset($materialInfo.type) == false} selected="selected"{/if} value="text">Text</option>
                <option{if isset($materialInfo.type) && $materialInfo.type == "StillImage"} selected="selected"{/if} value="StillImage">Image/Picture</option>
                <option{if isset($materialInfo.type) && $materialInfo.type == "Software"} selected="selected"{/if} value="Software">Software</option>
                <option{if isset($materialInfo.type) && $materialInfo.type == "Service"} selected="selected"{/if} value="Service">Service</option>
                <option{if isset($materialInfo.type) && $materialInfo.type == "PhysicalObject"} selected="selected"{/if} value="PhysicalObject">Physical Object</option>
                <option{if isset($materialInfo.type) && $materialInfo.type == "MovingImage"} selected="selected"{/if} value="MovingImage">Video, Animation, Moving Image</option>
                <option{if isset($materialInfo.type) && $materialInfo.type == "InteractiveResource"} selected="selected"{/if} value="InteractiveResource">Interactive Resource</option>
                <option{if isset($materialInfo.type) && $materialInfo.type == "Event"} selected="selected"{/if} value="Event">Event</option>
                <option{if isset($materialInfo.type) && $materialInfo.type == "Dataset"} selected="selected"{/if} value="Dataset">Dataset</option>
                <option{if isset($materialInfo.type) && $materialInfo.type == "Collection"} selected="selected"{/if} value="Collection">Collection</option>
                <option{if isset($materialInfo.type) && $materialInfo.type == "NotSpecified"} selected="selected"{/if} value="NotSpecified">Unknown/Other</option>
            </select>
        </div>
    </div>
    <div class="fieldRow">
        <label>
            <h3>Description</h3>
            <p>A brief description of the material.</p>
        </label>
        <div class="fieldInput">
            <textarea name="materialDescription">{$materialInfo.description|default:''}</textarea>
        </div>
    </div>
    <div class="fieldRow">
        <label>
            <h3>Language</h3>
            <p>Specifies the language of the material (for example, English).</p>
        </label>
        <div class="fieldInput">
            <input type="text" name="materialLanguage" value="{$materialInfo.language|default:''}">
        </div>
    </div>
    <div class="fieldRow">
        <label>
            <h3>Material Publisher</h3>
            <p>The publisher of the material.</p>
        </label>
        <div class="fieldInput">
            <input type="text" name="materialPublisher" value="{$materialInfo.publisher|default:''}">
        </div>
    </div>
    <div class="fieldRow">
        <label>
            <h3>Rights</h3>
            <p>
            If this material is covered by a specific liscense or limitation on its use, specify so here. Either the text of a liscense/rights statement, or a link to such a statement is acceptable. Note that the system will display this rights statement/liscense with this material, but can not itself enforce it.
            <br>
            <a href="javascript:toggleRightsExamples();">Toggle Examples</a>
            </span>
            <div id="rightsExamples" class="MIEVDescriptiveText" style="display: none;">
                <ul>
                <li>GNU General Public Liscense V3 (http://www.gnu.org/licenses/gpl-3.0.html)</li>
                <li>Public Domain</li>
                <li>Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License (http://creativecommons.org/licenses/by-nc-sa/3.0/)</li>
                <li>Copyright 2009 John Doe. All rights reserved. Modifications phrohibited without written permission from John Doe, 0 JD Lane, Somewhere, NY 00000</li>
                </ul>
            </div>
        </label>
        <div class="fieldInput">
            <textarea name="materialRights">{$materialInfo.rights|default:''}</textarea>
        </div>
    </div>
    <div class="fieldRow">
        <label>
            <h3>Material Source</h3>
            {if $action == "edit"}
            <p><strong>Note:</strong> To change a material's source, delete this material and create a new one with the new source.</p>
            {/if}
        </label>
        <div class="fieldInput">
            <select id="materialSourceType" onchange="setProperMaterialSourceInput();" name="materialSourceType">
                <option{if (isset($materialInfo.linkType) && $materialInfo.linkType == "LocalFile") || isset($materialInfo.linkType) == false} selected="selected"{/if} value="LocalFile">Upload File</option>
                <option{if isset($materialInfo.linkType) && $materialInfo.linkType == "ExternalURL"} selected="selected"{/if} value="ExternalURL">Internet URL</option>
            </select>
            <br>
            <div id="materialSourceInputDiv">
                <input id="materialSourceInput" type="file" name="materialFile" >
            </div>
        </div>
    </div>
    
    <div>
        <div id="left"></div>
        <div id="center">
            <input class="button" id="submit" type="submit" value="{if $action == "add" || $action == "doAdd"}Add Material{else}Save{/if}" name="submit">
            <a class="button" href="index.php?moduleAction=edit&moduleID={$moduleInfo.moduleID}&step=2">Cancel</a>
        </div>
        <div id="right"></div>
    </div>
</form>

{elseif $action == "delete" || $action == "doDelete"}
<p>You are about to delete the material <strong>{$materialInfo.title}</strong>. Are you sure you want to <strong>permanently delete</strong> this material?</p>

<p>
    <a class="button" href="material.php?action=doDelete&moduleID={$moduleInfo.moduleID}&materialID={$materialInfo.materialID}">Delete</a>
    <a class="button" href="index.php?moduleAction=edit&moduleID={$moduleInfo.moduleID}&step=2">Cancel</a>
</p>

{else}
<p>Undefined action specified. Be sure to only use links on the website to access this page.</p>

{/if} {* end $action if *}

{/if} {* end $hasPermission == true && $moduleAction != "error" if *}
    
    
</div>

{include file="footer.tpl"}
