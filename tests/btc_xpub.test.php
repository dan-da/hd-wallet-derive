<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class btc_xpub extends tests_common {
    
    public function runtests() {
        $this->test_derive();
    }
    
    protected function test_derive() {

        $xprv = 'xprvA19DfBgveHgn7vJLkdhvfnDhrchn8S9PyWTuNxFvqQamejnM3dzNUqm8nueewkAZDHPL5JLEz56xDbRd1CR5MxXQ2bLbjK3nKEsqyWazFU9';
        $xpub = 'xpub6E8a4hDpUfF5LQNorfEw2vASQeYGXtsFLjPWBLfYPk7kXY7VbBJd2e5ceAzn72Ti2x89YsTyYb7wi7T9LSiMPhirdV6gZ7ff8eShQtcKz7q';
        $addrs = [
            '3B6Y69iZiEFmneuNC82Xec2uvBhL8uNSvw',
            'bc1qq8xyswquhhfepa845areyk9j4jamdzsef6huem',
        ];
        
        // check xprv derivation results in correct addresses.
        $params = ['key' => $xprv,];        
        $results = $this->derive_params( $params );
        $this->eq( @$results[0]['address'], $addrs[0], 'xprv m/0' );
        $this->eq( @$results[1]['address'], $addrs[1], 'xprv m/1' );
        
        // check xpub derivation results in correct addresses.
        $params['key'] = $xpub;
        $results = $this->derive_params( $params );
        $this->eq( @$results[0]['address'], $addrs[0], 'xpub m/0' );
        $this->eq( @$results[1]['address'], $addrs[1], 'xpub m/1' );
    }
}
