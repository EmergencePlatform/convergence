{extends designs/site.tpl}

{block title}{$data->Label} | {$dwoo.parent}{/block}

{block content}
    {$Site = $data}
    <div class="container">
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
                <header class="page-header">
                    <div class="btn-toolbar pull-right">
                        <a href="/{$Site->getUrl('edit')}" class="btn btn-primary">{_ "Edit Site"}</a>
                        <a href="/sites" class="btn btn-success">{_ "Back to All Sites"}</a>
                    </div>
                    <h1>{$Site->Label}</h1>
                </header>
                <table class="table">
                    <tbody>
                        <tr>
                            <th>Label</th>
                            <td>{$Site->Label}</td>
                        </tr>
                        <tr>
                            <th>Handle</th>
                            <td>{$Site->Handle}</td>
                        </tr>
                        <tr>
                            <th>File System Status</th>
                            <td>
                                {if $Site->Updating == 1}
                                    Updating
                                {elseif $Site->ParentSite}
                                    {if $Site->ParentSite->LocalCursor !== $Site->ParentCursor}
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
                            <td><a href="//{$Site->PrimaryHostname->Hostname}" target="_blank">{$Site->PrimaryHostname->Hostname}</a></td>
                        </tr>
                        <tr>
                            <th>Inheritance Key</th>
                            <td>{$Site->InheritanceKey}</td>
                        </tr>
                        <tr>
                            <th>Host</th>
                            <td>{if $Site->Host}{$Site->Host->Hostname}{else}None{/if}</td>
                        </tr>
                        <tr>
                            <th>Parent Site</th>
                            <td>{if $Site->ParentSite}<a href="{$Site->ParentSite->PrimaryHostname->Hostname}" target="_blank">{$Site->ParentSite->PrimaryHostname->Hostname}</a>{else}None{/if}</td>
                        </tr>
                    </tbody>
                </table>
                <h1>File System Summary</h1>
                <div class="btn-toolbar">
                    <a href="/{$Site->getUrl('edit')}" class="btn btn-primary">{_ "Load Summary"}</a>
                    <a href="/sites" class="btn btn-success">{_ "Update File System"}</a>
                    <a href="/sites" class="btn btn-success">{_ "Update Local Cursor"}</a>
                </div>
            </div>
        </div>
    </div>
{/block}
