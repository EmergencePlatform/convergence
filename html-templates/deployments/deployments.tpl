{extends designs/convergence.tpl}

{block content}
{load_templates subtemplates/paging.tpl}
<div class="page-header">
    <div class='btn-toolbar pull-right'>
        <div class='btn-group'>
            <a href="/deployments/create" class="btn btn-primary">{_ "Add Deployment"}</a>
        </div>
    </div>
    <h1>{_ "Deployments"}</h1>
</div>

{$progress = \Convergence\Site::getUpdateProgress()}
<div class="update-complete alert alert-success alert-dismissible fade in hidden" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
    <strong>Update Complete</strong> <a href="/deployments">Refresh results</a>
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
    <div class="col-sm-4 vm-md">
        <div class="form-inline">
            <label class="sr-only" for="inlineFormInput">Name</label>
            <select class="form-control update-level">
                <option value="0">All Sites</option>
                <option value="1">Staging</option>
                <option value="2">Production</option>
            </select>
            <button type="submit" class="btn btn-primary" data-toggle="modal" data-target="#updateAllModal">Update Sites</button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        {if $data}
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Status</th>
                        <th>Sites</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach item=Deployment from=$data}
                        <tr>
                            <td>{$Deployment->Label}</td>
                            <td>{$Deployment->Status}</td>
                            <td>{count($Deployment->Sites)}</td>
                            <td class="text-right">
                                <a href="/{$Deployment->getUrl()}" class="btn btn-sm btn-primary">View</a>
                                <a href="/{$Deployment->getUrl('edit')}" class="btn btn-sm btn-success">Edit</a>
                                <a href="/{$Deployment->getUrl('update')}" class="btn btn-sm btn-info">Update</a>
                            </td>
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
{/block}

{block modals}
<div class="modal fade" id="updateAllModal" tabindex="-1" role="dialog" aria-labelledby="updateAllModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="updateAllModalLabel">Are You Sure?</h4>
            </div>
            <div class="modal-body">
                Are you sure you want to update all sites? Once started this update can't be stopped.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="StartSystemUpdate">Let's Do It!</button>
            </div>
        </div>
    </div>
</div>
{/block}

{block js-bottom}
    {$dwoo.parent}
    <script>
        var progressTimer;

        // Fetch poll progress from the server
        var pollProgress = function() {
            $.ajax({
                type: 'POST',
                url: '/sites/update-status'
            }).done(function(data) {
                setProgress(data.updating);
                if (data.updating == 100) {
                    $('.update-complete').removeClass('hidden');
                }
            });
        };

        // Update set progress UI
        var setProgress = function(progress) {
            $('.progress-row').removeClass('hidden');
            $('.progress-bar').css('width', progress + '%').text(progress + '%').attr('aria-valuenow', progress);
        };

        // Auto start progress timer on page load
        {if $progress != 100}
            progressTimer = window.setInterval(pollProgress, 1000);
        {/if}

        $(document).ready(function() {
            $('#StartSystemUpdate').click(function(e,t) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: '/deployments/update',
                    data: {
                        level: $('.update-level').val()
                    }
                }).done(function(data) {
                    if (typeof(progressTimer) == "undefined") {
                        progressTimer = window.setInterval(pollProgress, 1000);
                        setProgress(0);
                    }
                });
                $('#updateAllModal').modal('hide');
            });
        });
    </script>
{/block}