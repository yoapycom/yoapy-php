# YoApy PHP SDK (`yoapycom/yoapy-php`)

Framework-agnostic PHP client for the **YoApy** API (HMAC-signed, JSON-only).
Uses cURL by default (no external deps). Optional Guzzle transport included.

```
canonical = [METHOD, PATH, TIMESTAMP, NONCE, SHA256(body)].join('\n')
signature = base64( HMAC_SHA256(canonical, hex_to_bin(secret)) )
Headers: X-Key-Id, X-Timestamp, X-Nonce, X-Signature
```
> All POST requests are **JSON-only**. Files must be provided by URL via `media_urls`.

## Install (after publishing to Packagist)
```bash
composer require yoapycom/yoapy-php
```

## Requirements
- PHP 8.0+
- ext-json, ext-hash, ext-curl

## Quick start
```php
use YoApy\SDK\YoApyClient;

$client = new YoApyClient(
  'https://api.yoapy.com',
  'your_key_id',
  'your_secret_hex', // hex string, not base64
  null,              // transport (null => CurlTransport)
  30                 // timeout seconds
);

$ping = $client->authPing();

$res = $client->createPost([
  'account'     => 'yourhandle',
  'account_ids' => ['facebook'],
  'post_type'   => 'image',
  'text'        => "My title\n\nMy description",
  'media_urls'  => ['https://example.com/image.webp'],
  'article_url' => 'https://example.com/article',
]);

if (!empty($res['task_id'])) {
  $result = $client->getTaskResult($res['task_id']);
}
```

### Using Guzzle transport
```php
use YoApy\SDK\YoApyClient;
use YoApy\SDK\Http\GuzzleTransport;
use GuzzleHttp\Client as GuzzleClient;

$client = new YoApyClient(
  'https://api.yoapy.com',
  'your_key_id',
  'your_secret_hex',
  new GuzzleTransport(new GuzzleClient(['timeout' => 30])),
  30
);
```

## CLI helper
```
export YOAPY_BASE_URL=https://api.yoapy.com
export YOAPY_KEY_ID=your_key_id
export YOAPY_SECRET_HEX=your_secret_hex

vendor/bin/yoapy ping
vendor/bin/yoapy post '{"account":"yourhandle","account_ids":["facebook"],"post_type":"image","text":"Hi","media_urls":["https://.../image.webp"]}'
vendor/bin/yoapy task <task_id>
```

## Versioning
Initial release: v1.0.0

## License
MIT
