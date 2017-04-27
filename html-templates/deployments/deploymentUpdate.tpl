{extends designs/convergence.tpl}

{block title}{$data->Label} | {$dwoo.parent}{/block}

{block content}
{if !$summary}
    {$summary = $data->getFileSystemSummary()}
{else}
    {$updated = true}
{/if}

<div class="page-header">
    <div class='btn-toolbar pull-right'>
        <div class='btn-group'>
            <a href="/{$data->getUrl()}" class="btn btn-primary">{_ "View Deployment"}</a>
            <a href="/{$data->getUrl('edit')}" class="btn btn-success">{_ "Edit Deployment"}</a>
            <a href="/{Convergence\Deployment::$collectionRoute}" class="btn btn-info">{_ "All Deployments"}</a>
        </div>
    </div>
    <h1>{_ "Deployment:"} {$data->Label}</h1>
</div>

{if $updated == true}
    <div class="alert alert-success">{_ "Site updated"}</div>
{/if}

<div class="row">
    <div class="col-sm-12">
        <div class='btn-group'>
            <form method="POST">
                <input type="submit" value="Update File System" class="btn btn-primary">
                <a href="/{$data->getUrl('update')}" class="btn btn-success">View Available Updates</a>
            </form>
        </div>
        {foreach item=siteSummary from=$summary}
            <div>
                <h2>Site: {$siteSummary['site']->Label} ({$siteSummary['site']->Handle})</h2>
                <h3>New Files</h3>
                {dump $siteSummary['results']['new']}
                <h3>Updated Files</h3>
                {dump $siteSummary['results']['updated']}
                <h3>Deleted Files</h3>
                {dump $siteSummary['results']['deleted']}
            </div>
        {foreachelse}
            <p>No sites to update.</p>
        {/foreach}
    </div>
</div>
{/block}
