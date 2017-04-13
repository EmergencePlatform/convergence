{extends designs/site.tpl}

{block content}
{load_templates subtemplates/paging.tpl}
<main role="main">
    <div class="container">
        <header class="page-header">
            <div class="btn-toolbar pull-right">
                <a href="/hosts/create">{_ "Add Host"}</a>
            </div>
            <h1>{_ "Deployments"}</h1>
        </header>
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
                                    <td><a href="{$Host->getUrl()}">View</a> / <a href="{$Host->getUrl('edit')}">Edit</a></td>
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
