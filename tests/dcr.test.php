<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class dcr extends tests_common {
    
    public function runtests() {
        $this->test_dcr_addr();
    }
    
    protected function test_dcr_addr() {
        $xprv = 'dprv3qtyv5nhonKvYQLPbRWVLwsjonMxCXYPTyX5f794s3kTNbJ3W6kFoje2bBoESxGG8jDPfdpYqPYgTtMZJtwr7vModVPXZLHNKk6qz5uNaSU';
        $addr_correct = 'DsbUQtn83VLdhvUCPFJwj2NrUB7aQ9zbCBc';
        
        // check xprv derivation results in correct addresses.
        $params = ['key' => $xprv,
                   'coin' => 'DCR',
                   'numderive' => 1,
                   'cols' => 'address',
                   'format' => 'list',
                   ];        
        $address = $this->derive_params( $params );
        $this->eq( $address, $addr_correct, 'dcr address' );
    }
    
}
