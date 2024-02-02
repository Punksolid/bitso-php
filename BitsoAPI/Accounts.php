<?php

namespace BitsoAPI;

use JsonException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Accounts
{
    private Client $client;
    private HttpClientInterface $httpClient;
    private Bitso $bitso;

    public function __construct(Client $client, Bitso $bitso) {
        $this->client = $client;
        $this->httpClient = HttpClient::create([
            'base_uri' => Bitso::URL,
        ]);
        $this->bitso = $bitso;
    }

    public function balances(?string $asked_currency = null): array
    {
        $result = $this->client->getData(
            '/api/v3/balance/'
            );

        $balances_array = $result;

        if ($asked_currency !== null) {
            $balances_array = array_filter($balances_array, fn ($balance) => $balance['currency'] === $asked_currency);
        }

        return $balances_array['payload']['balances'];
    }

    /**
     * Bitso does not provide a way to get the total value of the account in a specific currency. This method will
     * calculate the total value of the account in the specified currency. Based on the orders in the books.
     *
     * @param $in_currency
     * @return int
     * @throws JsonException
     */
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

    public function accountStatus(): array
    {
        return $this->client->getData('/api/v3/account_status/')['payload'];
    }
}
