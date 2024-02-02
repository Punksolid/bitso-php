# bitso php #

A php wrapper for the [Bitso API](https://bitso.com/api_info/). 


# Installation #
To install the bitso php api wrapper:
`$ composer require punksolid/bitso:*`
or equivalently in your composer.json file:
```json
{
    "require": {
        "punksolid/bitso": "dev-master"
    }
}
```

# Public API Usage #

```php
require 'vendor/autoload.php'

$bitso = new BitsoAPI\Bitso();
```
The production API is set as default, to use Dev API:

```php
require 'vendor/autoload.php'

$bitso = new BitsoAPI\Bitso('','',$url);
```

# Private API Usage #

```php
require 'vendor/autoload.php'

$bitso = new BitsoAPI\Bitso(API_KEY, API_SECRET, URL);
```

See note above on how to use Dev API
# Note on Parameters #
Parameters must be arrays in the form of:
 ``` php
 ["arg1"=>"value","arg2"=>"value"]
 ```
 
Moreover, for methods such as lookup order, cancel order, there is no array of parameters, but there must be an array with order ids:

 ```php
 ['id','id','id]
 ```
 See specific calls for detailed examples.

# Public Calls #

### Available Books ###

```php
## Order books available on Bitso
$books = $bitso->available_books();

##sample usage for minimum amount of btc_mxn (0)
$books['payload'][0]->minimum_amount;
##for minimum amount of eth_mxn (1)
$books['payload'][1]->minimum_amount;
```

### Ticker ###

```php
## Ticker information
## Parameters
## [book] - Specifies which book to use
##                  - string
$ticker = $bitso->ticker(["book"=>"btc_mxn"]);

##sample usage for ask price of btc_mxn
$ticker['payload']->ask;
 ```

### Order Book ###

```php
## Public order book
## Parameters
## [book] - Specifies which book to use
##                  - string
## [aggregate = True] - Group orders with the same price
##                - boolean

$ob = $bitso->orderBook(["book"=>"btc_mxn","aggregate"=> "True"]);

## sample usage for array of asks for btc_mxn
$ob['payload']->asks;
```

### Trades ###

```php
## Public trades
## Parameters
## [book = 'btc_mxn'] - Specifies which book to use
##                    - str
## [marker = None] - Returns objects that are older or newer (depending on 'sort’) than the object with this ID
##                    - str
## [sort = 'desc'] - Specifies ordering direction of returned objects (asc, desc)
##                    - str
## [limit = '25'] - Specifies number of objects to return. (Max is 100)
##                    - str

$trades = $bitso->trades(["book"=>"btc_mxn"]);

##sample usage to get array of trades 
$trades['payload'];

```


# Private calls #

Private endpoints are used to manage your account and your orders. These requests must be signed
with your [Bitso credentials](https://bitso.com/api_info#generating-api-keys) 


### Account Status ###

```php
## Your account status
$status = $bitso->accountStatus();

##sample usage for account status array
$status['payload'];

```



### Account Balances ###

```php
## Your account balances
$balances = $bitso->balances();

##sample usage for account balances array
$balances['payload']->balances;

```

### Fees ###

```php
## Your trade fees
$fees = $bitso->fees();

##sample usage for fees array
$fees['payload'];

```

### Ledger ###
```php
## A ledger of your historic operations.
## Parameters
## [marker]    - Returns objects that are older or newer (depending on 'sort’) than the object with this ID
##                 - string
## [limit = 25]   - Limit result to that many transactions
##                 - int
## [sort = 'desc'] - Sorting by datetime
##                 - string - 'asc' or
##                 - 'desc'

$ledger = $bitso->ledger(["limit"=>"15"]);

##sample usage for ledger array of size determined by limit
$ledger['payload'];
```

### Withdrawals ###

```php
## Detailed info on your fund withdrawals.
## Parameters
## [wids]    - Specifies which withdrawal objects to return by IDs
##                 - list
## [marker]    - Returns objects that are older or newer (depending on 'sort’) than the object with this ID
##                 - string
## [limit = 25]   - Limit result to that many transactions
##                 - int
## [sort = 'desc'] - Sorting by datetime
##                 - string - 'asc' or
##                 - 'desc'

$withdrawals = $bitso->withdrawals(["limit"=>"20","wids"=>"ids"));

##sample usage for withdrawals array of size determined by limit
$withdrawals['payload'];
```

### Fundings ###

```php
## Detailed info on your fundings.
## Parameters
## [fids]    - Specifies which funding objects to return by IDs
##                 - list
## [marker]    - Returns objects that are older or newer (depending on 'sort’) than the object with this ID
##                 - string
## [limit = 25]   - Limit result to that many transactions
##                 - int
## [sort = 'desc'] - Sorting by datetime
##                 - string - 'asc' or
##                 - 'desc'

$fundings = $bitso->fundings(["limit"=>"20","fids"->"ids"));

##sample usage for fundings array of size determined by limit
$fundings['payload'];
```




### User Trades ###

```php
## Your trades
## Parameters
## [book = all]- Specifies which book to use
##                 - string
## [marker]    - Returns objects that are older or newer (depending on 'sort’) than the object with this ID
##                 - string
## [limit = 25]   - Limit result to that many transactions
##                 - int
## [sort = 'desc'] - Sorting by datetime
##                 - string - 'asc' or
##                 - 'desc'

$user_trades = $bitso->userTrades(['book'=>'btc_mxn']);

##sample usage for getting array of user trades
$user_trades['payload'];


```

### Open Orders ###

```php
## Returns a list of the user’s open orders
## Parameters
## [book] - Specifies which book to use
##                    - str
## [marker]    - Returns objects that are older or newer (depending on 'sort’) than the object with this ID
##                 - string
## [limit = 25]   - Limit result to that many transactions
##                 - int
## [sort = 'desc'] - Sorting by datetime
##                 - string - 'asc' or
##                 - 'desc'
$open_orders = $bitso->open_orders(['book'=>'btc_mxn']);

##sample usage for getting array of open orders
$open_orders['payload'];
```

### Lookup Order ###

```php
## Returns a list of details for 1 or more orders
## Parameters
## order_ids -  A list of Bitso Order IDs.
##          - string
$lookup_order = $bitso->lookup_order([oids]);

##sample usage for getting status of a specific order (if one oids is passed in)
$lookup_order['payload']->status;

##sample usage for getting status of a specific order (if more than one oids are passed in)
$lookup_order['payload'][i]->status;
```

### Cancel Order ###

```php
## Cancels an open order
## Parameters
## order_id -  A Bitso Order ID.
##          - string
$cancel_order =  $bitso->cancel_order([oids]);
```

### Place Order ###

```php
## Places a buy limit order.
## [book] - Specifies which book to use (btc_mxn, eth_mxn)
##                    - str
## [side] - the order side (buy, sell) 
##                    - str
## [order_type] - the order type (limit, market) 
##                    - str
## amount - Amount of major currency to buy.
##        - string
## major  - The amount of major currency for this order. An order must be specified in terms of major or minor, never both.
##        - string. Major denotes the cryptocurrency, in our case Bitcoin (BTC) or Ether (ETH).
## minor  - The amount of minor currency for this order. Minor denotes fiat currencies, in our case Mexican Peso (MXN)
##        - string
## price  - Price per unit of major. For use only with limit orders
##        - string

$place_order = $bitso->placeOrder(['book'  => 'btc_mxn', 'side'  => 'buy', 'major' => '.01', 'price' => '1000', type'  => 'limit']);
```


### Funding Destination Address ###

```php
## Gets a Funding destination address to fund your account
## fund_currency  - Specifies the currency you want to fund your account with (btc, eth, mxn)
##                            - str
$funding_destination = $bitso->fundingDestination(['fund_currency'=>'eth']);
```


### Bitcoin Withdrawal ###

```php
## Triggers a bitcoin withdrawal from your account
## amount  - The amount of BTC to withdraw from your account
##         - string
## address - The Bitcoin address to send the amount to
##         - string

$btc_withdrawal = $bitso->btc_withdrawal(['amount'=>'.05','address'  => '']);
```

### Ether Withdrawal ###

```php
## Triggers a bitcoin withdrawal from your account
## amount  - The amount of BTC to withdraw from your account
##         - string
## address - The Bitcoin address to send the amount to
##         - string

$eth_withdrawal = $bitso->eth_withdrawal(['amount'  => '.05','address'  => '']);

```



### Ripple Withdrawal ###

```php
## Triggers a ripple withdrawal from your account
## currency  - The currency to withdraw
##         - string
## amount  - The amount of BTC to withdraw from your account
##         - string
## address - The ripple address to send the amount to
##         - string

$ripple_withdrawal = $bitso->ripple_withdrawal(['currency'=>'MXN','amount'=> '.05','address'  => '']);

```



### Bank Withdrawal (SPEI) ###

```php
## Triggers a SPEI withdrawal from your account. These withdrawals are
##   immediate during banking hours (M-F 9:00AM - 5:00PM Mexico City Time).
##
## amount  - The amount of MXN to withdraw from your account
##         - string
## recipient_given_names - The recipient’s first and middle name(s)
##         - string
## recipient_family_names - The recipient’s last name)
##         - string
## clabe - The CLABE number where the funds will be sent to
##         - string
## notes_ref - The alpha-numeric reference number for this SPEI
##         - string
## numeric_ref - The numeric reference for this SPEI
##         - string

$spei_withdrawal = $bitso->spei_withdrawal(['amount'  => '105','recipient_given_names'  => 'Andre Pierre','recipient_family_names'=>'Gignac', 'clabe'=>'CLABE','notes_ref'=>'NOTES_REF','numeric_ref'=>'NUMERIC REF']);

```

## Testing ##
To test the library, after installing, write in your API Keys in either the bitso.php file or the two test files, then go to the root folder of the repository and run:
```
./vendor/bin/phpunit
```
Remember to input API Keys to test with, as the test files have empty slots for keys.
