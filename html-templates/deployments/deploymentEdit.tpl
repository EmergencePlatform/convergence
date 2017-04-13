{extends designs/site.tpl}

{block title}{if $data->isPhantom}{_ 'Create'}{else}{_('Edit %s')|sprintf:$data->Label|escape}{/if} &mdash; {_ 'Sites'} &mdash; {$dwoo.parent}{/block}

{block content}
    <div class="page-header">
        <div class="btn-toolbar pull-right">
            <a href="/{$data->getUrl()}" class="btn btn-success">{_ "View Deployment"}</a>
            <a href="/{Convergence\Deployment::$collectionRoute}" class="btn btn-success">{_ "All Deployments"}</a>
        </div>
        <h1>{if $data->isPhantom}{_ "Create deployment"}{else}{_ "Edit Deployment"}{/if}</h1>
    </div>

    {if !$data->isValid}
        <div class="error well">
            <strong>{_ "There were problems with your entry:"}</strong>
            <ul class="errors">
            {foreach item=error key=field from=$data->validationErrors}
                <li>{dump $error}</li>
            {/foreach}
            </ul>
        </div>
    {/if}

    {if $.get.updated == 'true'}
        <div class="well">{_ "Updated"}</div>
    {/if}

    <form method="POST">
    	<fieldset>
            {field inputName=Label label=Label error=$validationErrors.Label required=true default=$data->Label}
            <label class="field is-required ">
                <span class="field-label">{_ "Status"}:</span>
                <select name="Status" id="Status" class="field-control" {if !$data->isPhantom && $data->Status !== 'draft'}disabled{/if}>
                    <option value="draft" {if $data->Status == 'draft'}selected{/if}>Draft</option>
                    <option value="pending" {if $data->Status == 'pending'}selected{/if}>Pending</option>
                    <option value="pending" {if $data->Status == 'pending'}selected{/if}>Pending</option>
                    <option value="provisioning" {if $data->Status == 'provisioning'}selected{/if} disabled>Provisioning</option>
                    <option value="available" {if $data->Status == 'available'}selected{/if} disabled>Available</option>
                    <option value="suspended" {if $data->Status == 'suspended'}selected{/if}>Suspended</option>
                    <option value="interrupted" {if $data->Status == 'pending'}interrupted{/if} disabled>Interrupted</option>
                </select>
            </label>
            {$sites = Convergence\Site::getAll()}
            <label class="field select-field is-required ">
                <span class="field-label">{_ "Parent Site"}:</span>
                <select name="ParentSiteID" id="ParentSiteID" class="field-control" {if !$data->isPhantom && $data->Status !== 'draft'}disabled{/if}>
                    <option value="0">Skeleton V2</option>
                    {foreach item=Site from=$sites}
                        <option value="{$Site->ID}" {if $Site->ID == $data->ParentSiteID}selected{/if}>{$Site->Label}</option>
                    {/foreach}
                </select>
            </label>
            <div class="submit-area">
                <button type="submit" class="btn btn-primary">{if $data->isPhantom}{_ 'Create Deployment'}{else}{_ 'Save Changes'}{/if}</button>
            </div>
        </fieldset>
    </form>
{/block}
