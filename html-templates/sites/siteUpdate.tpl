{extends designs/convergence.tpl}

{block title}{$data->Label} | {$dwoo.parent}{/block}

{block content}
    {if !$summary}
        {$summary = $Site->getFileSystemSummary()}
    {else}
        {$updated = true}
    {/if}
    <div class="container">
        <div class="row">
            <header class="page-header">
                <div class="btn-toolbar pull-right">
                    <a href="/{$data->getUrl()}">View Site</a> | <a href="/{$data->Deployment->getUrl()}"> View Deployment</a>
                    <form method="POST"><input type="submit" value="Update File System"></form>
                </div>
                <h1>{$data->Label} File System</h1>
            </header>
            {if $updated == true}
                <div class="well">
                    {_ "Site updated"}
                </div>
            {/if}
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