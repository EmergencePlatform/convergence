{extends designs/convergence.tpl}

{block title}{if $data->isPhantom}{_ 'Create'}{else}{_('Edit %s')|sprintf:$data->Label|escape}{/if} &mdash; {_ 'Sites'} &mdash; {$dwoo.parent}{/block}

{block content}
<div class="page-header">
    <div class='btn-toolbar pull-right'>
        <div class='btn-group'>
            {if !$data->isPhantom}<a href="/{$data->getUrl()}" class="btn btn-primary">{_ "View Deployment"}</a>{/if}
            <a href="/{Convergence\Deployment::$collectionRoute}" class="btn btn-success">{_ "All Deployments"}</a>
        </div>
    </div>
    <h1>{if $data->isPhantom}{_ "Create Deployment"}{else}{_ "Edit Deployment:"} {$data->Label}{/if}</h1>
</div>

{if !$data->isValid}
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
    <div class="alert alert-success">{_ "Updated"}</div>
{/if}

<div class="row">
    <div class="col-sm-12 col-md-6">
        <form method="POST" {if !$data->isPhantom}action="/deployments/{$data->ID}/edit"{/if}>
            <fieldset>
                <div class="form-group">
                    <label for="Label">Label</label>
                    <input type="text" class="form-control" name="Label" required value="{$data->Label}">
                </div>
                <div class="form-group">
                    <label for="Status">Status</label>
                    <select name="Status" id="Status" class="form-control" {if !$data->isPhantom && $data->Status !== 'draft'}disabled{/if}>
                        <option value="draft" {if $data->Status == 'draft'}selected{/if}>Draft</option>
                        <option value="pending" {if $data->Status == 'pending'}selected{/if}>Pending</option>
                        <option value="provisioning" {if $data->Status == 'provisioning'}selected{/if} disabled>Provisioning</option>
                        <option value="available" {if $data->Status == 'available'}selected{/if} disabled>Available</option>
                        <option value="suspended" {if $data->Status == 'suspended'}selected{/if}>Suspended</option>
                        <option value="interrupted" {if $data->Status == 'pending'}interrupted{/if} disabled>Interrupted</option>
                    </select>
                </div>

                {$sites = Convergence\Site::getAll()}
                <div class="form-group">
                    <label for="Status">Parent Site</label>
                    <select name="ParentSiteID" id="ParentSiteID" class="form-control" {if !$data->isPhantom && $data->Status !== 'draft'}disabled{/if}>
                        <option value="0">Skeleton V2</option>
                        {foreach item=Site from=$sites}
                            <option value="{$Site->ID}" {if $Site->ID == $data->ParentSiteID}selected{/if}>{$Site->Label}</option>
                        {/foreach}
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">{if $data->isPhantom}Create{else}Update{/if}</button>
            </fieldset>
        </form>
    </div>
</div>
{/block}
