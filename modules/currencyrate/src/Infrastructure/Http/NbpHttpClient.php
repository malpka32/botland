<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Http;

use CurrencyRate\Infrastructure\Log\CurrencyRateDebugLogger;

final class NbpHttpClient
{
    private const BASE_URL = 'https://api.nbp.pl';
    private const TIMEOUT = 10;

    public function get(string $path): string
    {
        $url = self::BASE_URL . $path;
        CurrencyRateDebugLogger::log('NBP HTTP request started', ['path' => $path, 'url' => $url]);
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => self::TIMEOUT,
                'header' => "Accept: application/json\r\n",
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false || $response === '') {
            CurrencyRateDebugLogger::log('NBP HTTP request failed', ['url' => $url]);
            throw new \RuntimeException(sprintf('NBP request failed for "%s".', $url));
        }

        CurrencyRateDebugLogger::log('NBP HTTP request succeeded', [
            'url' => $url,
            'response_length' => strlen($response),
        ]);

        return $response;
    }
}
