<?php

namespace BitsoAPI;

use JsonException;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase
{
    const key = '';

    const secret = '';

    const url = 'https://bitso.com';

    private Bitso $bitso;

    private Client $client;

    /**
     * @throws JsonException
     */
    public function testBalances()
    {
        $json = <<<'JSON'
{
    "success": true,
    "payload": {
        "balances": [{
            "currency": "mxn",
            "total": "300.00",
            "locked": "25.1234",
            "available": "274.8766",
            "pending_deposit": "0.00000000",
            "pending_withdrawal": "200.00000000"
        }, {
            "currency": "btc",
            "total": "100.12345678",
            "locked": "25.00000000",
            "available": "75.12345678",
            "pending_deposit": "10.00000000",
            "pending_withdrawal": "0.00000000"
        }, {
            "currency": "eth",
            "total": "50.1234",
            "locked": "40.1234",
            "available": "10.0000",
            "pending_deposit": "0.00000000",
            "pending_withdrawal": "0.00000000"
        }]
    }
}
JSON;
        $client = $this->getMockAccountBalanceResponseObject($json);

        $accounts_api = new Accounts($client, new Bitso(self::key, self::secret, self::url));

        $this->assertIsArray($accounts_api->balances());
        $this->assertCount(3, $accounts_api->balances());
    }

    public function getMockAccountBalanceResponseObject(string $json)
    {
        $bitso_client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();

        $response = json_decode($json, true);

        $bitso_client->method('getData')->willReturn($response);

        return $bitso_client;
    }

    public function testAccountStatus()
    {
        $jsonResponseForAccountStatus = <<<'JSON'
{
    "success": true,
    "payload": {
        "client_id": "1234",
        "first_name": "Claude",
        "last_name":  "Shannon",
        "second_last_name": "Monet",
        "status": "active",
        "daily_limit": "5300.00",
        "monthly_limit": "32000.00",
        "daily_remaining": "3300.00",
        "monthly_remaining": "31000.00",
        "cash_deposit_allowance": "5300.00",
        "cellphone_number": "verified",
        "cellphone_number_stored":"+525555555555",
        "email": "verified",
        "email_stored":"shannon@maxentro.py",
        "official_id": "submitted",
        "proof_of_residency": "submitted",
        "signed_contract": "unsubmitted",
        "origin_of_funds": "unsubmitted",
        "verification_level": 5,
        "referral_code": "aauv",
        "country_of_residence": "MX",
        "gravatar_img": "https://secure.gravatar.com/avatar/4a35137ea4adc1ae89dd98d8a268f2fb?s=46&d=https%3A%2F%2Fapi-sandbox.bitso.com%2Fassets%2Fimages%2Fb%2Fprofiledefault.png",
        "account_creation_date": "2022-01-09T17:07:24+0000",
        "preferred_currency": "mxn",
        "enabled_two_factor_methods": [
            "totp"
        ],
        "business_name": "",
        "gender": "F",
        "user_default_fiat_currency": "mxn",
        "date_of_birth": "01/01/1990",
        "born_in_residence": "1",
        "tax_payer_type": "person",
        "entity_type": "N/A"
    }
}
JSON;
        $client = $this->getMockAccountBalanceResponseObject($jsonResponseForAccountStatus);
        $accounts_api = new Accounts($client, new Bitso(self::key, self::secret, self::url));

        $this->assertArrayHasKey('client_id', $accounts_api->accountStatus());
        $this->assertEquals('1234', $accounts_api->accountStatus()['client_id']);

    }

    public function testGetAccountBalanceCombined()
    {
        $this->markTestIncomplete('It works, but it needs more mocking
         to the rests of the requests for the current price of the currencies');
        $json = <<<'JSON'
{
    "success": true,
    "payload": {
        "balances": [{
            "currency": "mxn",
            "total": "300.00",
            "locked": "25.1234",
            "available": "274.8766",
            "pending_deposit": "0.00000000",
            "pending_withdrawal": "200.00000000"
        }, {
            "currency": "btc",
            "total": "100.12345678",
            "locked": "25.00000000",
            "available": "75.12345678",
            "pending_deposit": "10.00000000",
            "pending_withdrawal": "0.00000000"
        }, {
            "currency": "eth",
            "total": "50.1234",
            "locked": "40.1234",
            "available": "10.0000",
            "pending_deposit": "0.00000000",
            "pending_withdrawal": "0.00000000"
        }]
    }
}
JSON;
        $client = $this->getMockAccountBalanceResponseObject($json);

        $accounts_api = new Accounts($client, $this->bitso);

        $this->assertSame(75526675, $accounts_api->accountValue());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->bitso = new Bitso(self::key, self::secret, self::url);
        $this->client = new Client(self::key, self::secret, self::url);
    }
}
