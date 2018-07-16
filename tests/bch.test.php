<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class bch extends tests_common {
    
    public function runtests() {
        $this->test_cashaddr_default();
        $this->test_cashaddr();
        $this->test_legacyaddr();
    }
    
    protected function test_cashaddr_default() {
        $xprv = 'xprvA1kqyxBmtVGYC3q9cc55wY1A3WXgbbbpsuZwqsYYz32a2mZg4F95QhnJxnxp3i8yQ7HaxQpP9V4pBpCjoQtSU52Zzf8eBgY3s3zHxAo1JBV';
        $addr_correct = 'bitcoincash:qr5ztwnsvu58tnnfq7uevqhezd9y6upn0qqq95e53m';
        
        // check xprv derivation results in correct addresses.
        $params = ['key' => $xprv,
                   'coin' => 'BCH',
                   'numderive' => 1,
                   'cols' => 'address',
                   'format' => 'list',
                   ];        
        $address = $this->derive_params( $params );
        $this->eq( $address, $addr_correct, 'bch cashaddr' );
    }

    protected function test_cashaddr() {
        $xprv = 'xprvA1kqyxBmtVGYC3q9cc55wY1A3WXgbbbpsuZwqsYYz32a2mZg4F95QhnJxnxp3i8yQ7HaxQpP9V4pBpCjoQtSU52Zzf8eBgY3s3zHxAo1JBV';
        $addr_correct = 'bitcoincash:qr5ztwnsvu58tnnfq7uevqhezd9y6upn0qqq95e53m';
        
        // check xprv derivation results in correct addresses.
        $params = ['key' => $xprv,
                   'coin' => 'BCH',
                   'numderive' => 1,
                   'cols' => 'address',
                   'format' => 'list',
                   'bch-format' => 'cash',
                   ];        
        $address = $this->derive_params( $params );
        $this->eq( $address, $addr_correct, 'bch cashaddr' );
    }
    
    protected function test_legacyaddr() {
        $xprv = 'xprvA1kqyxBmtVGYC3q9cc55wY1A3WXgbbbpsuZwqsYYz32a2mZg4F95QhnJxnxp3i8yQ7HaxQpP9V4pBpCjoQtSU52Zzf8eBgY3s3zHxAo1JBV';
        $addr_correct = '1NAUxurm5kAJzn9mWfuPJiScFhpRw1kNaK';
        
        // check xprv derivation results in correct addresses.
        $params = ['key' => $xprv,
                   'coin' => 'BCH',
                   'numderive' => 1,
                   'cols' => 'address',
                   'format' => 'list',
                   'bch-format' => 'legacy',
                   ];        
        $address = $this->derive_params( $params );
        $this->eq( $address, $addr_correct, 'bch legacy addr' );
    }
    
}
