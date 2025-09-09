<?php

namespace YoApy\SDK\Http;

interface TransportInterface
{
    /**
     * @param string $method
     * @param string $url
     * @param array<string, string> $headers
     * @param string|null $body
     * @param int $timeoutSec
     * @return HttpResponse
     */
    public function request(string $method, string $url, array $headers = [], ?string $body = null, int $timeoutSec = 30): HttpResponse;
}
