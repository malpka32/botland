<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'CurrencyRate\\Tests\\' => __DIR__ . '/',
        'CurrencyRate\\' => dirname(__DIR__) . '/src/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $path = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (is_file($path)) {
            require $path;
        }

        return;
    }
});

if (!class_exists('Configuration')) {
    final class Configuration
    {
        /** @var array<string, string> */
        public static array $values = [];

        public static function get(string $key): string
        {
            return self::$values[$key] ?? '';
        }
    }
}

if (!class_exists('Context')) {
    final class Context
    {
        public object $language;
        public mixed $shop = null;
        public mixed $currency = null;
        public mixed $currentLocale = null;
        private static ?self $instance = null;

        private function __construct()
        {
            $this->language = (object) ['id' => 1];
        }

        public static function getContext(): self
        {
            if (self::$instance === null) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        public function getCurrentLocale(): mixed
        {
            return $this->currentLocale;
        }
    }
}

if (!class_exists('Currency')) {
    class Currency
    {
        public mixed $id = null;
        public mixed $iso_code = null;
        public mixed $symbol = null;
        public mixed $name = null;
        public mixed $conversion_rate = null;

        /** @var list<mixed> */
        public static array $currencies = [];
        public static ?self $defaultCurrency = null;

        public static function getCurrencies(bool $active = true, bool $idLang = false, bool $all = true): array
        {
            return self::$currencies;
        }

        public static function getDefaultCurrency(): ?self
        {
            return self::$defaultCurrency;
        }
    }
}

if (!class_exists('Product')) {
    final class Product
    {
        public static float $price = 0.0;

        public static function getPriceStatic(
            int $productId,
            bool $usetax = true,
            ?int $idProductAttribute = null,
            int $decimals = 6,
            ?int $divisor = null,
            bool $onlyReduc = false,
            bool $usereduc = true,
            int $quantity = 1,
            bool $forceAssociatedTax = false,
            ?int $idCustomer = null,
            ?int $idCart = null,
            ?int $idAddress = null,
            mixed &$specificPriceOutput = null,
            bool $withEcotax = true,
            bool $useGroupReduction = true,
            ?Context $context = null
        ): float {
            return self::$price;
        }
    }
}

if (!class_exists('PrestaShopLogger')) {
    final class PrestaShopLogger
    {
        /** @var list<array{message: string, severity: int}> */
        public static array $entries = [];

        public static function addLog(string $message, int $severity): void
        {
            self::$entries[] = ['message' => $message, 'severity' => $severity];
        }
    }
}
