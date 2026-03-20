<?php

declare(strict_types=1);

use CurrencyRate\Application\History\HistoryViewProvider;
use CurrencyRate\Application\View\HistoryRateRowViewMapper;

class CurrencyRateHistoryModuleFrontController extends ModuleFrontController
{
    private const DEFAULT_PER_PAGE = 20;

    public function setMedia(): void
    {
        parent::setMedia();

        $this->addCss($this->module->getPathUri() . 'views/css/history.css');
    }

    public function initContent(): void
    {
        parent::initContent();

        /** @var CurrencyRate $module */
        $module = $this->module;
        $historyProvider = $module->get(HistoryViewProvider::class);
        if (!$historyProvider instanceof HistoryViewProvider) {
            throw new \RuntimeException(sprintf('Service "%s" is not available.', HistoryViewProvider::class));
        }
        $currencyOptions = $historyProvider->getCurrencyOptions();
        $allowedCurrencyCodes = array_map(static function (array $option): string {
            return (string) $option['iso_code'];
        }, $currencyOptions);

        $filters = $this->resolveFilters($allowedCurrencyCodes);
        $rowsCollection = $historyProvider->getLastThirtyDays(
            $filters['date_from'],
            $filters['date_to'],
            $filters['currency']
        );
        $historyRateRowViewMapper = $module->get(HistoryRateRowViewMapper::class);
        if (!$historyRateRowViewMapper instanceof HistoryRateRowViewMapper) {
            throw new \RuntimeException(sprintf('Service "%s" is not available.', HistoryRateRowViewMapper::class));
        }
        $rows = $historyRateRowViewMapper->mapCollection($rowsCollection);
        $totalItems = count($rows);
        $perPage = self::DEFAULT_PER_PAGE;
        $pagesCount = max(1, (int) ceil($totalItems / $perPage));
        $currentPage = max(1, (int) Tools::getValue('page', 1));
        if ($currentPage > $pagesCount) {
            $currentPage = $pagesCount;
        }

        $offset = ($currentPage - 1) * $perPage;
        $pagedRows = array_slice($rows, $offset, $perPage);
        $pagination = $this->buildPagination($currentPage, $pagesCount, $perPage, $totalItems);

        $this->context->smarty->assign([
            'currencyrate_rows' => $pagedRows,
            'pagination' => $pagination,
            'currencyrate_filters' => $filters,
            'currencyrate_currency_options' => $currencyOptions,
        ]);

        $isAjaxRequest = Tools::getIsset('from-xhr');
        if ($isAjaxRequest) {
            $renderedTop = (string) $this->context->smarty->fetch(
                'module:currencyrate/views/templates/front/_partials/history-top.tpl'
            );
            $renderedList = (string) $this->context->smarty->fetch(
                'module:currencyrate/views/templates/front/_partials/history-list.tpl'
            );
            $renderedBottom = (string) $this->context->smarty->fetch(
                'module:currencyrate/views/templates/front/_partials/history-bottom.tpl'
            );

            $payload = json_encode([
                'current_url' => $this->updateQueryString(['from-xhr' => null]),
                'rendered_products_top' => $renderedTop,
                'rendered_products' => $renderedList,
                'rendered_products_bottom' => $renderedBottom,
            ]);

            $this->ajaxRender((string) $payload);
            exit;
        }

        $this->setTemplate('module:currencyrate/views/templates/front/history.tpl');
    }

    /**
     * @param list<string> $allowedCurrencyCodes
     *
     * @return array{date_from: ?string, date_to: ?string, currency: ?string, has_active_filters: bool}
     */
    private function resolveFilters(array $allowedCurrencyCodes): array
    {
        $defaultDateTo = \CurrencyRate\Application\History\HistoryDateRange::today();
        $defaultDateFrom = \CurrencyRate\Application\History\HistoryDateRange::defaultStartDateFrom($defaultDateTo);

        $dateFrom = $this->sanitizeDate((string) Tools::getValue('date_from', ''));
        $dateTo = $this->sanitizeDate((string) Tools::getValue('date_to', ''));

        if ($dateFrom === null) {
            $dateFrom = $defaultDateFrom->format('Y-m-d');
        }
        if ($dateTo === null) {
            $dateTo = $defaultDateTo->format('Y-m-d');
        }

        if ($dateFrom !== null && $dateTo !== null && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $currency = \CurrencyRate\Application\Support\CurrencyIsoCode::normalize((string) Tools::getValue('currency', ''));
        if ($currency === '' || !in_array($currency, $allowedCurrencyCodes, true)) {
            $currency = null;
        }

        $hasActiveFilters = (Tools::getIsset('date_from'))
            || (Tools::getIsset('date_to'))
            || (Tools::getIsset('currency'));

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'currency' => $currency,
            'has_active_filters' => $hasActiveFilters,
        ];
    }

    private function sanitizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if (!$date instanceof \DateTimeImmutable || $date->format('Y-m-d') !== $value) {
            return null;
        }

        return $value;
    }

    /**
     * @return array{
     *   total_items:int,
     *   items_shown_from:int,
     *   items_shown_to:int,
     *   current_page:int,
     *   pages_count:int,
     *   pages:array<int, array<string,mixed>>,
     *   should_be_displayed:bool
     * }
     */
    private function buildPagination(int $currentPage, int $pagesCount, int $perPage, int $totalItems): array
    {
        $pagination = new \PrestaShop\PrestaShop\Core\Product\Search\Pagination();
        $pagination
            ->setPage($currentPage)
            ->setPagesCount($pagesCount);

        $links = array_map(function (array $link): array {
            $link['url'] = $this->updateQueryString([
                'page' => $link['page'] > 1 ? $link['page'] : null,
            ]);

            return $link;
        }, $pagination->buildLinks());

        $links = array_values(array_filter($links, static function (array $page) use ($pagination): bool {
            if ('previous' === $page['type'] && 1 === $pagination->getPage()) {
                return false;
            }
            if ('next' === $page['type'] && $pagination->getPagesCount() === $pagination->getPage()) {
                return false;
            }

            return true;
        }));

        if ($totalItems <= 0) {
            $itemsFrom = 0;
            $itemsTo = 0;
        } else {
            $itemsFrom = (($currentPage - 1) * $perPage) + 1;
            $itemsTo = min($currentPage * $perPage, $totalItems);
        }

        return [
            'total_items' => $totalItems,
            'items_shown_from' => $itemsFrom,
            'items_shown_to' => $itemsTo,
            'current_page' => $currentPage,
            'pages_count' => $pagesCount,
            'pages' => $links,
            'should_be_displayed' => count($pagination->buildLinks()) > 3,
        ];
    }
}
