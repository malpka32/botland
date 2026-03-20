{if $currencyrate_sync_status === 'success'}
  <div class="alert alert-success" role="alert">
    {l s='Rates and history from last 30 days were synchronized.' d='Modules.Currencyrate.Admin'}
  </div>
{elseif $currencyrate_sync_status === 'error'}
  <div class="alert alert-danger" role="alert">
    {l s='Could not synchronize rates. Check logs for details.' d='Modules.Currencyrate.Admin'}
  </div>
{/if}

{if $currencyrate_settings_status === 'saved'}
  <div class="alert alert-success" role="alert">
    {l s='Settings saved.' d='Modules.Currencyrate.Admin'}
  </div>
{/if}

<form method="post" class="mb-3">
  <div class="form-group">
    <label for="currencyrate-debug-log-enabled" class="form-control-label">
      {l s='Debug logging' d='Modules.Currencyrate.Admin'}
    </label>
    <div class="switch prestashop-switch fixed-width-lg">
      <input
        id="currencyrate-debug-log-enabled-on"
        type="radio"
        name="currencyrate_debug_log_enabled"
        value="1"
        {if $currencyrate_debug_log_enabled}checked{/if}
      >
      <label for="currencyrate-debug-log-enabled-on">{l s='Yes' d='Admin.Global'}</label>
      <input
        id="currencyrate-debug-log-enabled-off"
        type="radio"
        name="currencyrate_debug_log_enabled"
        value="0"
        {if !$currencyrate_debug_log_enabled}checked{/if}
      >
      <label for="currencyrate-debug-log-enabled-off">{l s='No' d='Admin.Global'}</label>
      <a class="slide-button btn"></a>
    </div>
    <p class="help-block">
      {l s='Enable detailed debug logs for NBP endpoints, cache usage and table reads/writes.' d='Modules.Currencyrate.Admin'}
    </p>
  </div>
  <button class="btn btn-outline-primary" type="submit" name="submitCurrencyRateSettings">
    {l s='Save settings' d='Modules.Currencyrate.Admin'}
  </button>
</form>

<form method="post">
  <button class="btn btn-primary" type="submit" name="submitCurrencyRateSync">
    {l s='Download rates from last 30 days' d='Modules.Currencyrate.Admin'}
  </button>
</form>
