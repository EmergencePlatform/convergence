{extends designs/convergence.tpl}

{block title}{if $data->isPhantom}{_ 'Create'}{else}{_('Edit %s')|sprintf:$data->Hostname|escape}{/if} &mdash; {_ 'Hosts'} &mdash; {$dwoo.parent}{/block}

{block content}
<div class="page-header">
    <div class='btn-toolbar pull-right'>
        <div class='btn-group'>
            {if !$data->isPhantom}<a href="/{$data->getUrl()}" class="btn btn-primary">{_ "View Host"}</a>{/if}
            <a href="/{Convergence\Host::$collectionRoute}" class="btn btn-success">{_ "All Hosts"}</a>
        </div>
    </div>
    <h1>{if $data->isPhantom}{_ "Create Host"}{else}{_ "Edit Host:"} {$data->Hostname}{/if}</h1>
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
    <div class="col-sm-6">
        <form method="POST" {if !$data->isPhantom}action="/hosts/{$data->ID}/edit"{/if}>
            <fieldset>
                <div class="form-group">
                    <label for="Hostname">Hostname</label>
                    <input type="text" class="form-control" name="Hostname" required value="{$data->Hostname}">
                </div>
                <div class="form-group">
                    <label for="MaxSites">Max Sites</label>
                    <input type="text" class="form-control" name="MaxSites" required value="{$data->MaxSites}">
                </div>
                <div class="form-group">
                    <label for="ApiUsername">ApiUsername</label>
                    <input type="text" class="form-control" name="ApiUsername" value="{$data->ApiUsername}">
                </div>
                <div class="form-group">
                    <label for="ApiKey">Api Key</label>
                    <input type="text" class="form-control" name="ApiKey" value="{$data->ApiKey}">
                </div>
                <div class="form-group">
                    <label for="KernelVersion">Kernel Version</label>
                    <input type="text" class="form-control" name="KernelVersion" value="{$data->KernelVersion}">
                </div>
                <button type="submit" class="btn btn-primary">{if $data->isPhantom}Create{else}Update{/if}</button>
            </fieldset>
        </form>
    </div>
</div>
{/block}
