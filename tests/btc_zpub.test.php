<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class btc_zpub extends tests_common {
    
    public function runtests() {
        $this->test_p2wpkh();
    }
    
    protected function test_p2wpkh() {

        $prv = 'zprvAYvFEoGJuftXkeM7K7LnuvXgkeYdJv6qztN4Y3ge4B1bvPNLzawFytuxHpYBLUF4AX3veK9j3wBF1JvwouvotWhEb2Z5LCXqHEdN6rsDUBR';
        $pub = 'zpub6mubeJoCk3Spy8RaR8soH4URJgP7iNphN7HfLS6FcWYaoBhVY8FWXhES97GPWqLsaCBHsx1UJxxNzMpMRchMyV7Za7sAuzwrTZHq5EoZvGa';
        $addrs = [
            'bc1qptv38ur3d3kt6pzgephhtfzzm7v7hykzm9nm5p',
            'bc1qfgf7yz572379wyry050p9468fjk4p8df0d4ygq',
        ];
        
        // check xprv derivation results in correct addresses.
        $params = ['key' => $prv,];        
        $results = $this->derive_params( $params );
        $this->eq( @$results[0]['address'], $addrs[0], 'zprv m/0. p2wpkh' );
        $this->eq( @$results[1]['address'], $addrs[1], 'zprv m/1. p2wpkh' );
        
        // check xpub derivation results in correct addresses.
        $params['key'] = $pub;
        $results = $this->derive_params( $params );
        $this->eq( @$results[0]['address'], $addrs[0], 'zpub m/0. p2wpkh' );
        $this->eq( @$results[1]['address'], $addrs[1], 'zpub m/1. p2wpkh' );
    }
}
