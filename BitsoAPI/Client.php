<?php

namespace BitsoAPI;

use ErrorException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client
{
    private HttpClientInterface $client;
    private string $key;
    private string $secret;
    private string $url;

    public function __construct(string $key, string $secret, string $url = 'https://bitso.com')
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->url = $url;
        $this->client = HttpClient::create([
            'base_uri' => $url,
        ]);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * function to perform curl url request depending on type and method
     */
    public static function urlRequest(
        Bitso  $instance,
        string $type,
        string $path,
        string $HTTPMethod,
        string $JSONPayload = '',
        string $authHeader = ''
    ): string
    {
        if ($type === 'PUBLIC') {
            $response = $instance->client->request('GET', $path);

            return $response->getContent();
        }

        if ($type === 'PRIVATE') {
            $options = ['headers' => ['Authorization' => $authHeader, 'Content-Type' => 'application/json']];

            if ($HTTPMethod === 'GET' || $HTTPMethod === 'DELETE') {
                $response = $instance->client->request($HTTPMethod, $path, $options);
            }

            if ($HTTPMethod === 'POST') {
                $response = $instance->client->request('POST', $path, $options + ['body' => $JSONPayload]);
            }
        }

        return $response->getContent();
    }

    public static function checkAndDecode($result)
    {
        $result = json_decode((string)$result, true, 512, JSON_THROW_ON_ERROR);
        if ($result['success'] !== true) {
            throw new ErrorException($result['error']['message'], 1);
        }

        return $result;
    }


    // All the other API methods...
    public static function getData(Bitso $instance, $path, $RequestPath, $HTTPMethod, $JSONPayload = ''): array
    {
        $nonce = self::makeNonce();
        $message = $nonce . $HTTPMethod . $RequestPath . $JSONPayload;
        $signature = hash_hmac('sha256', $message, (string)$instance->secret);
        $authHeader = sprintf('Bitso %s:%s:%s', $instance->key, $nonce, $signature);

        $result = $instance->client->request(
            $HTTPMethod,
            $instance->url . $RequestPath,
            [
                'headers' => ['Authorization' => $authHeader],
            ]
        );

        return json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    public static function makeNonce(): float
    {
        return round(microtime(true) * 1000);
    }

}
