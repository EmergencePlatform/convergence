{template pagingLinks total pageSize=12 showAll=false}
    <nav aria-label="Page navigation">
        {if $total > $pageSize}
            <ul class="pagination">
                {$previousOffset = tif($.get.offset && $.get.offset > $pageSize ? $.get.offset - $pageSize : 0)}
                {$nextOffset = tif($.get.offset ? $.get.offset + $pageSize : $pageSize)}
                {if $.get.offset > 0}
                    <li>
                        <a href="?{refill_query limit=$pageSize offset=$previousOffset}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                {/if}
                {foreach item=page from=range(1,ceil($total/$pageSize))}
                    {math "($page-1)*$pageSize" assign=offset}
                    {if $.get.offset == $offset}
                        <li class="active">
                            <a href="?{refill_query limit=$pageSize offset=$offset}">{$page} <span class="sr-only">(current)</span></a>
                        </li>
                    {else}
                        <li>
                            <a href="?{refill_query limit=$pageSize offset=$offset}">{$page}</a>
                        </li>
                    {/if}
                {/foreach}
                {if $.get.offset < $total - $pageSize}
                    <li>
                        <a href="?{refill_query limit=$pageSize offset=$nextOffset}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                {/if}
            </ul>
        {/if}
    </nav>
{/template}