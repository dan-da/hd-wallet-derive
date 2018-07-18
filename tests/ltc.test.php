<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class ltc extends tests_common {
    
    public function runtests() {
        $this->test_ltc_addr();
        $this->test_ltc_ltub_addr();
        $this->test_ltc_ltub_mnemonic();
    }
    
    protected function test_ltc_addr() {
        $xpub = 'xpub6EKjmEgMQhVgM3br8Wxm33pHJ5hu44PVTXqzGeXRkZ7yHkwxhgZPUQWpDmyTonfTeUbA2KjTH1xYZEJJLe5h4NDQBDpS8rryj9ziSJ3XpNe';
        $addr_correct = 'Le54gW7VS5VZCpgJEGYiiZCqLAbr47WZ45';
        
        // check xprv derivation results in correct addresses.
        $params = ['key' => $xpub,
                   'coin' => 'LTC',
                   'numderive' => 1,
                   'cols' => 'address',
                   'format' => 'list',
                   ];        
        $address = $this->derive_params( $params );
        $this->eq( $address, $addr_correct, 'LTC-main x address from xpub' );
    }

    protected function test_ltc_ltub_addr() {
        $xprv = 'Ltpv7AYGzQxw3mXiPBReMzFGR5dxTypcvdgmEk13EVQt7AWMgY8sJyDkbrrZXBHhS4cLq1gYXcSWRyuKVmy7pCFXbtjsFFZNDgHJftBFsreBGfQ';
        $xpub = 'Ltub2bicbCPUHv4g148UcbjeVfUAuYWoztFRKG74uUBB3MHxGuKwdtGBcTxpma4yfCuSd9TK2fo3YcNqcyYsGRSvwsaN9YT63Aor93gugSNQcKZ';
        $addr_correct = 'LT9LdW5dvMSTsc3GcfSS4owxRebUtteiaq';
        
        // check xprv derivation results in correct addresses.
        $params = ['key' => $xprv,
                   'coin' => 'LTC',
                   'numderive' => 1,
                   'alt-extended' => 'Ltub',
                   'cols' => 'address',
                   'format' => 'list',
                   ];        
        $address = $this->derive_params( $params );
        $this->eq( $address, $addr_correct, 'LTC-main Ltub x address from Ltpv' );

        $params['key'] = $xpub;
        $address = $this->derive_params( $params );
        $this->eq( $address, $addr_correct, 'LTC-main Ltub x address from Ltub' );
    }

    protected function test_ltc_ltub_mnemonic() {
        $mnemonic = 'wasp method usage tuition must repeat bone owner prevent beach check birth shuffle author mean travel finger express govern basket until one parrot pumpkin flight grocery camp silly fly genius grief goddess slide motor stadium select slim crazy slam position claw fresh lecture glare exhaust music shine spend';
        $addr_correct = 'LT9LdW5dvMSTsc3GcfSS4owxRebUtteiaq';
        
        // check xprv derivation results in correct addresses.
        $params = ['mnemonic' => $mnemonic,
                   'coin' => 'LTC',
                   'numderive' => 1,
                   'alt-extended' => 'Ltub',
                   'cols' => 'address',
                   'format' => 'list',
                   ];        
        $address = $this->derive_params( $params );
        $this->eq( $address, $addr_correct, 'LTC-main Ltub x address from nmemonic' );
        
        $params['key-type'] = 'y';
        $address = $this->derive_params( $params );
        // convert between '3' and 'M' LTC addresses
        // at https://litecoin-project.github.io/p2sh-convert/
        $addr_correct = '33D1fRZSZvjJMpko6cpAuJzgEjHK7yr3dX';  // when using scripthash = 5. (old style p2sh)
        $addr_correct = 'M9R9yJyQX3ajAL2hCVoWixF5ZRsm49toRD';  // when using scripthash = 50 (new style p2sh).   and matches iancoleman's tool.
        $this->eq( $address, $addr_correct, 'LTC-main Ltub y address from nmemonic' );
        
        $params['coin'] = 'LTC-test';
        $params['key-type'] = 'x';
        unset($params['key-type']);
        $addr_correct = 'n24BJTmgneQUJxWEHpwpQCYRdirecJpoFN';
        $address = $this->derive_params( $params );
        $this->eq( $address, $addr_correct, 'LTC-test Ltub x address from nmemonic' );

        $params['key-type'] = 'y';
        unset($params['alt-extended']);
        $addr_correct = 'QTq3u8h3bDf6UCaULedr31brUbNogGFjPu';
        $address = $this->derive_params( $params );
        $this->eq( $address, $addr_correct, 'LTC-test y address from nmemonic' );
    }    
}
