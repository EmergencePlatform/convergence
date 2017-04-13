{extends designs/site.tpl}

{block title}{if $data->isPhantom}{_ 'Create'}{else}{_('Edit %s')|sprintf:$data->Hostname|escape}{/if} &mdash; {_ 'Hosts'} &mdash; {$dwoo.parent}{/block}

{block content}
    <div class="page-header">
        <div class="btn-toolbar pull-right">
            <a href="/{$data->getUrl()}" class="btn btn-success">{_ "View Hosts"}</a> | 
            <a href="/{Convergence\Host::$collectionRoute}" class="btn btn-success">{_ "All Hosts"}</a>
        </div>
        <h1>{if $data->isPhantom}{_ "Create Host"}{else}{_ "Edit Host"}{/if}</h1>
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
            {field inputName=Hostname label=Hostname error=$validationErrors.Hostname required=true default=$data->Hostname}
            {field inputName=MaxSites label='Max Sites' error=$validationErrors.MaxSites required=true default=$data->MaxSites}
            {field inputName=KernelVersion label=KernelVersion error=$validationErrors.KernelVersion default=$data->KernelVersion}
            {field inputName=ApiUsername label=ApiUsername error=$validationErrors.ApiUsername required=true default=$data->ApiUsername}
            {field inputName=ApiKey label=ApiKey error=$validationErrors.ApiKey required=true default=$data->ApiKey}
            <div class="submit-area">
                <button type="submit" class="btn btn-primary">{if $data->isPhantom}{_ 'Create Host'}{else}{_ 'Save Changes'}{/if}</button>
            </div>
        </fieldset>
    </form>
{/block}
