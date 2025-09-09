<?php

namespace YoApy\SDK\Http;

class HttpResponse
{
    public int $status;
    public string $body;
    /** @var array<string, string|string[]> */
    public array $headers;

    public function __construct(int $status, string $body, array $headers = [])
    {
        $this->status = $status;
        $this->body   = $body;
        $this->headers= $headers;
    }
}
