<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class mnemonic extends tests_common {
    
    const mnemonic = 'nothing edit remember box goddess local cabin term social destroy inner universe candy maze horse zone step direct captain patch cream output large ticket early cactus clap curious link quarter stamp guitar bone believe subject lawsuit funny infant creek width trigger talent kick payment habit example game shrimp';
    
    public function runtests() {
        $this->test_mnemonic_btc_mainnet();
        $this->test_mnemonic_btc_testnet();
        $this->test_mnemonic_btc_regtest();
    }
    
    protected function test_mnemonic_btc_mainnet() {
        
        // check xprv derivation results in correct addresses.
        $params = ['mnemonic' => self::mnemonic,
                   'coin' => 'BTC',
                   'numderive' => 1,
                   'cols' => 'address',
                   'format' => 'list',
                   ];
        
        // tests default value for --key-type
        $address = $this->derive_params( $params );
        $addr_correct = '1JxwtAMZYMiT2pR6DCRDBpqsoZ4FdTxoSS';
        $this->eq( $address, $addr_correct, 'btc mainnet xprv addr default' );

        $params['key-type'] = 'x';
        $address = $this->derive_params( $params );
        $this->eq( $address, $addr_correct, 'btc mainnet xprv addr' );
        
        $params['key-type'] = 'y';
        $address = $this->derive_params( $params );
        $addr_correct = '38sJZN3XwsMRVAQZcnWUfeEpHqNHQqZgJi';
        $this->eq( $address, $addr_correct, 'btc mainnet yprv addr' );
        
        $params['key-type'] = 'z';
        $address = $this->derive_params( $params );
        $addr_correct = 'bc1qrrtm560fy4zsv5lkcd38ytycyu2da0r60ajufz';
        $this->eq( $address, $addr_correct, 'btc mainnet zprv addr' );        
    }

    protected function test_mnemonic_btc_testnet() {
        
        // check xprv derivation results in correct addresses.
        $params = ['mnemonic' => self::mnemonic,
                   'coin' => 'BTC-test',
                   'numderive' => 1,
                   'cols' => 'address',
                   'format' => 'list',
                   ];
        
        $address = $this->derive_params( $params );
        $addr_correct = 'mxcgHPeZvhQYypoFqr5GRzQeogjc16dWya';
        $this->eq( $address, $addr_correct, 'btc testnet xprv addr' );
        
        $params['key-type'] = 'y';
        $address = $this->derive_params( $params );
        $addr_correct = '2NAGHgSwxHufJVmvB2ZncTqFvnmk9R7nX6e';
        $this->eq( $address, $addr_correct, 'btc testnet yprv addr' );
        
        $params['key-type'] = 'z';
        $address = $this->derive_params( $params );
        $addr_correct = 'tb1q086tqpwxkk4xdz3jw7pxwgluap0vu7hrvz0f9t';
        $this->eq( $address, $addr_correct, 'btc testnet zprv addr' );        
    }

    protected function test_mnemonic_btc_regtest() {
        
        // check xprv derivation results in correct addresses.
        $params = ['mnemonic' => self::mnemonic,
                   'coin' => 'BTC-regtest',
                   'numderive' => 1,
                   'cols' => 'address',
                   'format' => 'list',
                   ];
        
        $address = $this->derive_params( $params );
        $addr_correct = 'mxcgHPeZvhQYypoFqr5GRzQeogjc16dWya';
        $this->eq( $address, $addr_correct, 'btc regtest xprv addr' );

/* y,z prefix values not yet known for regtest.       
        $params['key-type'] = 'y';
        $address = $this->derive_params( $params );
        $addr_correct = '2NAGHgSwxHufJVmvB2ZncTqFvnmk9R7nX6e';
        $this->eq( $address, $addr_correct, 'btc regtest yprv addr' );
        
        $params['key-type'] = 'z';
        $address = $this->derive_params( $params );
        $addr_correct = 'bcrt1q086tqpwxkk4xdz3jw7pxwgluap0vu7hrwtkyjz';
        $this->eq( $address, $addr_correct, 'btc regtest zprv addr' );
 */
    }
    
}
