{*****************************************************************************
    File:       footer.tpl
    Purpose:    Smarty template for EdRepo's footer
    Author:     Jon Thompson
    Date:       29 April 2011
*****************************************************************************}

<div id="footer">
    {if $FOOTER.SHOW_NAME == true || $FOOTER.SHOW_LINKS == true}
    <p>
        {if $FOOTER.SHOW_NAME == true}
            <strong>{$COLLECTION_NAME}</strong><br />
        {/if}
        {if $FOOTER.SHOW_LINKS == true}
            <a href="{$baseDir}index.php">Home</a> | 
            <a href="{$baseDir}about.php">About</a> | 
            <a href="{$baseDir}browse.php">Browse</a> | 
            <a href="{$baseDir}showMyModules.php">My Modules</a> | 
            <a href="{$baseDir}moderate.php">Moderate</a> | 
            <a href="{$baseDir}admin/index.php">Admin</a>
        {/if}
    </p>
    {/if}
    
    {$FOOTER.CONTENT}
    <!--<p>Powered by <a href="http://sourceforge.net/projects/edrepo/">EdRepo</a>.</p>-->
</div>

</div>
</body>
</html>      
