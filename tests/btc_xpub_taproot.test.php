<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class btc_xpub_taproot extends tests_common {
    
    public function runtests() {
        $this->test_derive();
    }
    
    protected function test_derive() {

        $xprv = 'xprv9zVbXL4u79gJWBvR1SRfhJFk1ZcRvTBHXYoYZWhEFRrMpxU1Le3A5hqhrNReAXZQB42NTLXs9H4QJ25BvGZcZDxjavgiYKmsJgDS7Dt27hq';
        $xpub = 'xpub6DUwvqbnwXEbifzt7Txg4SCUZbSvKuu8tmj9Mu6qomPLhko9tBMQdWABhezKpaLZRT2RcXThgCgMpqmBMBJ4FBLsxTL4MA4gqX41nbQ3roF';
        $addrs = [
            'bc1ptwzcu4e9rk4zah2gmg6dsugyq9ma056lw2juwmr66kf2w6vgue3qepayyl',
            'bc1papd4kckcewh3jxh6lcm8rdtjf2ldqd3epg80ynx8yqyq66ays46shdjake',
        ];
        
        // check xprv derivation results in correct addresses.
        $params = ['key' => $xprv, 'addr-type' => 'p2tr', 'path' => 'm/0'];        
        $results = $this->derive_params( $params );
        $this->eq( @$results[0]['address'], $addrs[0], 'xprv m/0 p2tr' );
        $this->eq( @$results[1]['address'], $addrs[1], 'xprv m/1 p2tr' );
        
        // check xpub derivation results in correct addresses.
        $params['key'] = $xpub;
        $results = $this->derive_params( $params );
        $this->eq( @$results[0]['address'], $addrs[0], 'xpub m/0 p2tr' );
        $this->eq( @$results[1]['address'], $addrs[1], 'xpub m/1 p2tr' );
    }
}
