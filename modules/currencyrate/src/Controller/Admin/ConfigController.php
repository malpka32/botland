<?php

declare(strict_types=1);

namespace CurrencyRate\Controller\Admin;

use CurrencyRate\Application\History\RateHistorySynchronizer;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ConfigController extends FrameworkBundleAdminController
{
    private const DEBUG_LOG_ENABLED_KEY = 'CURRENCYRATE_DEBUG_LOG_ENABLED';

    public function __construct(private RateHistorySynchronizer $rateHistorySynchronizer)
    {
    }

    public function indexAction(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleSubmit($request);
        }

        $cronUrl = $this->buildCurrencyRatesCronUrl($request);

        return $this->render('@Modules/currencyrate/views/templates/admin/configuration.html.twig', [
            'layoutTitle' => $this->trans('Currency Rate', 'Modules.Currencyrate.Admin'),
            'layoutHeaderToolbarBtn' => [
                'help' => [
                    'href' => '#',
                    'desc' => $this->trans('Help', 'Modules.Currencyrate.Admin'),
                    'icon' => 'help_outline',
                    'class' => 'btn-outline-secondary js-currencyrate-help',
                ],
            ],
            'currencyrate_sync_status' => $request->query->get('sync_status'),
            'currencyrate_settings_status' => $request->query->get('settings_status'),
            'currencyrate_debug_log_enabled' => (string) \Configuration::get(self::DEBUG_LOG_ENABLED_KEY) === '1',
            'currencyrate_cron_url' => $cronUrl,
            'currencyrate_cron_example_command' => sprintf('curl "%s"', $cronUrl),
            'currencyrate_cron_docs_url' => 'https://devdocs.prestashop-project.org/9/faq/pricing/#refresh-exchange-rates',
            'currencyrate_currencies_url' => $this->generateUrl('admin_currencies_index'),
        ]);
    }

    private function handleSubmit(Request $request): RedirectResponse
    {
        if ($request->request->has('submitCurrencyRateSettings')) {
            $enabled = $request->request->get('currencyrate_debug_log_enabled') === '1' ? '1' : '0';
            \Configuration::updateValue(self::DEBUG_LOG_ENABLED_KEY, $enabled);

            return $this->redirectToRoute('currencyrate_admin_config', ['settings_status' => 'saved']);
        }

        if ($request->request->has('submitCurrencyRateSync')) {
            try {
                $this->rateHistorySynchronizer->syncLastThirtyDays();

                return $this->redirectToRoute('currencyrate_admin_config', ['sync_status' => 'success']);
            } catch (\Throwable $exception) {
                \PrestaShopLogger::addLog('[currencyrate] ' . $exception->getMessage(), 3);

                return $this->redirectToRoute('currencyrate_admin_config', ['sync_status' => 'error']);
            }
        }

        return $this->redirectToRoute('currencyrate_admin_config');
    }

    private function buildCurrencyRatesCronUrl(Request $request): string
    {
        $adminDirectoryName = basename((string) _PS_ADMIN_DIR_);
        $secureKey = md5(_COOKIE_KEY_ . (string) \Configuration::get('PS_SHOP_NAME'));
        $shopBaseUri = trim((string) __PS_BASE_URI__, '/');
        $shopBasePath = $shopBaseUri === '' ? '' : '/' . $shopBaseUri;

        return sprintf(
            '%s%s/%s/cron_currency_rates.php?secure_key=%s',
            $request->getSchemeAndHttpHost(),
            $shopBasePath,
            $adminDirectoryName,
            $secureKey
        );
    }
}
