{extends designs/site.tpl}

{block content-wrapper}
{load_templates subtemplates/paging.tpl}
<main role="main">
    <div class="container">
        <header class="page-header">
            <div class="btn-toolbar pull-right">
                <form action="/sites/create">
                    <button class="btn btn-success" type="submit">{glyph "plus"}&nbsp;{_ "Add Site&hellip;"}</button>
                </form>
            </div>
            <h1>{_ "Sites"}</span></h1>
        </header>
        {$progress = \Convergence\Site::getUpdateProgress()}
        <div class="update-complete alert alert-success alert-dismissible fade in hidden" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <strong>Update Complete</strong> <a href="/sites">Refresh results</a>
        </div>
        <div class="row progress-row {if $progress == 100}hidden{/if}">
            <div class="col-sm-12">
                <h3>Updating...</h3>
                <div class="progress">
                    <div class="progress-bar progress-bar-info progress-bar-striped"
                        role="progressbar" aria-valuenow="{$progress}" aria-valuemin="0"
                        aria-valuemax="100" style="width: {$progress}%">
                        {$progress}%
                    </div>
                </div>
            </div>
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
                                    <td><a href="/sites/{$Site->ID}/update">Update Site</a></td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                {/if}
            </div>
        </div>
        {$total = Convergence\Site::getCount()}
        {pagingLinks $total Convergence\SitesRequestHandler::$browseLimitDefault}        
    </div>
</main>
{/block}
