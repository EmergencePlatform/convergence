{extends designs/site.tpl}

{block title}{$data->Label} | {$dwoo.parent}{/block}

{block content}
    <div class="container">
        <div class="row">
            <header class="page-header">
                <div class="btn-toolbar pull-right">
                    <a href="/{$data->getUrl('edit')}" class="btn btn-primary">{_ "Edit Deployment"}</a>
                    <a href="/{Convergence\Deployment::$collectionRoute}" class="btn btn-success">{_ "All Deployments"}</a>
                </div>
                <h1>{$data->Label}</h1>
            </header>
            <table class="table">
                <tbody>
                    <tr>
                        <th>Label</th>
                        <td>{$data->Label}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{$data->Status}</td>
                    </tr>
                    <tr>
                        <th>Parent Site</th>
                        <td>{if $data->ParentSiteID !== 0}{$data->ParentSite->PrimaryHostname->Hostname}{else}Skeleton V2{/if}</td>
                    </tr>
                </tbody>
            </table>
            <h2>Sites</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Handle</th>
                        <th>Hostname</th>
                        <th>Parent</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach item=Site from=$data->Sites}
                        <tr>
                            <td>{$Site->Label}</td>
                            <td>{$Site->Handle}</td>
                            <td><a href="//{$Site->PrimaryHostname->Hostname}" target="_blank">{$Site->PrimaryHostname->Hostname}</a></td>
                            <td>
                                {if $Site->ParentSite}
                                    <a href="//{$Site->ParentSite->PrimaryHostname->Hostname}" target="_blank">{$Site->ParentSite->PrimaryHostname->Hostname}
                                {else}
                                    <a href="//{Convergence\Deployment::$defaultParentHostname}" target="_blank">{Convergence\Deployment::$defaultParentHostname}</a>
                                {/if}
                            </td>
                        </tr>
                    {foreachelse}
                        <tr>
                            <td colspan="4">Sorry no sites</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/block}
