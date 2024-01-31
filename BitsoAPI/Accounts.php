<?php

namespace BitsoAPI;

class Accounts
{
    public function __construct(
        private Bitso $bitso,
    )
    {}

    public function balances(?string $asked_currency = null): array
    {
        /*
        Get a user's balance.
            Returns:
              A list of bitso.Balance instances.
        */

        $request_path = '/api/v3/balance/';

        $nonce = Client::makeNonce();
        $HTTPMethod = 'GET';
        $JSONPayload = '';
        $message = $nonce.$HTTPMethod.$request_path.$JSONPayload;
        $format = 'Bitso %s:%s:%s';
        $signature = hash_hmac('sha256', $message, (string) $this->bitso->secret);
        $authHeader = sprintf($format, $this->bitso->key, $nonce, $signature);

        $result = $this->bitso->client->request(
            'GET',
            $this->bitso->url.'/api/v3/balance/',
            [
                'headers' => ['Authorization' => $authHeader],
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
                $book_price = $this->bitso->getPriceForBook($book);
                $accounts[$currency][$in_currency] += $total * $book_price;
            } catch (\Throwable) {
                try {
                    $book = $currency.'_'.$fallback_currency_converted_to;
                    $book_price_in_fallback = $this->bitso->getPriceForBook($book);
                    $accounts[$currency][$fallback_currency_converted_to] += $total * $book_price_in_fallback;
                } catch (\Throwable) {
                    $book = 'usd'.'_'.$currency; // ars

                    $book_price_in_fallback = $this->bitso->getPriceForBook($book);

                    $accounts[$currency]['usd'] = $total / $book_price_in_fallback;
                }
            }
        }

        return $this->bitso->getTotalInMxn($accounts);
    }
}