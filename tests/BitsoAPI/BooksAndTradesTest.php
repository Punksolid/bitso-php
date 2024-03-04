<?php

namespace BitsoAPI;

use JsonException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class BooksAndTradesTest extends TestCase
{
    const key = '';

    const secret = '';

    const url = '';

    public function testAvailableBooks(): void
    {
        $json_response = <<<'JSON'
{
   "payload":[
      {
         "default_chart":"candle",
         "minimum_price":"500",
         "fees":{
            "flat_rate":{
               "maker":"0.500",
               "taker":"0.650"
            },
            "structure":[
               {
                  "volume":"1500000",
                  "maker":"0.00500",
                  "taker":"0.00650"
               },
               {
                  "volume":"2000000",
                  "maker":"0.00490",
                  "taker":"0.00637"
               },
               {
                  "volume":"5000000",
                  "maker":"0.00480",
                  "taker":"0.00624"
               },
               {
                  "volume":"7000000",
                  "maker":"0.00440",
                  "taker":"0.00572"
               },
               {
                  "volume":"10000000",
                  "maker":"0.00420",
                  "taker":"0.00546"
               },
               {
                  "volume":"15000000",
                  "maker":"0.00400",
                  "taker":"0.00520"
               },
               {
                  "volume":"35000000",
                  "maker":"0.00370",
                  "taker":"0.00481"
               },
               {
                  "volume":"50000000",
                  "maker":"0.00300",
                  "taker":"0.00390"
               },
               {
                  "volume":"150000000",
                  "maker":"0.00200",
                  "taker":"0.00260"
               },
               {
                  "volume":"250000000",
                  "maker":"0.00100",
                  "taker":"0.00130"
               },
               {
                  "volume":"9999999999",
                  "maker":"0.0005",
                  "taker":"0.00130"
               }
            ]
         },
         "maximum_price":"16000000",
         "book":"btc_mxn",
         "minimum_value":"5",
         "maximum_amount":"500",
         "maximum_value":"50000000",
         "minimum_amount":".000075",
         "tick_size":"0.01"
      },
      {
         "default_chart":"candle",
         "minimum_price":"0.00000100",
         "fees":{
            "flat_rate":{
               "maker":"0.075",
               "taker":"0.098"
            },
            "structure":[
               {
                  "volume":"8",
                  "maker":"0.00075",
                  "taker":"0.00098"
               },
               {
                  "volume":"10",
                  "maker":"0.00072",
                  "taker":"0.00094"
               },
               {
                  "volume":"18",
                  "maker":"0.00071",
                  "taker":"0.00092"
               },
               {
                  "volume":"30",
                  "maker":"0.00070",
                  "taker":"0.00091"
               },
               {
                  "volume":"45",
                  "maker":"0.00067",
                  "taker":"0.00087"
               },
               {
                  "volume":"65",
                  "maker":"0.00065",
                  "taker":"0.00085"
               },
               {
                  "volume":"180",
                  "maker":"0.00063",
                  "taker":"0.00082"
               },
               {
                  "volume":"500",
                  "maker":"0.00059",
                  "taker":"0.00077"
               },
               {
                  "volume":"950",
                  "maker":"0.00055",
                  "taker":"0.00072"
               },
               {
                  "volume":"9999999999",
                  "maker":"0.00050",
                  "taker":"0.00065"
               }
            ]
         },
         "maximum_price":"5000.00000000",
         "book":"eth_btc",
         "minimum_value":"0.00000100",
         "maximum_amount":"1000.00000000",
         "maximum_value":"2000.00000000",
         "minimum_amount":"0.00000100",
         "tick_size":"0.00000001"
      }
   ]
}
JSON;
        $client = $this->getMockClient($json_response);
        $booksAndTrades = new BooksAndTrades($client, new Bitso(self::key, self::secret, self::url));

        $this->assertArrayHasKey('default_chart', $booksAndTrades->availableBooks()[0]);
        $this->assertArrayHasKey('fees', $booksAndTrades->availableBooks()[0]);
        $this->assertArrayHasKey('book', $booksAndTrades->availableBooks()[0]);
        $this->assertArrayHasKey('minimum_value', $booksAndTrades->availableBooks()[0]);
        $this->assertArrayHasKey('maximum_amount', $booksAndTrades->availableBooks()[0]);
        $this->assertArrayHasKey('maximum_value', $booksAndTrades->availableBooks()[0]);
        $this->assertArrayHasKey('minimum_amount', $booksAndTrades->availableBooks()[0]);
    }
//    public function testAvailableBooks()
//    {
//        $bitso = $this->getMockBuilder(Bitso::class)
//            ->setConstructorArgs([self::key, self::secret])
//            ->getMock();
//
//        $fake_response = json_decode(<<<'JSON'
//{
//        "success": true,
//        "payload": [{
//           "book": "btc_mxn",
//           "minimum_amount": ".003",
//           "maximum_amount": "1000.00",
//           "minimum_price": "100.00",
//           "maximum_price": "1000000.00",
//           "minimum_value": "25.00",
//           "maximum_value": "1000000.00"
//        }, {
//           "book": "eth_mxn",
//           "minimum_amount": ".003",
//           "maximum_amount": "1000.00",
//           "minimum_price": "100.0",
//           "maximum_price": "1000000.0",
//           "minimum_value": "25.0",
//           "maximum_value": "1000000.0"
//        }]}
//JSON
//        );
//
//        $bitso->expects($this->any())
//            ->method('available_books')
//            ->willReturn($fake_response);
//
//        $response = $bitso->available_books()->payload[0];
//
//        $this->assertEquals($response->minimum_amount, (float) '.003');
//        $this->assertEquals($response->maximum_amount, (float) '1000.00');
//        $this->assertEquals($response->minimum_price, (float) '100.00');
//        $this->assertEquals($response->maximum_price, (float) '1000000.00');
//        $this->assertEquals($response->minimum_value, (float) '25.00');
//        $this->assertEquals($response->maximum_value, (float) '1000000.00');
//
//    }

    public function getMockClient($json_response): Client
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();

        $client->expects($this->any())->method('getData')->willReturn(json_decode($json_response, true));

        return $client;
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    public function testTicker()
    {
        $jsonTickerResponseObject = <<<'JSON'
{
    "success": true,
    "payload": {
            "high": "472472.82",
            "last": "372110.00",
            "created_at": "2023-03-09T20:58:23+00:00",
            "book": "btc_mxn",
            "volume": "112.81964756",
            "vwap": "388387.4631589659",
            "low": "10000.00",
            "ask": "372800.00",
            "bid": "372110.00",
            "change_24": "-25580.00",
            "rolling_average_change": {
                "6": "-0.5228"
            }
    }
}
JSON;
        $client = $this->getMockClient($jsonTickerResponseObject);
        $booksAndTrades = new BooksAndTrades($client, $this->createMock(Bitso::class));

        $this->assertArrayHasKey('high', $booksAndTrades->ticker('btc_mxn'));
    }

    public function testTrades()
    {
        $jsonTradesResponseObject = <<<'JSON'
{
    "success": true,
    "payload": [{
        "book": "btc_mxn",
        "created_at": "2016-04-08T17:52:31.000+00:00",
        "amount": "0.02000000",
        "maker_side": "buy",
        "price": "5545.01",
        "tid": 55845
    }, {
        "book": "btc_mxn",
        "created_at": "2016-04-08T17:52:31.000+00:00",
        "amount": "0.33723939",
        "maker_side": "sell",
        "price": "5633.98",
        "tid": 55844
    }]
}
JSON;

        $client = $this->getMockClient($jsonTradesResponseObject);

        $booksAndTrades = new BooksAndTrades($client, $this->createMock(Bitso::class));
        $this->assertArrayHasKey('book', $booksAndTrades->trades(['book' => 'btc_mxn'])[0]);
        $this->assertArrayHasKey('created_at', $booksAndTrades->trades(['book' => 'btc_mxn'])[0]);
        $this->assertArrayHasKey('amount', $booksAndTrades->trades(['book' => 'btc_mxn'])[0]);
        $this->assertArrayHasKey('maker_side', $booksAndTrades->trades(['book' => 'btc_mxn'])[0]);
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}
