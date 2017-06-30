{extends designs/convergence.tpl}

{block title}{if $data->isPhantom}{_ 'Create'}{else}{_('Edit %s')|sprintf:$data->Label|escape}{/if} &mdash; {_ 'Sites'} &mdash; {$dwoo.parent}{/block}

{block search-form}
    <form action="/sites" class="navbar-form navbar-right">
        <input type="text" name="q" class="form-control" placeholder="Search..." {if $.get.q}value="{$.get.q}"{/if}>
    </form>
{/block}

{block content}
<div class="page-header">
    <div class='btn-toolbar pull-right'>
        <div class='btn-group'>
            {if !$data->isPhantom}<a href="/{$data->getUrl()}" class="btn btn-primary">{_ "View Site"}</a>{/if}
            <a href="/{Convergence\Site::$collectionRoute}" class="btn btn-success">{_ "All Sites"}</a>
        </div>
    </div>
    <h1>{if $data->isPhantom}{_ "Create New Site"}{else}{_("Edit Site: %s")|sprintf:$data->Label|escape}{/if}</h1>
</div>

{if !$data->isValid}{dump $data->validationErrors}
    <div class="alert alert-danger">
        <strong>{_ "There were problems with your entry:"}</strong>
        <ul class="errors">
        {foreach item=error key=field from=$data->validationErrors}
            <li>{dump $error}</li>
        {/foreach}
        </ul>
    </div>
{/if}

{if $.get.updated == 'true'}
    <div class="alert alert-success">{_ "Site updated"}</div>
{/if}

<div class="row">
    <div class="col-sm-12 col-md-6">
        <form method="POST">
            <div class="form-group">
                <label for="field-url-developers">{_ "Site Label"}:</label>
                <input type="text" name="Label" id="Label" class="form-control" placeholder="My Site" value="{refill field=Label default=$data->Label}" required />
            </div>
            <div class="form-group">
                <label for="field-url-developers">{_ "Handle (16 Character Max)"}:</label>
                <input type="text" name="Handle" id="Handle" class="form-control" maxlength="16" placeholder="example" value="{refill field=Handle default=$data->Handle}" required {if !$data->isPhantom}disabled{/if} />
            </div>
            <div class="form-group">
                <label for="field-title">{_ "Host"}:</label>
                {$hosts = Convergence\Host::getAll()}
                <select name="HostID" id="HostID" class="form-control" required {if !$data->isPhantom}disabled{/if}>
                    {foreach item=Host from=$hosts}
                        <option value="{$Host->ID}" {if $Host->ID == $data->HostID}selected{/if}>{$Host->Hostname}</option>
                    {/foreach}
                </select>
            </div>
            <div class="form-group">
                <label for="field-title">{_ "Deployment"}:</label>
                {$deployments = Convergence\Deployment::getAll()}
                <select name="DeploymentID" id="DeploymentID" class="form-control" required {if !$data->isPhantom}disabled{/if}>
                    {foreach item=Deployment from=$deployments}
                        <option value="{$Deployment->ID}" {if $Deployment->ID == $data->HostID}selected{/if}>{$Deployment->Label}</option>
                    {/foreach}
                </select>
            </div>
            <div class="form-group">
                <label for="PrimaryHostname">{_ "Primary Hostname"}:</label>
                <input type="text" name="PrimaryHostname" id="PrimaryHostname" class="form-control" placeholder="{_ 'example.com'}" value="{refill field=PrimaryHostname default=$data->PrimaryHostname->Hostname}" required />
            </div>
            <div class="form-group">
                <label for="field-title">{_ "Parent Site"}:</label>
                {$parentSites = Convergence\Site::getAll()}
                <select name="ParentSiteID" id="ParentSiteID" class="form-control" {if !$data->isPhantom}disabled{/if}>
                    <option value="">Select parent site</option>
                    {foreach item=ParentSite from=$parentSites}
                        <option value={$ParentSite->ID} {if $data->ParentSiteID == $ParentSite->ID}selected{/if}>{$ParentSite->Handle}</option>
                    {/foreach}
                </select>
            </div>
            <div class="form-group">
                <label for="ParentHostname">{_ "Parent Hostname"}:</label>
                <input type="text" name="ParentHostname" id="ParentHostname" class="form-control" placeholder="{_ 'example.com'}" value="{refill field=ParentHostname default=$data->ParentSite->PrimaryHostname->Hostname}" {if !$data->isPhantom}disabled{/if} />
            </div>
            <div class="form-group">
                <label for="ParentKey">{_ "Parent Inheritance Key"}:</label>
                <input type="text" name="ParentKey" id="ParentKey" class="form-control" placeholder="{_ 'abcdefg'}" value="{refill field=ParentKey default=$data->ParentSite->InheritanceKey}" {if !$data->isPhantom}disabled{/if} />
            </div>
            <button type="submit" class="btn btn-primary">{if $data->isPhantom}{_ 'Create Site'}{else}{_ 'Save Changes'}{/if}</button>
        </form>
    </div>
</div>
{/block}
