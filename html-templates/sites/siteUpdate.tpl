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
            <a href="/{$data->getUrl()}" class="btn btn-primary">{_ "View Site"}</a>
            <a href="/{Convergence\Site::$collectionRoute}" class="btn btn-success">{_ "All Sites"}</a>
        </div>
    </div>
    <h1>{$data->Label} File System</h1>
</div>

{if $data->Updating == true}
    <div class="alert alert-info">{_ "Site is updating"}</div>
{/if}

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
        <h2>Jobs</h2>
        {$jobs = Convergence\Job::getAllByWhere(array('SiteID' => $data->ID), array('limit' => 20, 'order' => 'ID DESC'))}
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
    </div>
</div>
{/block}