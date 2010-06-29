{if $pages->pageCount}
<div id="paginationControl">

    <!-- First page link -->
    {if $pages->previous}
      <a href="{$PAGE_URL}page={$pages->first}">
        Inicio
      </a> |
    {else}
      <span class="disabled">Inicio</span> |
    {/if}

    <!-- Numbered page links -->
    {foreach from=$pages->pagesInRange item=page}
        {if $page != $pages->current}
        <a href="{$PAGE_URL}page={$page}">
                {$page}
        </a> |
        {else}
            {$page} |
        {/if}
    {/foreach}

    <!-- Last page link -->
    {if $pages->next}
      <a href="{$PAGE_URL}page={$pages->last}">
        Fim
      </a>
    {else}
      <span class="disabled">Fim</span>
    {/if}
</div>
{/if}