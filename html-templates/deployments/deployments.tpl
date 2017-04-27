{extends designs/convergence.tpl}

{block content}
{load_templates subtemplates/paging.tpl}
<div class="page-header">
    <div class='btn-toolbar pull-right'>
        <div class='btn-group'>
            <a href="/deployments/create" class="btn btn-primary">{_ "Add Deployment"}</a>
        </div>
    </div>
    <h1>{_ "Deployments"}</h1>
</div>
<div class="row">
    <div class="col-sm-12">
        {if $data}
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Status</th>
                        <th>Sites</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach item=Deployment from=$data}
                        <tr>
                            <td>{$Deployment->Label}</td>
                            <td>{$Deployment->Status}</td>
                            <td>{count($Deployment->Sites)}</td>
                            <td class="text-right">
                                <a href="/{$Deployment->getUrl()}" class="btn btn-sm btn-primary">View</a>
                                <a href="/{$Deployment->getUrl('edit')}" class="btn btn-sm btn-success">Edit</a>
                                <a href="/{$Deployment->getUrl('update')}" class="btn btn-sm btn-info">Update</a>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        {else}
            <p>Sorry, no deployments available.</p>
        {/if}
    </div>
</div>
{$total = Convergence\Deployment::getCount()}
{pagingLinks $total Convergence\DeploymentRequestHandler::$browseLimitDefault}        
{/block}
