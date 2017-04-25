{extends designs/convergence.tpl}

{block content}
{load_templates subtemplates/paging.tpl}
<div class="page-header">
    <div class='btn-toolbar pull-right'>
        <div class='btn-group'>
            <a href="/sites/create" class="btn btn-primary">{_ "Add Site"}</a>
        </div>
    </div>
    <h1>{_ "Sites"}</h1>
</div>
<div class="row">
    <div class="col-sm-12">
        {if $data}
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Handle</th>
                        <th>Primary Hostname</th>
                        <th>Parent Handle</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach item=Site from=$data}
                        <tr>
                            <td><a href="/sites/{$Site->Handle}">{$Site->Handle}</a></td>
                            <td><a href="//{$Site->PrimaryHostname->Hostname}" target="_blank">{$Site->PrimaryHostname->Hostname}</a></td>
                            <td>{if $Site->ParentSite}{$Site->ParentSite->Handle}{else}N/A{/if}</td>
                            <td>
                                {if $Site->Updating == 1}
                                    Updating
                                {elseif $Site->ParentSite}
                                    {if $Site->ParentSite->LocalCursor !== $Site->ParentCursor}
                                        <p class="text-danger">Out of Date</p>
                                    {else}
                                        <p class="text-success">Synced</p>
                                    {/if}
                                {else}
                                    N/A
                                {/if}
                            </td>
                            <td class="text-right"><a href="/sites/{$Site->ID}/update" class="btn btn-sm btn-primary">View Updates</a></td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
            {$total = Convergence\Site::getCount()}
            {pagingLinks $total Convergence\SitesRequestHandler::$browseLimitDefault}
        {else}
            <p>No sites available.</p>
        {/if}
    </div>
</div>
{/block}
