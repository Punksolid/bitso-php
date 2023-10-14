<?php
// help
namespace BitsoAPI;

use ErrorException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Bitso
{
    //constructor, default is dev url
    private \Symfony\Contracts\HttpClient\HttpClientInterface $client;

    public function __construct(protected $key = '', protected $secret = '', protected $url = 'https://bitso.com')
    {

        $this->client = HttpClient::create([
            'base_uri' => $url,
        ]);
    }

    //function to perform curl url request depending on type and method
    public function url_request($type, $path, $HTTPMethod, $JSONPayload, $authHeader = ''): string
    {
        if ($type == 'PUBLIC') {
            $response = $this->client->request('GET', $path);

            return $response->getContent();
        }

        if ($type == 'PRIVATE') {
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

    private function makeNonce()
    {
        return round(microtime(true) * 1000);
    }

    public function checkAndDecode($result)
    {

        $result = json_decode((string) $result, true, 512, JSON_THROW_ON_ERROR);
        if ($result['success'] != 1) {
            throw new ErrorException($result->error->message, 1);
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

        $path = $this->url . '/api/v3/available_books/';
        $type = 'PUBLIC';
        $HTTPMethod = 'GET';
        $JSONPayload = '';
//        dd($path);
        $result = $this->url_request($type, $path, $HTTPMethod, $JSONPayload);
//        dd($result);
        return $this->checkAndDecode($result);
    }

    private function makeMessage($HTTPMethod, $RequestPath, $JSONPayload = ''): string
    {
        return $this->makeNonce() . $HTTPMethod . $RequestPath . $JSONPayload;
    }

    private function makeAuthHeader($message, $signature): string
    {
        $format = 'Bitso %s:%s:%s';

        return sprintf($format, $this->key, $nonce, $signature);
    }

    private function buildSignature(): string
    {
        // nonce  +  HTTP method  +  request path  +  JSON payload
        $message = $this->makeMessage('GET', '/api/v3/ticker/', '');
        return hash_hmac('sha256', $message, $this->secret);
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
        $signature = hash_hmac('sha256', $message, $this->secret);
        $authHeader = sprintf('Bitso %s:%s:%s', $this->key, $nonce, $signature);
        $authHeader = $this->makeAuthHeader($message, $signature);
        $result = $this->client->request('GET', $this->url . '/api/v3/ticker/', [
            'headers' => [
                'Authorization' => $authHeader
            ], 'query' => [
                'book' => $book
            ]
        ]);
        return $this->checkAndDecode($result->getContent());
    }

    public function makeRequest()
    {

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
            A bitso.OrderBook instance. */

        $parameters = http_build_query($params, '', '&');
        $path = $this->url . '/order_book/?' . $parameters;
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
        $path = $this->url . '/trades/?' . $parameters;
        $type = 'PUBLIC';
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $result = $this->url_request($type, $path, $HTTPMethod, $JSONPayload);

        return $this->checkAndDecode($result);
    }

    //gets data and makes request
    public function getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type)
    {
        $nonce = $this->makeNonce();
        $message = $nonce . $HTTPMethod . $RequestPath . $JSONPayload;
        $signature = hash_hmac('sha256', $message, $this->secret);
        $format = 'Bitso %s:%s:%s';
        $authHeader = sprintf($format, $this->key, $nonce, $signature);

        $result = $this->client->request(
            $HTTPMethod,
            $this->url . $RequestPath,
            [
                'headers' => ['Authorization' => $authHeader]
            ]
        );

        return json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    //#####           #######
    //##### PRIVATE QUERIES #######
    //#####           #######

    public function account_status()
    {
        /*
        Get a user's account status.
          Returns:
            A bitso.AccountStatus instance. */

        $path = $this->url . '/account_status/';
        $RequestPath = '/api/v3/account_status/';
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);

    }

    public function balances($currency = 'mxn')
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
        $type = 'PRIVATE';
        $message = $nonce . $HTTPMethod . $RequestPath . $JSONPayload;
        $format = 'Bitso %s:%s:%s';
        $signature = hash_hmac('sha256', $message, $this->secret);
        $authHeader = sprintf($format, $this->key, $nonce, $signature);
        $result = $this->client->request('GET',
            $this->url . '/api/v3/balance/',
            ['headers' => ['Authorization' => $authHeader,]
            ]);

        $balances_array = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);

        foreach ($balances_array['payload']['balances'] as $balance) {
            if ($balance['currency'] == $currency) {

                $askedBalance = $balance['total'];
                break;
            }
        }

        return $askedBalance;
    }

    public function fees(): array
    {
        /*
        Get a user's fees for all availabel order books.
        requires key, signature and nonce.
          Returns:
            A list bitso.Fees instances.
        */
        $path = $this->url . '/api/v3/fees/';
        $RequestPath = '/api/v3/fees/';
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        $message = $nonce . $HTTPMethod . $RequestPath . $JSONPayload;
        $signature = hash_hmac('sha256', $message, $this->secret);
        $format = 'Bitso %s:%s:%s';
        $authHeader = sprintf($format, $this->key, $nonce, $signature);


        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);
    }

    public function ledger($params)
    {
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
        $path = $this->url . '/ledger/?' . $parameters;
        $RequestPath = '/api/v3/ledger/?' . $parameters;
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);
    }

    public function withdrawals($params)
    {
        $parameters = null;
        /*
        Get the ledger of user operations
        Args:
          wids (list, optional):
            Specifies which withdrawal objects to return
          marker (str, optional):
            Returns objects that are older or newer (depending on 'sort') than the object which
            has the marker value as ID
          limit (int, optional):
            Limit the number of results to parameter value, max=100, default=25
          sort (str, optional):
            Sorting by datetime: 'asc', 'desc'
            Defuault is 'desc'
        Returns:
          A list bitso.Withdrawal instances.
        */
        if (in_array('wids', $params)) {
            $ids = $params('wids');
            unset($params['wids']);
            $id_nums = implode('', $ids);
            $path = $this->url . '/withdrawals/' . $id_nums . '/?' . $parameters;
            $RequestPath = '/api/v3/withdrawals/' . $id_nums . '/?' . $parameters;
        }
        $parameters = http_build_query($params, '', '&');
        $path = $this->url . '/withdrawals/?' . $parameters;
        $RequestPath = '/api/v3/withdrawals/?' . $parameters;

        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);
    }

    public function fundings($params)
    {
        $parameters = null;
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
            $id_nums = implode('', $ids);
            $path = $this->url . '/withdrawals/' . $id_nums . '/?' . $parameters;
            $RequestPath = '/api/v3/withdrawals/' . $id_nums . '/?' . $parameters;
        }
        $parameters = http_build_query($params, '', '&');
        $path = $this->url . '/fundings/?' . $parameters;
        $RequestPath = '/api/v3/fundings/?' . $parameters;
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);
    }

    public function order_trades($id)
    {
        /*
          Returns all Trades Associated with an order
        */
        $path = $this->url . '/order_trades/' . $id;
        $RequestPath = '/api/v3/order_trades/' . $id;
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);
    }

    public function user_trades($params, $ids = [])
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
        $path = $this->url . '/user_trades/' . $id_nums . '/?' . $parameters;
        $RequestPath = '/api/v3/user_trades/' . $id_nums . '/?' . $parameters;
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);
    }

    public function open_orders($params)
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
        $path = $this->url . '/open_orders/?' . $parameters;
        $RequestPath = '/api/v3/open_orders/?' . $parameters;
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);
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
        $path = $this->url . '/orders/' . $parameters;
        $RequestPath = '/api/v3/orders/' . $parameters;
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);
    }

    public function cancel_order($ids)
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

        $path = $this->url . '/orders/' . $parameters;
        $RequestPath = '/api/v3/orders/' . $parameters;
        $nonce = $this->makeNonce();
        $HTTPMethod = 'DELETE';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);
    }

    public function place_order($params)
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
        $path = $this->url . '/orders/';
        $RequestPath = '/api/v3/orders/';
        $nonce = $this->makeNonce();
        $HTTPMethod = 'POST';
        $JSONPayload = json_encode($params, JSON_THROW_ON_ERROR);
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);
    }

    public function funding_destination($params)
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
        $path = $this->url . '/funding_destination/?' . $parameters;
        $RequestPath = '/api/v3/funding_destination/?' . $parameters;
        $nonce = $this->makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);
    }

    public function btc_withdrawal($params)
    {
        /*
    Triggers a bitcoin withdrawal from your account
      Args:
        amount (str):
          The amount of BTC to withdraw from your account
        address (str):
          The Bitcoin address to send the amount to

      Returns:
        ok
    */
        $path = $this->url . '/bitcoin_withdrawal/';
        $RequestPath = '/api/v3/bitcoin_withdrawal/';
        $nonce = $this->makeNonce();
        $HTTPMethod = 'POST';
        $JSONPayload = json_encode($params, JSON_THROW_ON_ERROR);
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);
    }

    public function eth_withdrawal($params)
    {
        /*
        Triggers an ether withdrawal from your account
          Args:
            amount (str):
              The amount of BTC to withdraw from your account
            address (str):
              The Bitcoin address to send the amount to

          Returns:
            ok
        */
        $path = $this->url . '/ether_withdrawal/';
        $RequestPath = '/api/v3/ether_withdrawal/';
        $nonce = $this->makeNonce();
        $HTTPMethod = 'POST';
        $JSONPayload = json_encode($params, JSON_THROW_ON_ERROR);
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);
    }

    public function ripple_withdrawal($params)
    {
        /*
      Triggers a ripple withdrawal from your account
        Args:
          currency (str):
            The currency to withdraw
          amount (str):
            The amount of BTC to withdraw from your account
          address (str):
            The ripple address to send the amount to

        Returns:
          ok
      */
        $path = $this->url . '/ripple_withdrawal/';
        $RequestPath = '/api/v3/ripple_withdrawal/';
        $nonce = $this->makeNonce();
        $HTTPMethod = 'POST';
        $JSONPayload = json_encode($params, JSON_THROW_ON_ERROR);
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);

    }

    public function spei_withdrawal($params)
    {
        /*
      Triggers a SPEI withdrawal from your account.
        These withdrawals are immediate during banking hours for some banks (M-F 9:00AM - 5:00PM Mexico City Time), 24 hours for others.
        Args:
          amount (str):
            The amount of MXN to withdraw from your account
          recipient_given_names (str):
            The recipient's first and middle name(s)
          recipient_family_names (str):
            The recipient's last names
          clabe (str):
            The CLABE number where the funds will be sent to
            https://en.wikipedia.org/wiki/CLABE
          notes_ref (str):
            The alpha-numeric reference number for this SPEI
          numeric_ref (str):
            The numeric reference for this SPEI

        Returns:
          ok
      */
        $path = $this->url . '/spei_withdrawal/';
        $RequestPath = '/api/v3/spei_withdrawal/';
        $nonce = $this->makeNonce();
        $HTTPMethod = 'POST';
        $JSONPayload = json_encode($params, JSON_THROW_ON_ERROR);
        $type = 'PRIVATE';

        return $this->getData($nonce, $path, $RequestPath, $HTTPMethod, $JSONPayload, $type);
    }

    public function setClient(HttpClientInterface $client): void
    {
        $this->client = $client;
    }
}
