<?php

namespace BitsoAPI;

use PHPUnit\Framework\TestCase;

class OrdersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testFees()
    {
        $feesResponseObject = <<<'JSON'
{
   "success":true,
   "payload":{
      "fees":[
         {
            "book":"btc_mxn",
            "fee_percent":"0.6500",
            "fee_decimal":"0.00650000",
            "taker_fee_percent":"0.6500",
            "taker_fee_decimal":"0.00650000",
            "maker_fee_percent":"0.5000",
            "maker_fee_decimal":"0.00500000",
            "volume_currency":"mxn",
            "current_volume":"4375.99",
            "next_volume":"1500000.00",
            "next_maker_fee_percent":"0.490",
            "next_taker_fee_percent":"0.637",
            "nextVolume":"1500000.00",
            "nextFee":"0.490",
            "nextTakerFee":"0.637"
         },
         {
            "book":"eth_btc",
            "fee_percent":"0.0980",
            "fee_decimal":"0.00098000",
            "taker_fee_percent":"0.0980",
            "taker_fee_decimal":"0.00098000",
            "maker_fee_percent":"0.0750",
            "maker_fee_decimal":"0.00075000",
            "volume_currency":"btc",
            "current_volume":"0.00000000",
            "next_volume":"8.00000000",
            "next_maker_fee_percent":"0.072",
            "next_taker_fee_percent":"0.094",
            "nextVolume":"8.00000000",
            "nextFee":"0.072",
            "nextTakerFee":"0.094"
         }
      ],
      "deposit_fees":[
         {
            "currency":"cop",
            "method":"bdb",
            "fee":"0.00",
            "is_fixed":false
         },
         {
            "currency":"ars",
            "method":"bind",
            "fee":"0.01",
            "is_fixed":false
         }
      ],
      "withdrawal_fees":{
         "aave":"0.00100000",
         "ada":"0.50000000",
         "algo":"0.01000000"
      }
   }
}

JSON;

        $client = $this->getClientMock($feesResponseObject);

        $orders = new Orders($client, new Bitso(
            '',
            '',
            ''
        ));

        $this->assertArrayHasKey('fees', $orders->fees());
        $this->assertArrayHasKey('book', $orders->fees()['fees'][0]);
    }

    public function testUserTrades()
    {
        $jsonUserTrades = <<<'JSON'
{
    "success": true,
    "payload": [
        {
            "book": "btc_mxn",
            "major": "0.00050327",
            "minor": "-188.08206440",
            "major_currency": "btc",
            "minor_currency": "mxn",
            "price": "373720.00",
            "side": "buy",
            "maker_side": "sell",
            "fees_currency": "btc",
            "fees_amount": "0.00000327",
            "tid": "156478320",
            "oid": "mZ3DDN4VxOMTEQZU",
            "created_at": "2023-03-09T22:40:37+0000",
            "origin_id": "1029384756"
        },
        {
            "book": "btc_mxn",
            "major": "0.00050327",
            "minor": "-187.91598530",
            "major_currency": "btc",
            "minor_currency": "mxn",
            "price": "373390.00",
            "side": "buy",
            "maker_side": "sell",
            "fees_currency": "btc",
            "fees_amount": "0.00000327",
            "tid": "156476894",
            "oid": "YD0NNMY2NzkyikEj",
            "created_at": "2023-03-09T21:29:26+0000",
            "origin_id": "6848302747"
        }
    ]
}
JSON;
        $client = $this->getClientMock($jsonUserTrades);

        $bitso = $this->getMockBuilder(Bitso::class)
            ->setConstructorArgs(['', ''])
            ->getMock();

        $orders = new Orders($client, $bitso);
        $response = $orders->userTrades(['book' => 'btc_mxn']);

        $this->assertEquals(-188.08206440, $response[0]['minor']);
        $this->assertEquals(0.00050327, $response[0]['major']);
        $this->assertEquals(156478320, $response[0]['tid']);
        $this->assertEquals('mZ3DDN4VxOMTEQZU', $response[0]['oid']);
    }

    public function getClientMock(string $json)
    {
        $bitso_client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response = json_decode($json, true);

        $bitso_client->method('getData')
            ->willReturn($response);

        return $bitso_client;
    }
}
