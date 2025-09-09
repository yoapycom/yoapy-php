<?php

namespace YoApy\SDK\Http;

use GuzzleHttp\Client;

class GuzzleTransport implements TransportInterface
{
    private Client $client;

    public function __construct(?Client $client = null) { $this->client = $client ?? new Client(); }

    public function request(string $method, string $url, array $headers = [], ?string $body = null, int $timeoutSec = 30): HttpResponse
    {
        $opts = ['headers' => $headers, 'timeout' => $timeoutSec];
        if ($body !== null) { $opts['body'] = $body; }
        $resp = $this->client->request(strtoupper($method), $url, $opts);
        return new HttpResponse($resp->getStatusCode(), (string)$resp->getBody(), $resp->getHeaders());
    }
}
