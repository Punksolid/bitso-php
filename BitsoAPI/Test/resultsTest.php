<?php
namespace BitsoAPI\Test;
use BitsoAPI\results;
use BitsoAPI\bitso;
use \Datetime;

class resultsTest extends \PHPUnit_Framework_TestCase
{
  public const key = '';
  public const secret = '';

	function testProcessResults(){
    	$result = new Results();
    	$this->assertEquals($result->processResults(),1);

    }

 	public function testAvailableBooks()
 	{
 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

 		$fake_response = json_decode('{
        "success": true,
        "payload": [{
           "book": "btc_mxn",
           "minimum_amount": ".003",
           "maximum_amount": "1000.00",
           "minimum_price": "100.00",
           "maximum_price": "1000000.00",
           "minimum_value": "25.00",
           "maximum_value": "1000000.00"
        }, {
           "book": "eth_mxn",
           "minimum_amount": ".003",
           "maximum_amount": "1000.00",
           "minimum_price": "100.0",
           "maximum_price": "1000000.0",
           "minimum_value": "25.0",
           "maximum_value": "1000000.0"
        }]}');
       

 		$bitso->expects($this->any())
            ->method('available_books')
            ->will($this->returnValue($fake_response));

        $response = $bitso->available_books()->payload[0];


        $this->assertEquals($response->minimum_amount,(float)".003");
        $this->assertEquals($response->maximum_amount,(float)"1000.00");
        $this->assertEquals($response->minimum_price,(float)"100.00");
        $this->assertEquals($response->maximum_price,(float)"1000000.00");
        $this->assertEquals($response->minimum_value,(float)"25.00");
        $this->assertEquals($response->maximum_value,(float)"1000000.00");

    }

    public function testTicker()
 	{
 		
 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

 		$fake_response = json_decode(' {
        "success": true,
        "payload": {
            "book": "btc_mxn",
            "volume": "22.31349615",
            "high": "5750.00",
            "last": "5633.98",
            "low": "5450.00",
            "vwap": "5393.45",
            "ask": "5632.24",
            "bid": "5520.01",
            "created_at": "2016-04-08T17:52:31.000+00:00"
            }
        }');
       

 		$bitso->expects($this->any())
            ->method('ticker')
            ->will($this->returnValue($fake_response));

        $response = $bitso->ticker("btc_mxn")->payload;


        $this->assertEquals($response->volume,(float)"22.31349615");
        $this->assertEquals($response->ask,(float)"5632.24");

    }


	public function testOrderBook()
	{
 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

 		$fake_response = json_decode(' {
	    "success": true,
	    "payload": {
	        "asks": [{
	            "book": "btc_mxn",
	            "price": "5632.24",
	            "amount": "1.34491802"
	        },{
	            "book": "btc_mxn",
	            "price": "5633.44",
	            "amount": "0.4259"
	        },{
	            "book": "btc_mxn",
	            "price": "5642.14",
	            "amount": "1.21642"
	        }],
	        "bids": [{
	            "book": "btc_mxn",
	            "price": "6123.55",
	            "amount": "1.12560000"
	        },{
	            "book": "btc_mxn",
	            "price": "6121.55",
	            "amount": "2.23976"
	        }],
	        "updated_at": "2016-04-08T17:52:31.000+00:00",
	        "sequence": "27214"
	       }
	    }');
       

 		$bitso->expects($this->any())
            ->method('order_book')
            ->will($this->returnValue($fake_response));

		$response = $bitso->order_book("btc_mxn")->payload;

       	$this->assertInternalType("array", $response->asks);
        $this->assertEquals(sizeof($response->asks),3);
       	$this->assertInternalType("array", $response->bids);
        $this->assertEquals(sizeof($response->bids),2);
        $this->assertEquals($response->asks[0]->price, (float)"5632.24");
        $this->assertEquals($response->asks[0]->amount, (float)"1.34491802");
        $this->assertEquals($response->bids[0]->price, (float)"6123.55");
        $this->assertEquals($response->bids[0]->amount, (float)"1.12560000");
        $this->assertEquals($response->sequence, 27214);
        date_default_timezone_set('UTC');
        $date_time = new DateTime($response->updated_at);
        #$this->assertInstanceOf($response->updated_at,datetime);
        $this->assertEquals($date_time->format('Y'), 2016);
        $this->assertEquals($date_time->format('M'), "Apr");
        $this->assertEquals($date_time->format('d'), 8);
        $this->assertEquals($date_time->format('h'), 05);
        $this->assertEquals($date_time->format('i'), 52);
        $this->assertEquals($date_time->format('s'), 31);

        
 	}

 	public function testTrades()
	{
 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

 		$fake_response = json_decode('{
        "success": true,
        "payload": [{
           "book": "btc_mxn",
           "created_at": "2016-04-08T17:52:31.000+00:00",
           "amount": "0.02000000",
           "side": "buy",
           "price": "5545.01",
           "tid": 55845
        }, {
           "book": "btc_mxn",
           "created_at": "2016-04-08T17:52:31.000+00:00",
           "amount": "0.33723939",
           "side": "sell",
           "price": "5633.98",
           "tid": 55844
           }]
       }');
       

 		$bitso->expects($this->any())
            ->method('trades')
            ->will($this->returnValue($fake_response));

		$response = $bitso->trades("btc_mxn")->payload[0];

       # $this->assertInstanceOf($response->asks,Array());
        $this->assertEquals(sizeof($bitso->trades("btc_mxn")->payload),2);
        $this->assertEquals($response->price, (float)"5545.01");

        date_default_timezone_set('UTC');
        $date_time = new DateTime($response->created_at);
        #$this->assertInstanceOf($response->updated_at,datetime);
        $this->assertEquals($date_time->format('Y'), 2016);
        $this->assertEquals($date_time->format('M'), "Apr");
        $this->assertEquals($date_time->format('d'), 8);
        $this->assertEquals($date_time->format('h'), 05);
        $this->assertEquals($date_time->format('i'), 52);
        
 	}

 	public function testAccountStatus()
	{
 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

 		$fake_response = json_decode('{
        "success": true,
        "payload": {
            "client_id": "1234",
            "status": "active",
            "daily_limit": "5300.00",
            "monthly_limit": "32000.00",
            "daily_remaining": "3300.00",
            "monthly_remaining": "31000.00",
            "cellphone_number": "verified",
            "official_id": "submitted",
            "proof_of_residency": "submitted",
            "signed_contract": "unsubmitted",
            "origin_of_funds": "unsubmitted"
        }
    }');

 		$bitso->expects($this->any())
            ->method('account_status')
            ->will($this->returnValue($fake_response));

        $response = $bitso->account_status()->payload;

      
        $this->assertEquals($response->client_id, "1234");
        #$this->assertInternalType("float", $response->daily_limit);
        $this->assertEquals($response->daily_limit, (float)"5300.00");
        $this->assertEquals($response->monthly_limit, (float)"32000.00");
        $this->assertEquals($response->daily_remaining, (float)"3300.00");
        $this->assertEquals($response->monthly_remaining, (float)"31000.00");

    }

    public function testBalances()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

 		$fake_response = json_decode('{
            "success": true,
            "payload": {
                "balances": [{
                    "currency": "mxn",
                    "total": "100.1234",
                    "locked": "25.1234",
                    "available": "75.0000"
                }, {
                    "currency": "btc",
                    "total": "4.12345678",
                    "locked": "25.00000000",
                    "available": "75.12345678"
                }, {
                    "currency": "cop",
                    "total": "500000.1234",
                    "locked": "40000.1234",
                    "available": "10000.0000"
                }]
            }
        }');

 		$bitso->expects($this->any())
            ->method('balances')
            ->will($this->returnValue($fake_response));

       # print_r($bitso->url_request("As","as","As","As"));
       # print_r($bitso->balances());
        $response = $bitso->balances()->payload;

      
        $this->assertEquals($response->balances[0]->available, (float)"75.0000");
        $this->assertEquals($response->balances[1]->available, (float) "75.12345678");
        $this->assertEquals($response->balances[0]->locked, (float)"25.1234");
        $this->assertEquals($response->balances[0]->currency, "mxn");
       

    }
    public function testFees()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

 		$fake_response = json_decode('{
            "success": true,
            "payload": {
                "fees": [{
                    "book": "btc_mxn",
                    "fee_decimal": "0.0001",
                    "fee_percent": "0.01"
                }, {
                    "book": "eth_mxn",
                    "fee_decimal": "0.001",
                    "fee_percent": "0.1"
                }]
            }
        }');

 		$bitso->expects($this->any())
            ->method('fees')
            ->will($this->returnValue($fake_response));

        $response = $bitso->fees()->payload;

      
        $this->assertEquals($response->fees[0]->book, "btc_mxn");
        $this->assertEquals($response->fees[0]->fee_decimal, (float)"0.0001");
        $this->assertEquals($response->fees[0]->fee_percent, (float)"0.01");
        
      
    }
    public function testLedger()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

 		$root = realpath($_SERVER["DOCUMENT_ROOT"]);
    $string = file_get_contents($root."/BitsoAPI/Test/ledger.json");
 		
		$fake_response = json_decode($string);
	 

 		$bitso->expects($this->any())
            ->method('ledger')
            ->will($this->returnValue($fake_response));

        $response = $bitso->ledger("btc_mxn")->payload;

        #print_r($response[0]->balance_updates[1]->amount);

      	// for($i=0; $i<sizeof($response);$i++){
      	// 	for($j=0;$j<sizeof($response[$j]->balance_updates);$j++){
      	// 		print_r($response[$j]->balance_updates[$j]->amount);
      	// 		#$this->assertInternalType("float",$response[$j]->balance_updates[$j]->amount);
      	// 	}
      	// }

        $this->assertEquals($response[0]->balance_updates[1]->amount, (float)"1013.540958479115");
      
    }


        public function testWithdrawals()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

 		$root = realpath($_SERVER["DOCUMENT_ROOT"]);
    $string = file_get_contents($root."/BitsoAPI/Test/withdrawals.json");

		$fake_response = json_decode($string);
	 

 		$bitso->expects($this->any())
            ->method('withdrawals')
            ->will($this->returnValue($fake_response));

        $response = $bitso->withdrawals("btc_mxn")->payload;

        #print_r($response[0]->balance_updates[1]->amount);

      	// for($i=0; $i<sizeof($response);$i++){
      	// 	for($j=0;$j<sizeof($response[$j]->balance_updates);$j++){
      	// 		print_r($response[$j]->balance_updates[$j]->amount);
      	// 		#$this->assertInternalType("float",$response[$j]->balance_updates[$j]->amount);
      	// 	}
      	// }

      	$this->assertEquals($response[0]->amount, (float)'0.48650929');
      	$this->assertEquals($response[1]->amount, (float)'2612.70');
     	$this->assertEquals($response[2]->amount, (float)'500.00');  
    }


    public function testFundings()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

 		$root = realpath($_SERVER["DOCUMENT_ROOT"]);
 		$string = file_get_contents($root."/BitsoAPI/Test/fundings.json");
		$fake_response = json_decode($string);
	 

 		$bitso->expects($this->any())
            ->method('fundings')
            ->will($this->returnValue($fake_response));

        $response = $bitso->fundings("btc")->payload;

        #print_r($response[0]->balance_updates[1]->amount);

      	// for($i=0; $i<sizeof($response);$i++){
      	// 	for($j=0;$j<sizeof($response[$j]->balance_updates);$j++){
      	// 		print_r($response[$j]->balance_updates[$j]->amount);
      	// 		#$this->assertInternalType("float",$response[$j]->balance_updates[$j]->amount);
      	// 	}
      	// }

      	$this->assertEquals($response[0]->amount, (float)'0.48650929');
      	$this->assertEquals($response[1]->amount, (float)'300.15');
    }

        public function testUserTrades()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

		$fake_response = json_decode('{
        "success": true,
        "payload": [{
            "book": "btc_mxn",
            "major": "-0.25232073",
            "created_at": "2016-04-08T17:52:31.000+00:00",
            "minor": "1013.540958479115",
            "fees_amount": "-10.237787459385",
            "fees_currency": "mxn",
            "price": "4057.45",
            "tid": 51756,
            "oid": "19vaqiv72drbphig81d3y1ywri0yg8miihs80ng217drpw7xyl0wmytdhtby2ygk",
            "side": "sell"
        }, {
            "book": "eth_mxn",
            "major": "4.86859395",
            "created_at": "2016-04-08T17:52:31.000+00:00",
            "minor": "-626.77",
            "fees_amount": "-0.04917771",
            "fees_currency": "btc",
            "price": "127.45",
            "tid": 51757,
            "oid": "19vaqiv72drbphig81d3y1ywri0yg8miihs80ng217drpw7xyl0wmytdhtby2ygk",
            "side": "buy"
        }]
    }');
	 

 		$bitso->expects($this->any())
            ->method('user_trades')
            ->will($this->returnValue($fake_response));

        $response = $bitso->user_trades("btc_mxn")->payload;

        #print_r($response[0]->balance_updates[1]->amount);

      	// for($i=0; $i<sizeof($response);$i++){
      	// 	for($j=0;$j<sizeof($response[$j]->balance_updates);$j++){
      	// 		print_r($response[$j]->balance_updates[$j]->amount);
      	// 		#$this->assertInternalType("float",$response[$j]->balance_updates[$j]->amount);
      	// 	}
      	// }

      	$this->assertEquals($response[0]->minor, (float)"1013.540958479115");
      	$this->assertEquals($response[0]->major, (float)"-0.25232073");
      	$this->assertEquals($response[0]->tid, 51756);
      	$this->assertEquals($response[0]->oid, "19vaqiv72drbphig81d3y1ywri0yg8miihs80ng217drpw7xyl0wmytdhtby2ygk");

      	
    }

    public function testOpenOrders()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

		$fake_response = json_decode('{
        "success": true,
        "payload": [{
            "book": "btc_mxn",
            "original_amount": "0.01000000",
            "unfilled_amount": "0.00500000",
            "original_value": "56.0",
            "created_at": "2016-04-08T17:52:31.000+00:00",
            "updated_at": "2016-04-08T17:52:51.000+00:00",
            "price": "5600.00",
            "oid": "543cr2v32a1h684430tvcqx1b0vkr93wd694957cg8umhyrlzkgbaedmf976ia3v",
            "side": "buy",
            "status": "partial-fill",
            "type": "limit"
        }, {
            "book": "btc_mxn",
            "original_amount": "0.12680000",
            "unfilled_amount": "0.12680000",
            "original_value": "507.2",
            "created_at": "2016-04-08T17:52:31.000+00:00",
            "updated_at": "2016-04-08T17:52:41.000+00:00",
            "price": "4000.00",
            "oid": "qlbga6b600n3xta7actori10z19acfb20njbtuhtu5xry7z8jswbaycazlkc0wf1",
            "side": "sell",
            "status": "open",
            "type": "limit"
        }, {
            "book": "btc_mxn",
            "original_amount": "1.12560000",
            "unfilled_amount": "1.12560000",
            "original_value": "6892.66788",
            "created_at": "2016-04-08T17:52:31.000+00:00",
            "updated_at": "2016-04-08T17:52:41.000+00:00",
            "price": "6123.55",
            "oid": "d71e3xy2lowndkfmde6bwkdsvw62my6058e95cbr08eesu0687i5swyot4rf2yf8",
            "side": "sell",
            "status": "open",
            "type": "limit"
        }]
    }');
	 

 		$bitso->expects($this->any())
            ->method('open_orders')
            ->will($this->returnValue($fake_response));

        $response = $bitso->open_orders("btc_mxn")->payload;

      	$this->assertEquals($response[0]->original_amount, (float)"0.01000000");
      	$this->assertEquals($response[0]->price, (float)"5600.00");
      	$this->assertEquals($response[0]->type, "limit");
      	$this->assertEquals($response[0]->oid, "543cr2v32a1h684430tvcqx1b0vkr93wd694957cg8umhyrlzkgbaedmf976ia3v");
      	$this->assertEquals($response[1]->status, "open");

      	
    }

    public function testLookupOrder()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

		$fake_response = json_decode('{
        "success": true,
        "payload": [{
            "book": "btc_mxn",
            "original_amount": "0.01000000",
            "unfilled_amount": "0.00500000",
            "original_value": "56.0",
            "created_at": "2016-04-08T17:52:31.000+00:00",
            "updated_at": "2016-04-08T17:52:51.000+00:00",
            "price": "5600.00",
            "oid": "543cr2v32a1h684430tvcqx1b0vkr93wd694957cg8umhyrlzkgbaedmf976ia3v",
            "side": "buy",
            "status": "partial-fill",
            "type": "limit"
        }, {
            "book": "btc_mxn",
            "original_amount": "0.12680000",
            "unfilled_amount": "0.12680000",
            "original_value": "507.2",
            "created_at": "2016-04-08T17:52:31.000+00:00",
            "updated_at": "2016-04-08T17:52:41.000+00:00",
            "price": "4000.00",
            "oid": "qlbga6b600n3xta7actori10z19acfb20njbtuhtu5xry7z8jswbaycazlkc0wf1",
            "side": "sell",
            "status": "open",
            "type": "limit"
        }]
    }');
	 

 		$bitso->expects($this->any())
            ->method('lookup_order')
            ->will($this->returnValue($fake_response));

        $response = $bitso->lookup_order(['543cr2v32a1h684430tvcqx1b0vkr93wd694957cg8umhyrlzkgbaedmf976ia3v','qlbga6b600n3xta7actori10z19acfb20njbtuhtu5xry7z8jswbaycazlkc0wf1'])->payload;

      	$this->assertEquals($response[0]->original_amount, (float)"0.01000000");
      	$this->assertEquals($response[0]->price, (float)"5600.00");
      	$this->assertEquals($response[0]->type, "limit");


      	
    }

    public function testCancelOrder()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

		$fake_response = json_decode('{
            "success": true,
            "payload":[
                "543cr2v32a1h684430tvcqx1b0vkr93wd694957cg8umhyrlzkgbaedmf976ia3v",
                "qlbga6b600n3xta7actori10z19acfb20njbtuhtu5xry7z8jswbaycazlkc0wf1",
                "d71e3xy2lowndkfmde6bwkdsvw62my6058e95cbr08eesu0687i5swyot4rf2yf8"
                ]
        }');
	 

 		$bitso->expects($this->any())
            ->method('lookup_order')
            ->will($this->returnValue($fake_response));

        $response = $bitso->lookup_order(['543cr2v32a1h684430tvcqx1b0vkr93wd694957cg8umhyrlzkgbaedmf976ia3v','qlbga6b600n3xta7actori10z19acfb20njbtuhtu5xry7z8jswbaycazlkc0wf1',"d71e3xy2lowndkfmde6bwkdsvw62my6058e95cbr08eesu0687i5swyot4rf2yf8"])->payload;

      	$this->assertInternalType("array",$response);
      	$this->assertEquals(sizeof($response),3);
      	
    }

    public function testPlaceOrder()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

		$fake_response = json_decode('{
        "success": true,
        "payload": {
            "oid": "qlbga6b600n3xta7actori10z19acfb20njbtuhtu5xry7z8jswbaycazlkc0wf1"
        }
    }');
	 

 		$bitso->expects($this->any())
            ->method('place_order')
            ->will($this->returnValue($fake_response));

        $response = $bitso->place_order(['book'=>'btc_mxn', 'side'=>'buy', 'order_type'=>'limit', 'major'=>'0.1', 'price'=>'5600'])->payload;

      	
      	$this->assertEquals($response->oid,"qlbga6b600n3xta7actori10z19acfb20njbtuhtu5xry7z8jswbaycazlkc0wf1");
      	
    }

    public function testFundingDestination()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

		$fake_response = json_decode('{
            "success": true,
            "payload": {
                "account_identifier_name": "SPEI CLABE",
                "account_identifier": "646180115400346012"             
            }
        }');
	 

 		$bitso->expects($this->any())
            ->method('funding_destination')
            ->will($this->returnValue($fake_response));

        $response = $bitso->funding_destination("mxn")->payload;

      	$this->assertEquals($response->account_identifier_name,"SPEI CLABE");
      	
    }

    public function testBtcWithdrawal()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

		$fake_response = json_decode('{
            "success": true,
            "payload": {
                "wid": "c5b8d7f0768ee91d3b33bee648318688",
                "status": "pending",
                "created_at": "2016-04-08T17:52:31.000+00:00",
                "currency": "btc",
                "method": "Bitcoin",
                "amount": "0.48650929",
                "details": {
                    "withdrawal_address": "3EW92Ajg6sMT4hxK8ngEc7Ehrqkr9RoDt7",
                    "tx_hash": null
                }
            }
        }');
	 

 		$bitso->expects($this->any())
            ->method('btc_withdrawal')
            ->will($this->returnValue($fake_response));

        $response = $bitso->btc_withdrawal(['amount'=>'0.48650929', 'address'=>'3EW92Ajg6sMT4hxK8ngEc7Ehrqkr9RoDt7'])->payload;

      	$this->assertEquals($response->amount,(float)"0.48650929");
      	
    }

    public function testEthWithdrawal()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

		$fake_response = json_decode('{
        "success": true,
        "payload": {
            "wid": "c5b8d7f0768ee91d3b33bee648318698",
            "status": "pending",
            "created_at": "2016-04-08T17:52:31.000+00:00",
            "currency": "btc",
            "method": "Ether",
            "amount": "10.00",
            "details": {
                "withdrawal_address": "0x55f03a62acc946dedcf8a0c47f16ec3892b29e6d",
                "tx_hash": null
            }
        }
    }');
	 

 		$bitso->expects($this->any())
            ->method('eth_withdrawal')
            ->will($this->returnValue($fake_response));

        $response = $bitso->eth_withdrawal(['amount'=>'10.00', 'address'=>'0x55f03a62acc946dedcf8a0c47f16ec3892b29e6d'])->payload;

      	$this->assertEquals($response->amount,(float)"10.00");
      	
    }

    public function testRippleWithdrawal()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

		$fake_response = json_decode('{
            "success": true,
            "payload": {
                "wid": "c5b8d7f0768ee91d3b33bee648318688",
                "status": "pending",
                "created_at": "2016-04-08T17:52:31.000+00:00",
                "currency": "btc",
                "method": "Ripple",
                "amount": "0.48650929",
                "details": {
                    "withdrawal_address": "rG1QQv2nh2gr7RCZ1P8YYcBUKCCN633jCn",
                    "tx_id": null
                }
            }
        }');
	 

 		$bitso->expects($this->any())
            ->method('ripple_withdrawal')
            ->will($this->returnValue($fake_response));

        $response = $bitso->ripple_withdrawal(['type' => 'btc', 'amount'=>'0.48650929', 'address'=>'rG1QQv2nh2gr7RCZ1P8YYcBUKCCN633jCn'])->payload;

      	$this->assertEquals($response->amount,(float)"0.48650929");
      	
    }

    public function testSpeiWithdrawal()
	{

 		$bitso = $this->getMockBuilder(\BitsoAPI\bitso::class)
 		->setConstructorArgs([self::key, self::secret])
 		->getMock();

		$fake_response = json_decode('{
            "success": true,
            "payload": {
                "wid": "p4u8d7f0768ee91d3b33bee6483132i8",
                "status": "pending",
                "created_at": "2016-04-08T17:52:31.000+00:00",
                "currency": "mxn",
                "method": "SPEI Transfer",
                "amount": "300.15",
                "details": {
                    "sender_name": "JUAN ESCUTIA",
                    "receive_clabe": "012610001967722183",
                    "sender_clabe": "646180115400467548",
                    "numeric_reference": "80416",
                    "concepto": "Tacos del viernes",
                    "clave_rastreo": null,
                    "beneficiary_name": "FRANCISCO MARQUEZ"
                }
            }
        }');
	 

 		$bitso->expects($this->any())
            ->method('spei_withdrawal')
            ->will($this->returnValue($fake_response));

        $response = $bitso->spei_withdrawal(['amount'=>'0.48650929', 'first_names'=>"FRANCISCO", 'last_names'=>"MARQUEZ", 'clabe'=>"012610001967722183"])->payload;

      	$this->assertEquals($response->amount,(float)"300.15");
      	
    }



}