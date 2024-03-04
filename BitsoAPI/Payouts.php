<?php

namespace BitsoAPI;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Payouts
{
    private Client $client;

    private HttpClientInterface $httpClient;

    private Bitso $bitso;

    public function __construct(Client $client, Bitso $bitso)
    {
        $this->client = $client;
        $this->httpClient = HttpClient::create([
            'base_uri' => Bitso::URL,
        ]);
        $this->bitso = $bitso;
    }

    /**
     * @see https://docs.bitso.com/bitso-payouts-funding/docs/list-your-withdrawals#query-parameters
     *
     * @throws \JsonException
     */
    public function withdrawals(
        $withdrawal_id = null,
        $origin_id = null,
        $status = null,
        $limit = 25,
        $method = null,
        $marker = null
    ): array {
        $params = [
            'withdrawal_id' => $withdrawal_id,
            'origin_id' => $origin_id,
            'status' => $status,
            'limit' => $limit,
            'method' => $method,
            'marker' => $marker,
        ];

        $parameters = http_build_query($params, '', '&');

        $requestPath = '/api/v3/withdrawals?'.$parameters;

        return $this->client->getData($requestPath)['payload'];
    }

    public function withdrawalMethods($currency = ''): array
    {
        return $this->client->getData('/api/v3/withdrawal_methods/'.$currency)['payload'];
    }
}
