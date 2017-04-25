{extends designs/convergence.tpl}

{block title}{$data->Label} | {$dwoo.parent}{/block}

{block content}
<div class="page-header">
    <div class='btn-toolbar pull-right'>
        <div class='btn-group'>
            <a href="/{$data->getUrl('edit')}" class="btn btn-primary">{_ "Edit Deployment"}</a>
            <a href="/{Convergence\Deployment::$collectionRoute}" class="btn btn-success">{_ "All Deployments"}</a>
        </div>
    </div>
    <h1>{_ "Deployment:"} {$data->Label}</h1>
</div>
<div class="row">
    <div class="col-sm-12 col-md-6">
        <h2>Deployment Data</h2>
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
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <h2>Sites</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Label</th>
                    <th>Handle</th>
                    <th>Hostname</th>
                    <th>Status</th>
                    <th>Parent</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {foreach item=Site from=$data->Sites}
                    <tr>
                        <td>{$Site->Label}</td>
                        <td><a href="/{$Site->getUrl()}">{$Site->Handle}</a></td>
                        <td><a href="//{$Site->PrimaryHostname->Hostname}" target="_blank">{$Site->PrimaryHostname->Hostname}</a></td>
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
                        <td>
                            {if $Site->ParentSite}
                                <a href="//{$Site->ParentSite->PrimaryHostname->Hostname}" target="_blank">{$Site->ParentSite->PrimaryHostname->Hostname}
                            {else}
                                <a href="//{Convergence\Deployment::$defaultParentHostname}" target="_blank">{Convergence\Deployment::$defaultParentHostname}</a>
                            {/if}
                        </td>
                        <td>
                            <form method="POST" action="/{$Site->getUrl('update')}"><input type="submit" class="btn btn-sm btn-primary" value="Update File System"></form>
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
