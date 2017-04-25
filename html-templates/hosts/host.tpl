{extends designs/convergence.tpl}

{block title}{$data->Hostname} | {$dwoo.parent}{/block}

{block content}
<div class="page-header">
    <div class='btn-toolbar pull-right'>
        <div class='btn-group'>
            <a href="/{$data->getUrl('edit')}" class="btn btn-primary">{_ "Edit Host"}</a>
            <a href="/{Convergence\Host::$collectionRoute}" class="btn btn-success">{_ "All Hosts"}</a>
        </div>
    </div>
    <h1>Host: {$data->Hostname}</h1>
</div>
<div class="row">
    <div class="col-sm-12">
        <h2>Host Data</h2>
        <table class="table">
            <tbody>
                <tr>
                    <th>Hostname</th>
                    <td>{$data->Hostname}</td>
                </tr>
                <tr>
                    <th>MaxSites</th>
                    <td>{$data->MaxSites}</td>
                </tr>
                <tr>
                    <th>ApiUsername</th>
                    <td>{$data->ApiUsername}</td>
                </tr>
                <tr>
                    <th>ApiKey</th>
                    <td>{$data->ApiKey}</td>
                </tr>
                <tr>
                    <th>KernelVersion</th>
                    <td>{$data->KernelVersion}</td>
                </tr>
            </tbody>
        </table>
        <h2>Deployments</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Label</th>
                    <th>Status</th>
                    <th>Parent</th>
                </tr>
            </thead>
            <tbody>
                {foreach item=Deployment from=$data->Deployments}
                    <tr>
                        <td>{$Deployment->Label}</td>
                        <td>{$Deployment->Status}</td>
                        <td>
                            {if $Deployment->ParentSite}
                                <a href="//{$Deployment->ParentSite->PrimaryHostname->Hostname}" target="_blank">{$Deployment->ParentSite->PrimaryHostname->Hostname}
                            {else}
                                <a href="//{Convergence\Deployment::$defaultParentHostname}" target="_blank">{Convergence\Deployment::$defaultParentHostname}</a>
                            {/if}
                        </td>
                    </tr>
                {foreachelse}
                    <tr>
                        <td colspan="3">Sorry no deployments.</td>
                    </tr>
                {/foreach}
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
                        <td colspan="4">Sorry no sites.</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>
{/block}
