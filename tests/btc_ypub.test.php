<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class btc_ypub extends tests_common {
    
    public function runtests() {
        $this->test_p2wpkh_in_p2sh();
    }
    
    protected function test_p2wpkh_in_p2sh() {

        $prv = 'yprvAE5yw8bPkzM3uM9zUkZAhqSBagQBNJ7M5mqqkenkgAdisHZ7jvmhMqFpGcabLZb8ksw7tqZAbGph82KP6DWo6H1digrekHiM1WZiiFphpmP';
        $pub = 'ypub6T5LLe8HbMuM7qETan6B4yNv8iEfmkqCSzmSZ3CNEWAhk5tGHU5wudaJ7uJoWvgxAZ4V8UQurJbq75CnhvHMBFRxhnAkL68NBqEBgdqtpKu';
        $addrs = [
            '3QK3LnzVMUFZVKxtGeTixbSoVZLi16p3bw',
            '3MnjYuAaiKUWHMNTRoTV6uncRuWgud3qPG',
        ];
        
        // check xprv derivation results in correct addresses.
        $params = ['key' => $prv,];        
        $results = $this->derive_params( $params );
        $this->eq( @$results[0]['address'], $addrs[0], 'yprv m/0. p2wpkh/p2sh' );
        $this->eq( @$results[1]['address'], $addrs[1], 'yprv m/1. p2wpkh/p2sh' );
        
        // check xpub derivation results in correct addresses.
        $params['key'] = $pub;
        $results = $this->derive_params( $params );
        $this->eq( @$results[0]['address'], $addrs[0], 'ypub m/0. p2wpkh/p2sh' );
        $this->eq( @$results[1]['address'], $addrs[1], 'ypub m/1. p2wpkh/p2sh' );
    }
}
