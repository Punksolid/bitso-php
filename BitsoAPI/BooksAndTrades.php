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
    private string $url = '';

    public function __construct(Client $client, Bitso $bitso)
    {
        $this->client = $client;
        $this->httpClient = HttpClient::create([
            'base_uri' => Bitso::URL,
        ]);
        $this->bitso = $bitso;
    }

    /**
     * The method GET /available_books/ enables you to retrieve a list of all available books in the Bitso Exchange.
     *
     * @see https://docs.bitso.com/bitso-api/docs/list-available-books
     *
     * @return array
     */
    public function availableBooks(): array
    {
        $result = $this->client->getData('/api/v3/available_books/');

        return $result['payload'];
    }

    /**
     * Fetches trades from the Bitso API.
     *
     * @param array $params $params  An associative array containing the following keys:
     *                      - 'book' (string):  Specifies which book to use. This is a required parameter.
     *                      - 'limit' (int):  Specifies the number of objects to return. Maximum is 100. Default is 25.
     *                      - 'marker' (string):  Specifies to return older or newer objects (depending on the value of the sort parameter) than the object with the given ID.
     *                      - 'sort' (string):  Specifies the ordering direction of returned objects. Valid values: 'asc', 'desc'. Default is 'desc'.
     * @see https://docs.bitso.com/bitso-api/docs/list-trades
     *
     * @return array The trades fetched from the Bitso API.
     * @throws JsonException If there is an error decoding the JSON response from the Bitso API.
     */
    public function trades(array $params)
    {
        $parameters = http_build_query($params, '', '&');
        $path = $this->url.'/api/v3/trades/?'.$parameters;
        $result = $this->client->getData($path);

        return $result['payload'];
    }

    /**
     * The method GET /ticker/ enables you to retrieve trading information from the specified book.
     *
     * @see https://docs.bitso.com/bitso-api/docs/ticker
     *
     * @return array
     *
     * @throws JsonException
     */
    public function ticker($book)
    {
        $book_query = http_build_query(['book' => $book], '', '&');
        $path = '/api/v3/ticker?'.$book_query;

        $result = $this->client->getData($path);

        return $result['payload'];
    }

    //#####          #######
    //##### PUBLIC QUERIES #######
    //#####          #######

    /**
     *The method GET /order_book/ enables you to retrieve a list of all open orders in the specified book. The value of the aggregate query parameter determines the response returned:
     *
     * True: The service aggregates the orders by price, and the response includes only the top 50 orders for each side of the book. It is the default behavior.
     * False: The response consists of the whole order book.
     *
     * @see https://docs.bitso.com/bitso-api/docs/list-order-book
     *
     * @return mixed
     *
     * @throws JsonException
     * @throws \ErrorException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function orderBook($params)
    {
        $parameters = http_build_query($params, '', '&');
        $path = $this->url.'/api/v3/order_book/?'.$parameters;
        $type = 'PUBLIC';
        $HTTPMethod = 'GET';
        $result = Client::urlRequest($this, $type, $path, $HTTPMethod, '', '');

        return Client::checkAndDecode($result);
    }
}
