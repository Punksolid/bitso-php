<?php

declare(strict_types=1);

namespace BitsoAPI;

use ErrorException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Bitso
{
    //constructor, default is dev url
    private readonly HttpClientInterface $client;

    public function __construct(protected $key = '', protected $secret = '', protected $url = 'https://bitso.com')
    {
        $this->client = HttpClient::create([
            'base_uri' => $url,
        ]);
    }

    //function to perform curl url request depending on type and method
    public function url_request($type, $path, $HTTPMethod, $JSONPayload, $authHeader = ''): string
    {
        if ($type === 'PUBLIC') {
            $response = $this->client->request('GET', $path);

            return $response->getContent();
        }

        if ($type === 'PRIVATE') {
            $options = ['headers' => ['Authorization' => $authHeader, 'Content-Type' => 'application/json']];

            if ($HTTPMethod === 'GET' or $HTTPMethod === 'DELETE') {
                $response = $this->client->request($HTTPMethod, $path, $options);
            }

            if ($HTTPMethod === 'POST') {
                $response = $this->client->request('POST', $path, $options + ['body' => $JSONPayload]);
            }
        }

        return $response->getContent();
    }

    public function checkAndDecode($result)
    {
        $result = json_decode((string) $result, true, 512, JSON_THROW_ON_ERROR);
        if ($result['success'] !== true) {
            throw new ErrorException($result['error']['message'], 1);
        }

        return $result;
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
        $JSONPayload = '';
        $result = $this->url_request($type, $path, $HTTPMethod, $JSONPayload);

        return $this->checkAndDecode($result);
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

        return $this->checkAndDecode($result->getContent());
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
        $JSONPayload = '';
        $result = $this->url_request($type, $path, $HTTPMethod, $JSONPayload);

        return $this->checkAndDecode($result);
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
        $JSONPayload = '';
        $result = $this->url_request($type, $path, $HTTPMethod, $JSONPayload);

        return $this->checkAndDecode($result);
    }

    //gets data and makes request
    public function getData($path, $RequestPath, $HTTPMethod, $JSONPayload = ''): array
    {
        $nonce = $this->makeNonce();
        $message = $nonce.$HTTPMethod.$RequestPath.$JSONPayload;
        $signature = hash_hmac('sha256', $message, (string) $this->secret);
        $authHeader = sprintf('Bitso %s:%s:%s', $this->key, $nonce, $signature);

        $result = $this->client->request(
            $HTTPMethod,
            $this->url.$RequestPath,
            [
                'headers' => ['Authorization' => $authHeader],
            ]
        );

        return json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

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
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($path, $RequestPath, $HTTPMethod, $JSONPayload);
    }

    public function balances(?string $asked_currency = null)
    {
        /*
        Get a user's balance.
            Returns:
              A list of bitso.Balance instances.
        */

        $RequestPath = '/api/v3/balance/';

        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $message = $nonce.$HTTPMethod.$RequestPath.$JSONPayload;
        $format = 'Bitso %s:%s:%s';
        $signature = hash_hmac('sha256', $message, (string) $this->secret);
        $authHeader = sprintf($format, $this->key, $nonce, $signature);
        $result = $this->client->request(
            'GET',
            $this->url.'/api/v3/balance/',
            ['headers' => ['Authorization' => $authHeader],
            ]
        );

        $balances_array = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $balances_array = $balances_array['payload']['balances'];

        if ($asked_currency !== null) {
            $balances_array = array_filter($balances_array, fn ($balance) => $balance['currency'] === $asked_currency);
        }

        return $balances_array;
    }

    public function accountValue($in_currency = 'usd'): int
    {
        $fallback_currency_converted_to = 'mxn';
        $sub_accounts = $this->balances();

        $accounts = [];
        foreach ($sub_accounts as $sub_account) {
            $currency = $sub_account['currency'];
            $total = $sub_account['total'];
            if ($total === 0) { // skip zero balances
                continue;
            }
            $accounts[$currency]['usd'] = 0;
            $accounts[$currency]['mxn'] = 0;

            if ($currency === $in_currency) {
                $accounts[$currency][$in_currency] = $total;
                $account_value[$currency] = $total;

                continue;
            }

            if ($currency === $in_currency) {
                $accounts[$currency][$in_currency] = $total;
                $account_value[$currency] = $total;

                continue;
            }

            try {
                $book = $currency.'_'.$in_currency;
                $book_price = $this->getPriceForBook($book);
                $accounts[$currency][$in_currency] += $total * $book_price;
            } catch (\Throwable) {
                try {
                    $book = $currency.'_'.$fallback_currency_converted_to;
                    $book_price_in_fallback = $this->getPriceForBook($book);
                    $accounts[$currency][$fallback_currency_converted_to] += $total * $book_price_in_fallback;
                } catch (\Throwable) {
                    $book = 'usd'.'_'.$currency; // ars

                    $book_price_in_fallback = $this->getPriceForBook($book);

                    $accounts[$currency]['usd'] = $total / $book_price_in_fallback;
                }
            }
        }

        return $this->getTotalInMxn($accounts);
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
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        $message = $nonce.$HTTPMethod.$RequestPath.$JSONPayload;
        $signature = hash_hmac('sha256', $message, (string) $this->secret);
        $format = 'Bitso %s:%s:%s';

        return $this->getData($path, $RequestPath, $HTTPMethod, $JSONPayload);
    }

    public function ledger($params = null): array
    {
        if ($params === null) {
            $params = ['limit' => 1];
        }
        /*
        Get the ledger of user operations
        Args:
          operations (str, optional):
            They type of operations to include. Enum of ('trades', 'fees', 'fundings', 'withdrawals')
            If None, returns all the operations.
          marker (str, optional):
            Returns objects that are older or newer (depending on 'sort') than the object which
            has the marker value as ID
          limit (int, optional):
            Limit the number of results to parameter value, max=100, default=25
          sort (str, optional):
            Sorting by datetime: 'asc', 'desc'
            Defuault is 'desc'
        Returns:
          A list bitso.LedgerEntry instances.
        */

        $parameters = http_build_query($params, '', '&');
        $path = $this->url.'/api/v3/ledger/?'.$parameters;
        $RequestPath = '/api/v3/ledger/?'.$parameters;
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($path, $RequestPath, $HTTPMethod, $JSONPayload);
    }

    /**
     * @see https://docs.bitso.com/bitso-payouts-funding/docs/list-your-withdrawals#query-parameters
     *
     * @throws \JsonException
     */
    public function withdrawals($withdrawal_id = null, $origin_id = null, $status = null, $limit = 25, $method = null, $marker = null): array
    {
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

        return $this->getData($path, $RequestPath, $HTTPMethod);
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
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($path, $RequestPath, $HTTPMethod, $JSONPayload);
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
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($path, $RequestPath, $HTTPMethod, $JSONPayload);
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
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($path, $RequestPath, $HTTPMethod, $JSONPayload);
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
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($path, $RequestPath, $HTTPMethod, $JSONPayload);
    }

    public function cancel_order($ids): array
    {
        /*
        Cancels an open order
        Args:
          order_id (str):
            A Bitso Order ID.

        Returns:
          A list of Order IDs (OIDs) for the canceled orders. Orders may not be successfully cancelled if they have been filled, have been already cancelled, or the OIDs are incorrect
        */
        if ($ids === 'all') {
            $parameters = 'all';
        } else {
            $parameters = implode(',', $ids);
        }

        $path = $this->url.'/orders/'.$parameters;
        $RequestPath = '/api/v3/orders/'.$parameters;
        $nonce = $this->makeNonce();
        $HTTPMethod = 'DELETE';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($path, $RequestPath, $HTTPMethod, $JSONPayload);
    }

    public function place_order($params): array
    {
        /*
        Places a buy limit order.
          Args:
            book (str):
              Specifies which book to use.
            side (str):
              the order side (buy or sell)
            order_type (str):
              Order type (limit or market)
            major (str):
              The amount of major currency for this order. An order could be specified in terms of major or minor, never both.
            minor (str):
              The amount of minor currency for this order. An order could be specified in terms of major or minor, never both.
            price (str):
              Price per unit of major. For use only with limit orders.
          Returns:
            A bitso.Order instance.
        */
        $path = $this->url.'/api/v3/orders/';
        $RequestPath = '/api/v3/orders/';
        $nonce = $this->makeNonce();
        $HTTPMethod = 'POST';
        $JSONPayload = json_encode($params, JSON_THROW_ON_ERROR);
        $type = 'PRIVATE';

        return $this->getData($path, $RequestPath, $HTTPMethod, $JSONPayload);
    }

    public function funding_destination($params): array
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
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($path, $RequestPath, $HTTPMethod, $JSONPayload);
    }

    public function setCredentials($key, $secret): static
    {
        $this->key = $key;
        $this->secret = $secret;

        return $this;
    }

    private function makeNonce(): float
    {
        return round(microtime(true) * 1000);
    }

    private function makeMessage($HTTPMethod, $RequestPath, $JSONPayload = ''): string
    {
        return $this->makeNonce().$HTTPMethod.$RequestPath.$JSONPayload;
    }

    private function makeAuthHeader($message, $signature): string
    {
        return sprintf('Bitso %s:%s:%s', $this->key, $this->makeNonce(), $signature);
    }
}
