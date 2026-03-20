<section class="currencyrate-block">
  <h4 class="currencyrate-title">{l s='Product price in available currencies' d='Modules.Currencyrate.Shop'}</h4>
  <div class="table-responsive">
    <table class="table table-striped table-sm currencyrate-table">
      <thead>
        <tr>
          <th>{l s='Currency' d='Modules.Currencyrate.Shop'}</th>
          <th>{l s='Price' d='Modules.Currencyrate.Shop'}</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$currencyrate_rows item=row}
          <tr>
            <td>{$row.name|escape:'htmlall':'UTF-8'}</td>
            <td>{$row.formatted_price|escape:'htmlall':'UTF-8'}</td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  </div>
  <div class="currencyrate-history-link">
    <a href="{$link->getModuleLink('currencyrate','history')|escape:'htmlall':'UTF-8'}">
      {l s='View exchange rates history' d='Modules.Currencyrate.Shop'}
    </a>
  </div>
</section>
