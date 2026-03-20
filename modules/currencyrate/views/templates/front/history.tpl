{extends file='page.tpl'}

{block name='page_title'}
  {l s='Currency rates history - last 30 days' d='Modules.Currencyrate.Shop'}
{/block}

{block name='page_content'}
  <section class="currencyrate-block">
    {include file='module:currencyrate/views/templates/front/_partials/history-top.tpl'}
    {include file='module:currencyrate/views/templates/front/_partials/history-list.tpl'}
    {include file='module:currencyrate/views/templates/front/_partials/history-bottom.tpl'}
  </section>
{/block}
