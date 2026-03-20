<div id="js-product-list-top" class="products-selection">
  <div class="col-12">
    <form method="get" class="currencyrate-filters-form">
      <div class="row">
        <div class="col-lg-3">
          <label for="currencyrate-date-from">{l s='Date from' d='Modules.Currencyrate.Shop'}</label>
          <input
            id="currencyrate-date-from"
            class="form-control"
            type="date"
            name="date_from"
            value="{$currencyrate_filters.date_from|default:''|escape:'htmlall':'UTF-8'}"
          >
        </div>
        <div class="col-lg-3">
          <label for="currencyrate-date-to">{l s='Date to' d='Modules.Currencyrate.Shop'}</label>
          <input
            id="currencyrate-date-to"
            class="form-control"
            type="date"
            name="date_to"
            value="{$currencyrate_filters.date_to|default:''|escape:'htmlall':'UTF-8'}"
          >
        </div>
        <div class="col-lg-2">
          <label for="currencyrate-currency">{l s='Currency' d='Modules.Currencyrate.Shop'}</label>
          <select id="currencyrate-currency" class="form-control" name="currency" size="">
            <option value="">{l s='All' d='Modules.Currencyrate.Shop'}</option>
            {foreach from=$currencyrate_currency_options item=option}
              <option
                value="{$option.iso_code|escape:'htmlall':'UTF-8'}"
                {if $currencyrate_filters.currency === $option.iso_code}selected{/if}
              >
                {$option.iso_code|escape:'htmlall':'UTF-8'} - {$option.name|escape:'htmlall':'UTF-8'}
              </option>
            {/foreach}
          </select>
        </div>
        <div class="col-lg-2 currencyrate-filter-actions">
          <button type="submit" class="btn btn-primary">{l s='Filter' d='Modules.Currencyrate.Shop'}</button>
        </div>
        <div class="col-lg-2 currencyrate-filter-actions">
          <a
            class="btn btn-secondary {if !$currencyrate_filters.has_active_filters}disabled{/if}"
            href="{$link->getModuleLink('currencyrate','history')|escape:'htmlall':'UTF-8'}"
          >
            {l s='Reset' d='Modules.Currencyrate.Shop'}
          </a>
        </div>
      </div>
    </form>
  </div>
</div>
