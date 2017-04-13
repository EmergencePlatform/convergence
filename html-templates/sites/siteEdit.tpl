{extends designs/site.tpl}

{block title}{if $data->isPhantom}{_ 'Create'}{else}{_('Edit %s')|sprintf:$data->Label|escape}{/if} &mdash; {_ 'Sites'} &mdash; {$dwoo.parent}{/block}

{block content}
    {$Site = $data}
        
    <div class="container">
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
                <div class="page-header">
                    <div class="btn-toolbar pull-right">
                        <a href="/{$Site->getUrl()}" class="btn btn-primary">{_ "View Site"}</a>
                        <a href="/sites" class="btn btn-success">{_ "All Sites"}</a>
                    </div>
                    <h1>
                        {if $Site->isPhantom}
                            {_ "Create new site"}
                        {else}
                            {_("Edit site %s")|sprintf:$Site->Label|escape}
                        {/if}
                    </h1>
                </div>

                {if !$Site->isValid}
                    <div class="error well">
                        <strong>{_ "There were problems with your entry:"}</strong>
                        <ul class="errors">
                        {foreach item=error key=field from=$Site->validationErrors}
                            <li>{dump $error}</li>
                        {/foreach}
                        </ul>
                    </div>
                {/if}

                {if $.get.updated == 'true'}
                    <div class="well">
                        {_ "Site updated"}
                    </div>
                {/if}

                <form method="POST">
                    <div class="form-group">
                        <label for="field-url-developers">{_ "Site Label"}:</label>
                        <input type="text" name="Label" id="Label" class="form-control" placeholder="My Site" value="{refill field=Label default=$Site->Label}" required />
                    </div>
                    <div class="form-group">
                        <label for="field-url-developers">{_ "Handle (16 Character Max)"}:</label>
                        <input type="text" name="Handle" id="Handle" class="form-control" maxlength="16" placeholder="example" value="{refill field=Handle default=$Site->Handle}" required {if !$Site->isPhantom}disabled{/if} />
                    </div>
                    <div class="form-group">
                        <label for="field-title">{_ "Host"}:</label>
                        {$hosts = Convergence\Host::getAll()}
                        <select name="HostID" id="HostID" class="form-control" required {if !$Site->isPhantom}disabled{/if}>
                            {foreach item=Host from=$hosts}
                                <option value={$Host->ID} {if $Host->ID == $Site->HostID}selected{/if}>{$Host->Hostname}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="PrimaryHostname">{_ "Primary Hostname"}:</label>
                        <input type="text" name="PrimaryHostname" id="PrimaryHostname" class="form-control" placeholder="{_ 'example.com'}" value="{refill field=PrimaryHostname default=$Site->PrimaryHostname->Hostname}" required />
                    </div>
                    <div class="form-group">
                        <label for="field-title">{_ "Parent Site"}:</label>
                        {$parentSites = Convergence\Site::getAll()}
                        <select name="ParentSiteID" id="ParentSiteID" class="form-control" {if !$Site->isPhantom}disabled{/if}>
                            <option value="">Select parent site</option>
                            {foreach item=ParentSite from=$parentSites}
                                <option value={$ParentSite->ID} {if $Site->ParentSiteID == $ParentSite->ID}selected{/if}>{$ParentSite->Handle}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ParentHostname">{_ "Parent Hostname"}:</label>
                        <input type="text" name="ParentHostname" id="ParentHostname" class="form-control" placeholder="{_ 'example.com'}" value="{refill field=ParentHostname default=$Site->ParentSite->PrimaryHostname->Hostname}" {if !$Site->isPhantom}disabled{/if} />
                    </div>
                    <div class="form-group">
                        <label for="ParentKey">{_ "Parent Inheritance Key"}:</label>
                        <input type="text" name="ParentKey" id="ParentKey" class="form-control" placeholder="{_ 'abcdefg'}" value="{refill field=ParentKey default=$Site->ParentSite->InheritanceKey}" {if !$Site->isPhantom}disabled{/if} />
                    </div>
                    <button type="submit" class="btn btn-primary">{if $Site->isPhantom}{_ 'Create Site'}{else}{_ 'Save Changes'}{/if}</button>
                </form>
            </div>
        </div>
    </div>
{/block}
