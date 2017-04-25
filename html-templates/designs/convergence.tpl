<!DOCTYPE html>
{load_templates designs/site.subtemplates.tpl}

<html class="no-js" lang="en-us">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{block title}Convergence{/block}</title>
    <meta name="description" content="Convergence HQ Panel">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    
    {literal}
        <style>
            body {
                padding-top: 50px;
            }
            .vm-md {
                margin-top: 1.5em;
                margin-bottom: 1.5em;
            }
        </style>
    {/literal}
    {block styles}{/block}
</head>

<body data-gr-c-s-loaded="true">
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href="/deployments">Convergence</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li><a href="/hosts">Hosts</a></li>
                    <li><a href="/deployments">Deployments</a></li>
                    <li><a href="/sites">Sites</a></li>
                </ul>
                <form class="navbar-form navbar-right">
                    <input type="text" name="q" class="form-control" placeholder="Search by handle..." {if $.get.q}value="{$.get.q}"{/if}>
                </form>
            </div>
        </div>
    </nav>
    <div class="container" role="main">
        {block content}{/block}
    </div>
    {block modals}{/block}
    {block js-bottom}
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    {/block}
</body>
</html>