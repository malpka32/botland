<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Http;

use CurrencyRate\Application\Log\DebugLoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class NbpHttpClient
{
    private const BASE_URL = 'https://api.nbp.pl';
    private const TIMEOUT = 10;

    private HttpClientInterface $httpClient;

    public function __construct(
        private DebugLoggerInterface $logger,
        ?HttpClientInterface $httpClient = null
    )
    {
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    public function get(string $path): string
    {
        $url = self::BASE_URL . $path;
        $this->logger->log('NBP HTTP request started', ['path' => $path, 'url' => $url]);

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => ['Accept' => 'application/json'],
                'timeout' => self::TIMEOUT,
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
            if ($statusCode < 200 || $statusCode >= 300 || $content === '') {
                $this->logger->log('NBP HTTP request failed', [
                    'url' => $url,
                    'status_code' => $statusCode,
                ]);
                throw new \RuntimeException(sprintf('NBP request failed for "%s" with status %d.', $url, $statusCode));
            }
        } catch (\Throwable $exception) {
            $this->logger->log('NBP HTTP request failed', [
                'url' => $url,
                'error' => $exception->getMessage(),
            ]);
            throw new \RuntimeException(sprintf('NBP request failed for "%s".', $url), 0, $exception);
        }

        $this->logger->log('NBP HTTP request succeeded', [
            'url' => $url,
            'response_length' => strlen($content),
        ]);

        return $content;
    }
}
