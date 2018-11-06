<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class eth extends tests_common {
    
    public function runtests() {
        $this->test_eth_addr();
    }
    
    protected function test_eth_addr() {
        $xprv = 'xprvA13PCLdU2AGYkVhaKm5nBzDm6MALfWGMF3GzADnwucqqsZaqSoeQ5FYVNNJZzTZJsKA8dgS8X4CMmwmU5MEJZFBjfjLfttUZVbtfKZ6z1HU';
        $addr_correct = '0x0994230b7B3e29E27885643a3890807E9f20346D';
        $privkey_correct = '0xcbdbdaf6ee8a028858e14f47fbcf21d1a37d383bcae2390995815b21e3db4770';
        
        // check xprv derivation results in correct addresses.
        $params = ['key' => $xprv,
                   'coin' => 'ETH',
                   'numderive' => 1,
                   'cols' => 'address,privkey',
                   'format' => 'json',
                   ];
        $r =  $this->derive_params( $params );
        $this->eq( $r['address'], $addr_correct, 'address' );
        $this->eq( $r['privkey'], $privkey_correct, 'privkey' );
    }
    
}
