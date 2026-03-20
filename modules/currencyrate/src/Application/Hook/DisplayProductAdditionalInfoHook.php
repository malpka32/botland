<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Hook;

use CurrencyRate\Application\ProductRateTableBuilder;
use CurrencyRate\Application\View\ProductConvertedPriceViewMapper;

final class DisplayProductAdditionalInfoHook implements HookHandlerInterface
{
    public function __construct(
        private ProductRateTableBuilder $productRateTableBuilder,
        private ProductConvertedPriceViewMapper $productConvertedPriceViewMapper
    ) {
    }

    public function hookName(): string
    {
        return 'displayProductAdditionalInfo';
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function handle(array $payload): string
    {
        $productId = $this->extractProductId($payload);
        if ($productId <= 0) {
            return '';
        }

        $productAttributeId = isset($payload['product']['id_product_attribute'])
            ? (int) $payload['product']['id_product_attribute']
            : null;
        $rows = $this->productRateTableBuilder->buildForProduct($productId, $productAttributeId);
        if ($rows->count() === 0) {
            return '';
        }

        $context = \Context::getContext();
        if (!isset($context->smarty) || !is_object($context->smarty)) {
            return '';
        }

        $context->smarty->assign([
            'currencyrate_rows' => $this->productConvertedPriceViewMapper->mapCollection($rows),
        ]);

        return (string) $context->smarty->fetch('module:currencyrate/views/templates/hook/product_rates.tpl');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractProductId(array $payload): int
    {
        if (isset($payload['product']['id_product'])) {
            return (int) $payload['product']['id_product'];
        }

        if (isset($payload['product']->id_product)) {
            return (int) $payload['product']->id_product;
        }

        if (isset($payload['product']['id'])) {
            return (int) $payload['product']['id'];
        }

        return 0;
    }
}
