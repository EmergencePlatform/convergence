{extends designs/convergence.tpl}

{block title}{$data->Label} | {$dwoo.parent}{/block}

{block content}
{if !$summary}
    {$summary = $Site->getFileSystemSummary()}
{else}
    {$updated = true}
{/if}

<div class="page-header">
    <div class='btn-toolbar pull-right'>
        <div class='btn-group'>
            <a href="/{$data->getUrl()}" class="btn btn-primary">{_ "View Site"}</a>
            <a href="/{Convergence\Site::$collectionRoute}" class="btn btn-success">{_ "All Sites"}</a>
        </div>
    </div>
    <h1>{$data->Label} File System</h1>
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
        <div>
            <h2>New Files</h2>
            {dump $summary['new']}
            <h2>Updated Files</h2>
            {dump $summary['updated']}
            <h2>Deleted Files</h2>
            {dump $summary['deleted']}
        </div>
    </div>
</div>
{/block}