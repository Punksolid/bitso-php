<?php
namespace BitsoAPI;
include('bitso.php'); 
// your api credentials
$key = '';
$secret = '';

$bitso = new bitso($key, $secret,"https://dev.bitso.com/api/v3");
$order_book = $bitso->order_book(['book'=>'btc_mxn', 'aggregate' => 'True']);
$ticker = $bitso->ticker(['book'=>'btc_mxn']);
$trades = $bitso->trades(['book'=>'btc_mxn', 'limit' => '2']);
$available_books = $bitso->available_books();
$account_status = $bitso->account_status();
$balances = $bitso->balances();
$fees = $bitso->fees();
$ledger = $bitso->ledger(['limit'=>'10']);
$withdrawals = $bitso->withdrawals(['limit'=>'10']);
$fundings = $bitso->fundings(['limit'=>'1']);
$user_trades = $bitso->user_trades(['book'=>'btc_mxn']);
$open_orders = $bitso->open_orders(['book'=>'btc_mxn']);
$place_order = $bitso->place_order(['book'  => 'btc_mxn', 'side'  => 'buy', 'major' => '.01', 'price' => '1000', 'type'  => 'limit']);
$id = $place_order->payload->oid; 
$lookup_order = $bitso->lookup_order([$id]);
#NEED TO IMPLEMENT ALL
$cancel_order =  $bitso->cancel_order([$id, $id, $id]);
$funding_destination = $bitso->funding_destination(['fund_currency'=>'eth']);
print_r($funding_destination);
// $btc_withdrawal = $bitso->btc_withdrawal(array('amount'  => '.05',
//                               'address'  => ''));
// $eth_withdrawal = $bitso->eth_withdrawal(array('amount'  => '.05',
//                               'address'  => ''));
// $ripple_withdrawal = $bitso->ripple_withdrawal(array('currency'=>'MXN','amount'  => '.05','address'  => ''));
// $spei_withdrawal = $bitso->spei_withdrawal(array('amount'  => '105',
//                               'recipient_given_names'  => 'Andre Pierre','recipient_family_names'=>'Gignac', 'clabe'=>'6969696969696969696969','notes_ref'=>'6969696969','numeric_ref'=>'6969696969'));
echo "Ticker: ".$ticker->success."\n";
echo "Trades: ".$trades->success."\n";
echo "Available Books: ".$available_books->success."\n";
echo "Account Status: ".$account_status->success."\n";
echo "Fees: ".$fees->success."\n";
echo "Balances: ".$balances->success."\n";
echo "Ledger: ".$ledger->success."\n";
echo "Withdrawals: ".$withdrawals->success."\n";
echo "Fundings: ".$fundings->success."\n";
echo "User Trades: ".$user_trades->success."\n";
echo "Open Orders: ".$open_orders->success."\n";
echo "Place Order: ".$place_order->success."\n";
echo "Lookup Order: ".$lookup_order->success."\n";
echo "Cancel Order: ".$cancel_order->success."\n";
echo "Funding Destination: ".$funding_destination->success."\n";
// echo "Bitcoin Withdrawal: ".$btc_withdrawal->success."\n";
// echo "Ether Withdrawal: ".$eth_withdrawal->success."\n";
// echo "Ripple Withdrawal: ".$ripple_withdrawal->success."\n";
// echo "SPEI Withdrawal: ".$spei_withdrawal->success."\n";