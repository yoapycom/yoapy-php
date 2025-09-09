<?php

namespace YoApy\SDK\Http;

class CurlTransport implements TransportInterface
{
    public function request(string $method, string $url, array $headers = [], ?string $body = null, int $timeoutSec = 30): HttpResponse
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new \RuntimeException('Failed to init cURL');
        }

        $hdrs = [];
        foreach ($headers as $k => $v) { $hdrs[] = $k . ': ' . $v; }

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => $timeoutSec,
            CURLOPT_HTTPHEADER     => $hdrs,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            $code = curl_errno($ch);
            curl_close($ch);
            throw new \RuntimeException('cURL error ' . $code . ': ' . $err);
        }

        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $headerPart = substr($raw, 0, $headerSize);
        $bodyPart   = substr($raw, $headerSize);

        $headersOut = [];
        foreach (explode("\r\n", trim($headerPart)) as $line) {
            if (stripos($line, 'HTTP/') === 0) { continue; }
            if (strpos($line, ':') !== false) {
                [$k, $v] = explode(':', $line, 2);
                $k = trim($k); $v = trim($v);
                if (isset($headersOut[$k])) {
                    if (is_array($headersOut[$k])) { $headersOut[$k][] = $v; }
                    else { $headersOut[$k] = [$headersOut[$k], $v]; }
                } else {
                    $headersOut[$k] = $v;
                }
            }
        }

        return new HttpResponse((int)$status, (string)$bodyPart, $headersOut);
    }
}
