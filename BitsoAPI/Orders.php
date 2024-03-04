<?php

namespace BitsoAPI;

use Symfony\Component\HttpClient\HttpClient;

class Orders
{
    private Client $client;

    private \Symfony\Contracts\HttpClient\HttpClientInterface $httpClient;

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
     * @throws \JsonException
     */
    public function fees(): array
    {
        return $this->client->getData('/api/v3/fees/')['payload'];
    }

    public function userTrades($params = [], $ids = []): array
    {
        $id_nums = implode('', $ids);
        $parameters = http_build_query($params, '', '&');

        $requestPath = '/api/v3/user_trades/'.$id_nums.'/?'.$parameters;

        return $this->client->getData($requestPath)['payload'];
    }
}
