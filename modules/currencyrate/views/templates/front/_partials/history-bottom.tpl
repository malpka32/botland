<div id="js-product-list-bottom">
  {if $currencyrate_rows|@count > 0}
    {include file='_partials/pagination.tpl' pagination=$pagination}
  {/if}
</div>
