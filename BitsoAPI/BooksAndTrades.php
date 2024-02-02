<?php

namespace BitsoAPI;

use JsonException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BooksAndTrades
{

    protected Client $client;
    private HttpClientInterface $httpClient;
    private Bitso $bitso;

    public function __construct(Client $client, Bitso $bitso) {
        $this->client = $client;
        $this->httpClient = HttpClient::create([
            'base_uri' => Bitso::URL,
        ]);
        $this->bitso = $bitso;
    }

    public function availableBooks(): array
    {
        $result = $this->client->getData('/api/v3/available_books/');

        return $result['payload'];
    }

    public function trades($params)
    {
        $parameters = http_build_query($params, '', '&');
        $path = $this->url . '/api/v3/trades/?' . $parameters;
        $type = 'PUBLIC';
        $HTTPMethod = 'GET';
        $result = Client::urlRequest($this, $type, $path, $HTTPMethod, '', '');

        return Client::checkAndDecode($result);
    }

    /**
     * The method GET /ticker/ enables you to retrieve trading information from the specified book.
     *
     * @see https://docs.bitso.com/bitso-api/docs/ticker
     * @param $book
     * @return array
     * @throws JsonException
     */
    public function ticker($book)
    {
        $book_query = http_build_query(['book' => $book], '', '&');
        $path = '/api/v3/ticker?' . $book_query;

        $result = $this->client->getData($path);

        return $result['payload'];
    }
}
