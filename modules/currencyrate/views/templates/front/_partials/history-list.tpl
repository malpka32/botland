<div id="js-product-list">
  {if $currencyrate_rows|@count > 0}
    <div class="table-responsive">
      <table class="table table-bordered table-striped currencyrate-table">
        <thead>
          <tr>
            <th>{l s='Date' d='Modules.Currencyrate.Shop'}</th>
            <th>{l s='Currency' d='Modules.Currencyrate.Shop'}</th>
            <th>{l s='Code' d='Modules.Currencyrate.Shop'}</th>
            <th>{l s='NBP mid rate (PLN)' d='Modules.Currencyrate.Shop'}</th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$currencyrate_rows item=row}
            <tr>
              <td data-label="{l s='Date' d='Modules.Currencyrate.Shop'}">{$row.effective_date|escape:'htmlall':'UTF-8'}</td>
              <td data-label="{l s='Currency' d='Modules.Currencyrate.Shop'}">{$row.currency_name|escape:'htmlall':'UTF-8'}</td>
              <td data-label="{l s='Code' d='Modules.Currencyrate.Shop'}">{$row.iso_code|escape:'htmlall':'UTF-8'}</td>
              <td data-label="{l s='NBP mid rate (PLN)' d='Modules.Currencyrate.Shop'}">{$row.mid|string_format:"%.6f"}</td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  {else}
    <p class="alert alert-info">
      {l s='No historical rates found. Use module configuration button to import last 30 days.' d='Modules.Currencyrate.Shop'}
    </p>
  {/if}
</div>
