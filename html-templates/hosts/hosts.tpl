{extends designs/convergence.tpl}

{block content}
{load_templates subtemplates/paging.tpl}
<div class="page-header">
    <div class='btn-toolbar pull-right'>
        <div class='btn-group'>
            <a href="/hosts/create" class="btn btn-primary">{_ "Add Host"}</a>
        </div>
    </div>
    <h1>{_ "Hosts"}</h1>
</div>
<div class="row">
    <div class="col-sm-12">
        {if $data}
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Hostname</th>
                        <th>Max Sites</th>
                        <th>Username</th>
                        <th>Key</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach item=Host from=$data}
                        <tr>
                            <td>{$Host->Hostname}</td>
                            <td>{$Host->MaxSites}</td>
                            <td>{$Host->ApiUsername}</td>
                            <td>{$Host->ApiKey}</td>
                            <td class="text-right"><a href="{$Host->getUrl()}" class="btn btn-sm btn-primary">View</a> <a href="{$Host->getUrl('edit')}" class="btn btn-sm btn-success">Edit</a></td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        {else}
            <p>Sorry, no hosts available.</p>
        {/if}
    </div>
</div>     
{/block}
