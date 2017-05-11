{extends designs/convergence.tpl}

{block title}{$data->Label} | {$dwoo.parent}{/block}

{block styles}
    {$dwoo.parent}
    {literal}
        <style>
            .btn-inline form {
                display: inline-block;
            }
            .hiddenRow {
                padding: 0 !important;
            }
            .table-condensed {
                border-collapse:collapse;
            }
        </style>
    {/literal}
{/block}

{block content}
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

{foreach item=Site from=$data->Sites}
    {if $Site->Updating == true}
        <div class="alert alert-success">{$Site->Handle} is Updateing</div>
    {/if}
{/foreach}

<div class="row">
    <div class="col-sm-12">
        <div class="btn-inline">
            <form method="POST">
                <input type="submit" value="Update File System" class="btn btn-primary">
                <input type="hidden" name="updatevfs" value="1" />
            </form>
            <form method="POST">
                <input type="submit" value="Get Summary" class="btn btn-success">
            </form>
            <a href="" class="btn btn-info">Refresh</a>
        </div>
        {foreach item=Site from=$data->Sites}
            <h2>Site: {$Site->Handle} - Jobs</h2>
            {if $jobs[$Site->ID] !== false}
                <table class="table table-condensed">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Status</th>
                            <th>Completed</th>
                            <th>Action</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach item=job from=$jobs[$Site->ID]['jobs']}
                            <tr data-toggle="collapse" data-target="#job{$dwoo.foreach.default.index}" class="accordion-toggle">
                                <td>{$job['uid']}</td>
                                <td>{$job['status']}</td>
                                <td>{date('g:i:s a Y-m-d', $job['completed']/1000)}</td>
                                <td>{$job['commands'][0]['action']}</td>
                                <td><a href="#">Click for Results</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="hiddenRow">
                                    <div class="accordian-body collapse" id="job{$dwoo.foreach.default.index}">
                                        {foreach item=command from=$job['commands']}
                                            <h4>Action: {$command['action']}</h4>
                                            {if $command['action'] == 'vfs-summary'}
                                                <h5>Local Cursor: {$command['result']['localCursor']}</h5>
                                                <h5>Parent Cursor: {$command['result']['parentCursor']}</h5>
                                                <h5>New Files</h5>
                                                {dump $command['result']['new']}
                                                <h5>Updated Files</h5>
                                                {dump $command['result']['updated']}
                                                <h5>Deleted Files</h5>
                                                {dump $command['result']['deleted']}
                                            {else}
                                                {dump $command}
                                            {/if}
                                        {/foreach}
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            {else}
                <p>No jobs</p>
            {/if}
        {/foreach}
    </div>
</div>
{/block}
