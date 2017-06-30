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

{block search-form}
    <form action="/deployments" class="navbar-form navbar-right">
        <input type="text" name="q" class="form-control" placeholder="Search..." {if $.get.q}value="{$.get.q}"{/if}>
    </form>
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
        <div class="alert alert-success">{$Site->Handle} is Updating</div>
    {/if}
{/foreach}

<div class="row">
    <div class="col-sm-12">
        <div class="btn-inline">
            <form method="POST">
                <input type="submit" value="Update File System" class="btn btn-primary">
                <input type="hidden" name="action" value="vfs-update" />
            </form>
            <form method="POST">
                <input type="submit" value="Get Summary" class="btn btn-success">
                <input type="hidden" name="action" value="vfs-summary" />
            </form>
            <form method="POST">
                <input type="submit" value="Sync Pending Jobs" class="btn btn-info">
                <input type="hidden" name="action" value="jobs-sync" />
            </form>
        </div>
        {foreach item=Site from=$data->Sites}
            <h2>Site Jobs: {$Site->Handle}</h2>
            {$jobs = Convergence\Job::getAllByWhere(array('SiteID' => $Site->ID), array('limit' => 20, 'order' => 'ID DESC'))}
            {if $jobs}
                <table class="table table-condensed">
                    <thead>
                        <tr>
                            <th>UID</th>
                            <th>Status</th>
                            <th>Received</th>
                            <th>Started</th>
                            <th>Completed</th>
                            <th>Action</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach item=Job from=$jobs}
                            <tr data-toggle="collapse" data-target="#job{$Job->ID}" class="accordion-toggle">
                                <td>{$Job->UID}</td>
                                <td>{$Job->Status}</td>
                                <td>{date('g:i:s a', $Job->Received)}</td>
                                <td>{if $Job->Started}{date('g:i:s a', $Job->Started)}{/if}</td>
                                <td>{if $Job->Completed}{date('g:i:s a', $Job->Completed)}{/if}</td>
                                <td>{$Job->Action}</td>
                                <td><a href="#">Click for Results</td>
                            </tr>
                            <tr>
                                <td colspan="7" class="hiddenRow">
                                    <div class="accordian-body collapse" id="job{$Job->ID}">
                                        <h4>Command</h4>
                                        {dump $Job->Command}
                                        <h4>Result</h4>
                                        {dump $Job->Result}
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
