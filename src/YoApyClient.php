<?php

namespace YoApy\SDK;

use YoApy\SDK\Exceptions\YoApyException;
use YoApy\SDK\Http\TransportInterface;
use YoApy\SDK\Http\CurlTransport;

class YoApyClient
{
    private string $baseUrl;
    private string $keyId;
    private string $secretHex;
    private int $timeout;
    private TransportInterface $transport;

    public function __construct(
        string $baseUrl,
        string $keyId,
        string $secretHex,
        ?TransportInterface $transport = null,
        int $timeout = 30
    ) {
        $this->baseUrl   = rtrim($baseUrl, '/');
        $this->keyId     = $keyId;
        $this->secretHex = $secretHex;
        $this->timeout   = $timeout;
        $this->transport = $transport ?? new CurlTransport();
    }

    public function authPing(): array { return $this->get('/v1/auth_ping'); }

    public function createPost(array $payload): array { return $this->post('/v1/posts', $payload); }

    public function getTaskResult(string $taskId): array { return $this->get('/v1/get_task_result', ['task_id' => $taskId]); }

    private function get(string $path, array $query = []): array
    {
        $path = HmacSigner::normalizePath($path);
        $url  = $this->baseUrl . $path;
        if (!empty($query)) {
            $qs = http_build_query($query);
            $url .= (str_contains($url, '?') ? '&' : '?') . $qs;
        }

        $sig  = HmacSigner::sign('GET', $path, '', $this->secretHex);

        $headers = [
            'X-Key-Id'    => $this->keyId,
            'X-Timestamp' => (string)$sig['timestamp'],
            'X-Nonce'     => $sig['nonce'],
            'X-Signature' => $sig['signature'],
            'Accept'      => 'application/json',
        ];

        $resp = $this->transport->request('GET', $url, $headers, null, $this->timeout);
        return $this->handle($resp->status, $resp->body);
    }

    private function post(string $path, array $json): array
    {
        $path = HmacSigner::normalizePath($path);
        $url  = $this->baseUrl . $path;

        $body = json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($body === false) { throw new YoApyException('Failed to encode JSON body'); }

        $sig = HmacSigner::sign('POST', $path, $body, $this->secretHex);

        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'X-Key-Id'     => $this->keyId,
            'X-Timestamp'  => (string)$sig['timestamp'],
            'X-Nonce'      => $sig['nonce'],
            'X-Signature'  => $sig['signature'],
        ];

        $resp = $this->transport->request('POST', $url, $headers, $body, $this->timeout);
        return $this->handle($resp->status, $resp->body);
    }

    private function handle(int $status, string $body): array
    {
        if ($status >= 200 and $status < 300) {
            $data = json_decode($body, true);
            return is_array($data) ? $data : ['raw' => $body];
        }
        throw new YoApyException('YoApy API error: HTTP ' . $status . ' — ' . $body);
    }
}
