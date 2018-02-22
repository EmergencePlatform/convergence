{extends designs/convergence.tpl}

{block title}{$data->Label} | {$dwoo.parent}{/block}

{block search-form}
    <form action="/sites" class="navbar-form navbar-right">
        <input type="text" name="q" class="form-control" placeholder="Search..." {if $.get.q}value="{$.get.q}"{/if}>
    </form>
{/block}

{block content}
<div class="page-header">
    <div class='btn-toolbar pull-right'>
        <div class='btn-group'>
            <a href="/{$data->getUrl('edit')}" class="btn btn-primary">{_ "Edit Site"}</a>
            <a href="/{$data->getUrl('update')}" class="btn btn-success">{_ "Update Site"}</a>
            <a href="/{Convergence\Site::$collectionRoute}" class="btn btn-info">{_ "All Sites"}</a>
        </div>
    </div>
    <h1>Site: {$data->Label}</h1>
</div>

<div class="row">
    <div class="col-sm-12">
        <h2>Site Data</h2>
        <table class="table">
            <tbody>
                <tr>
                    <th>Label</th>
                    <td>{$data->Label}</td>
                </tr>
                <tr>
                    <th>Handle</th>
                    <td>{$data->Handle}</td>
                </tr>
                <tr>
                    <th>File System Status</th>
                    <td>
                        {if $data->Updating == 1}
                            Updating
                        {elseif $data->ParentSite}
                            {if $data->ParentSite->LocalCursor !== $data->ParentCursor}
                                <span class="text-danger">Out of Date</span>
                            {else}
                                <span class="text-success">Synced</span>
                            {/if}
                        {else}
                            N/A
                        {/if}
                    </td>
                </tr>
                <tr>
                    <th>Primary Hostname</th>
                    <td><a href="//{$data->PrimaryHostname->Hostname}" target="_blank">{$data->PrimaryHostname->Hostname}</a></td>
                </tr>
                <tr>
                    <th>Secondary Hostnames</th>
                    <td>
                        {foreach item=Hostname from=$data->Hostnames}
                            {if $Hostname->ID !== $data->PrimaryHostnameID}
                                <a href="//{$Hostname->Hostname}" target="_blank">{$Hostname->Hostname}</a><br>
                            {/if}
                        {/foreach}
                    </td>
                <tr>
                    <th>Inheritance Key</th>
                    <td>{$data->InheritanceKey}</td>
                </tr>
                <tr>
                    <th>Host</th>
                    <td>{if $data->Host}{$data->Host->Hostname}{else}None{/if}</td>
                </tr>
                <tr>
                    <th>Parent Site</th>
                    <td>{if $data->ParentSite}<a href="{$data->ParentSite->PrimaryHostname->Hostname}" target="_blank">{$data->ParentSite->PrimaryHostname->Hostname}</a>{else}None{/if}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
{/block}
