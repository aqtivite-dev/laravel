<?php

namespace Aqtivite\Laravel\Http;

use Aqtivite\Php\Contracts\HttpTransportInterface;
use Aqtivite\Php\Exceptions\AqtiviteException;
use Aqtivite\Php\Http\TransportResponse;
use Illuminate\Support\Facades\Http;

class LaravelTransport implements HttpTransportInterface
{
    public function send(string $method, string $url, array $options = []): TransportResponse
    {
        try {
            $request = Http::accept('application/json');

            if (isset($options['headers'])) {
                $request = $request->withHeaders($options['headers']);
            }

            if (isset($options['query'])) {
                $url .= '?' . http_build_query($options['query']);
            }

            if (isset($options['json'])) {
                $request = $request->asJson();
                $response = $request->send($method, $url, ['body' => json_encode($options['json'])]);
            } elseif (isset($options['form_params'])) {
                $request = $request->asForm();
                $response = $request->send($method, $url, ['body' => http_build_query($options['form_params'])]);
            } elseif (isset($options['multipart'])) {
                $request = $request->asMultipart();
                foreach ($options['multipart'] as $part) {
                    $request = $request->attach(
                        $part['name'],
                        $part['contents'],
                        $part['filename'] ?? null,
                    );
                }
                $response = $request->send($method, $url);
            } else {
                $response = $request->send($method, $url);
            }
        } catch (\Throwable $e) {
            throw new AqtiviteException(
                message: 'HTTP request failed: ' . $e->getMessage(),
                code: $e->getCode(),
                previous: $e,
            );
        }

        return new TransportResponse(
            statusCode: $response->status(),
            body: $response->json() ?? [],
        );
    }
}
