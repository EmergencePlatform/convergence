{extends designs/site.tpl}

{block content}
{load_templates subtemplates/paging.tpl}
<main role="main">
    <div class="container">
        <header class="page-header">
            <div class="btn-toolbar pull-right">
                <a href="/deployments/create">{_ "Add Deployment"}</a>
            </div>
            <h1>{_ "Deployments"}</h1>
        </header>
        <div class="row">
            <div class="col-sm-12">
                {if $data}
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach item=Deployment from=$data}
                                <tr>
                                    <td>{$Deployment->Label}</td>
                                    <td>{$Deployment->Status}</td>
                                    <td><a href="/{$Deployment->getUrl()}">View</a> / <a href="/{$Deployment->getUrl('edit')}">Edit</a></td>
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
    </div>
</main>
{/block}
