<?php

declare(strict_types=1);

namespace BitsoAPI;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Bitso
{
    //constructor, default is dev url
    public const URL = 'https://bitso.com';

    public readonly HttpClientInterface $client;

    private Client $bitsoClient;

    public function __construct(public $key = '', public $secret = '', public $url = 'https://bitso.com')
    {
        $this->client = HttpClient::create(['base_uri' => $url]);
        $this->bitsoClient = new Client($key, $secret, $url);
    }

    public function accounts(): Accounts
    {
        return new Accounts($this->bitsoClient, $this);
    }

    public function orders(): Orders
    {
        return new Orders($this->bitsoClient, $this);
    }

    public function booksAndTrades(): BooksAndTrades
    {
        return new BooksAndTrades($this->bitsoClient, $this);
    }

    public function contacts(): Contacts
    {
        return new Contacts($this);
    }

    public function clabes(): Clabes
    {
        return new Clabes($this);
    }

    //#####          #######
    //##### PUBLIC QUERIES #######
    //#####          #######

    public function orderBook($params)
    {
        $parameters = http_build_query($params, '', '&');
        $path = $this->url.'/api/v3/order_book/?'.$parameters;
        $type = 'PUBLIC';
        $HTTPMethod = 'GET';
        $result = Client::urlRequest($this, $type, $path, $HTTPMethod, '', '');

        return Client::checkAndDecode($result);
    }

    public function getTotalInMxn($accounts)
    {
        $sum_in_mxn = 0.0;
        $sum_in_usd = 0.0;
        foreach ($accounts as $account) {
            $sum_in_mxn += $account['mxn'];
            $sum_in_usd += $account['usd'];
        }

        // convert usd to mxn
        $usd_to_mxn = (float)$this->getPriceForBook('usd_mxn');

        return $sum_in_mxn + $sum_in_usd * $usd_to_mxn;
    }

    //#####           #######
    //##### PRIVATE QUERIES #######
    //#####           #######

    public function getPriceForBook($book): string
    {
        $ticker = $this->booksAndTrades()->ticker($book);

        return $ticker['last'];
    }

    public function fundings($params = []): array
    {
        if ($params === []) {
            $params = ['limit' => 1];
        }

        if (in_array('fids', $params)) {
            $ids = $params('fids');
            unset($params['fids']);
        }
        $parameters = http_build_query($params, '', '&');
        $path = $this->url.'/fundings/?'.$parameters;
        $RequestPath = '/api/v3/fundings/?'.$parameters;
        $HTTPMethod = 'GET';

        return Client::getData($path, $RequestPath, $HTTPMethod);
    }

    public function open_orders($params): array
    {
        $parameters = http_build_query($params, '', '&');
        $path = $this->url.'/open_orders/?'.$parameters;
        $RequestPath = '/api/v3/open_orders/?'.$parameters;
        $HTTPMethod = 'GET';

        return Client::getData($path, $RequestPath, $HTTPMethod);
    }

    public function lookup_order($ids)
    {
        $parameters = implode(',', $ids);
        $path = $this->url.'/orders/'.$parameters;
        $RequestPath = '/api/v3/orders/'.$parameters;
        $HTTPMethod = 'GET';

        return Client::getData($path, $RequestPath, $HTTPMethod);
    }

    public function cancel_order($ids): array
    {

        if ($ids === 'all') {
            $parameters = 'all';
        } else {
            $parameters = implode(',', $ids);
        }

        $path = $this->url.'/orders/'.$parameters;
        $RequestPath = '/api/v3/orders/'.$parameters;
        $HTTPMethod = 'DELETE';

        return Client::getData($path, $RequestPath, $HTTPMethod);
    }

    public function placeOrder($params): array
    {
        $path = $this->url.'/api/v3/orders/';
        $RequestPath = '/api/v3/orders/';
        $HTTPMethod = 'POST';
        $JSONPayload = json_encode($params, JSON_THROW_ON_ERROR);

        return Client::getData($path, $RequestPath, $HTTPMethod);
    }

    public function fundingDestination($params): array
    {
        $parameters = http_build_query($params, '', '&');
        $path = $this->url.'/funding_destination/?'.$parameters;
        $RequestPath = '/api/v3/funding_destination/?'.$parameters;
        $HTTPMethod = 'GET';

        return Client::getData($path, $RequestPath, $HTTPMethod);
    }

    public function setCredentials($key, $secret): static
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->bitsoClient = new Client($key, $secret, $this->url);

        return $this;
    }
}
