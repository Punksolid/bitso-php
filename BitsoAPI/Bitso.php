<?php

declare(strict_types=1);

namespace BitsoAPI;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Bitso
{
    //constructor, default is dev url
    public readonly HttpClientInterface $client;

    public function __construct(public $key = '', public $secret = '', public $url = 'https://bitso.com')
    {
        $this->client = HttpClient::create([
            'base_uri' => $url,
        ]);
    }

//#####          #######
    //##### PUBLIC QUERIES #######
    //#####          #######

    public function available_books()
    {
        /*
        Returns:
         A list of bitso.AvilableBook instances */

        $path = $this->url.'/api/v3/available_books/';
        $type = 'PUBLIC';
        $HTTPMethod = 'GET';
        $result = Client::urlRequest($this, $type, $path, $HTTPMethod, '', '');

        return Client::checkAndDecode($result);
    }

    public function ticker($book)
    {
        /*
        Get a Bitso price ticker.
          Args:
            book (str):
              Specifies which book to use.

          Returns:
            A bitso.Ticker instance.
        */
        $message = $this->makeMessage('GET', '/api/v3/ticker/', '');
        $signature = hash_hmac('sha256', $message, (string) $this->secret);
        $authHeader = $this->makeAuthHeader($message, $signature);
        $result = $this->client->request('GET', $this->url.'/api/v3/ticker/', [
            'headers' => [
                'Authorization' => $authHeader,
            ], 'query' => [
                'book' => $book,
            ],
        ]);

        return Client::checkAndDecode($result->getContent());
    }

    public function order_book($params)
    {
        /*
          Get a public Bitso order book with a list of all open orders in the specified book
              Args:
                book (str):
                  Specifies which book to use. Default is btc_mxn
                aggregate (bool):
                  Specifies if orders should be aggregated by price

              Returns:
                A bitso.OrderBook instance.
            */

        $parameters = http_build_query($params, '', '&');
        $path = $this->url.'/api/v3/order_book/?'.$parameters;
        $type = 'PUBLIC';
        $HTTPMethod = 'GET';
        $result = Client::urlRequest($this, $type, $path, $HTTPMethod, '', '');

        return Client::checkAndDecode($result);
    }

    public function trades($params)
    {
        /*
        Get a list of recent trades from the specified book.
          Args:
            book (str):
              Specifies which book to use. Default is btc_mxn
            marker (str, optional):
              Returns objects that are older or newer (depending on 'sort') than the object which
              has the marker value as ID
            limit (int, optional):
              Limit the number of results to parameter value, max=100, default=25
            sort (str, optional):
              Sorting by datetime: 'asc', 'desc'
              Defuault is 'desc'

          Returns:
            A list of bitso.Trades instances. */

        $parameters = http_build_query($params, '', '&');
        $path = $this->url.'/api/v3/trades/?'.$parameters;
        $type = 'PUBLIC';
        $HTTPMethod = 'GET';
        $result = Client::urlRequest($this, $type, $path, $HTTPMethod, '', '');

        return Client::checkAndDecode($result);
    }

    //gets data and makes request

    //#####           #######
    //##### PRIVATE QUERIES #######
    //#####           #######

    public function account_status(): array
    {
        /*
        Get a user's account status.
          Returns:
            A bitso.AccountStatus instance. */

        $path = $this->url.'/api/v3/account_status/';
        $RequestPath = '/api/v3/account_status/';
        $nonce = Client::makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return Client::getData($this, $path, $RequestPath, $HTTPMethod, $JSONPayload);
    }





    public function getTotalInMxn($accounts)
    {
        $sum_in_mxn = 0;
        $sum_in_usd = 0;
        foreach ($accounts as $account) {
            $sum_in_mxn += $account['mxn'];
            $sum_in_usd += $account['usd'];
        }

        // convert usd to mxn
        $usd_to_mxn = $this->getPriceForBook('usd_mxn');

        return $sum_in_mxn + $sum_in_usd * $usd_to_mxn;
    }

    public function getPriceForBook($book)
    {
        $ticker = $this->ticker($book);

        return $ticker['payload']['last'];
    }

    public function fees(): array
    {
        /*
        Get a user's fees for all availabel order books.
        requires key, signature and nonce.
          Returns:
            A list bitso.Fees instances.
        */
        $path = $this->url.'/api/v3/fees/';
        $RequestPath = '/api/v3/fees/';
        $nonce = Client::makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';

        return Client::getData($this, $path, $RequestPath, $HTTPMethod, $JSONPayload);
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

        $path = $this->url.'/api/v3/withdrawals/?'.$parameters;
        $RequestPath = '/api/v3/withdrawals/?'.$parameters;
        $HTTPMethod = 'GET';

        return Client::getData($this, $path, $RequestPath, $HTTPMethod, '');
    }

    public function fundings($params = []): array
    {
        if ($params === []) {
            $params = ['limit' => 1];
        }
        /*
        Get the ledger of user operations
        Args:
          fids (list, optional):
            Specifies which funding objects to return
          marker (str, optional):
            Returns objects that are older or newer (depending on 'sort') than the object which
            has the marker value as ID
          limit (int, optional):
            Limit the number of results to parameter value, max=100, default=25
          sort (str, optional):
            Sorting by datetime: 'asc', 'desc'
            Defuault is 'desc'
        Returns:
          A list bitso.Funding instances.
        */
        if (in_array('fids', $params)) {
            $ids = $params('fids');
            unset($params['fids']);
        }
        $parameters = http_build_query($params, '', '&');
        $path = $this->url.'/fundings/?'.$parameters;
        $RequestPath = '/api/v3/fundings/?'.$parameters;
        $nonce = Client::makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return Client::getData($this, $path, $RequestPath, $HTTPMethod, $JSONPayload);
    }

    public function user_trades($params = [], $ids = []): array
    {
        /*
        Get a list of the user's transactions
        Args:
           book (str):
            Specifies which order book to get user trades from.
          marker (str, optional):
            Returns objects that are older or newer (depending on 'sort') than the object which
            has the marker value as ID
          limit (int, optional):
            Limit the number of results to parameter value, max=100, default=25
          sort (str, optional):
            Sorting by datetime: 'asc', 'desc'
            Defuault is 'desc'

        Returns:
          A list bitso.UserTrade instances.
        */
        $id_nums = implode('', $ids);
        $parameters = http_build_query($params, '', '&');
        $path = $this->url.'/user_trades/'.$id_nums.'/?'.$parameters;
        $RequestPath = '/api/v3/user_trades/'.$id_nums.'/?'.$parameters;
        $nonce = Client::makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return Client::getData($this, $path, $RequestPath, $HTTPMethod, $JSONPayload);
    }

    public function open_orders($params): array
    {
        /*
        Get a list of the user's open orders
        Args:
          book (str):
            Specifies which book to use. Default is btc_mxn

        Returns:
          A list of bitso.Order instances.
        */
        $parameters = http_build_query($params, '', '&');
        $path = $this->url.'/open_orders/?'.$parameters;
        $RequestPath = '/api/v3/open_orders/?'.$parameters;
        $nonce = Client::makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return Client::getData($this, $path, $RequestPath, $HTTPMethod, $JSONPayload);
    }

    public function lookup_order($ids)
    {
        /*
        Get a list of details for one or more orders
        Args:
          order_ids (list):
            A list of Bitso Order IDs

        Returns:
          A list of bitso.Order instances.
        */
        $parameters = implode(',', $ids);
        $path = $this->url.'/orders/'.$parameters;
        $RequestPath = '/api/v3/orders/'.$parameters;
        $nonce = Client::makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return Client::getData($this, $path, $RequestPath, $HTTPMethod, $JSONPayload);
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
        $JSONPayload = '';

        return Client::getData($this, $path, $RequestPath, $HTTPMethod, $JSONPayload);
    }

    public function place_order($params): array
    {
        $path = $this->url.'/api/v3/orders/';
        $RequestPath = '/api/v3/orders/';
        $HTTPMethod = 'POST';
        $JSONPayload = json_encode($params, JSON_THROW_ON_ERROR);

        return Client::getData($this, $path, $RequestPath, $HTTPMethod, $JSONPayload);
    }

    public function fundingDestination($params): array
    {
        /*
        Returns account funding information for specified currencies.
          Args:
            fund_currency (str):
              Specifies which book to use.

          Returns:
            A bitso.Funding Destination instance.
        */
        $parameters = http_build_query($params, '', '&');
        $path = $this->url.'/funding_destination/?'.$parameters;
        $RequestPath = '/api/v3/funding_destination/?'.$parameters;
        $nonce = Client::makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return Client::getData($this, $path, $RequestPath, $HTTPMethod, $JSONPayload);
    }

    public function setCredentials($key, $secret): static
    {
        $this->key = $key;
        $this->secret = $secret;

        return $this;
    }

    private function makeMessage($HTTPMethod, $RequestPath, $JSONPayload = ''): string
    {
        return Client::makeNonce() .$HTTPMethod.$RequestPath.$JSONPayload;
    }

    private function makeAuthHeader($message, $signature): string
    {
        return sprintf('Bitso %s:%s:%s', $this->key, Client::makeNonce(), $signature);
    }
}




