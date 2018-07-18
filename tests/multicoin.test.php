<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class multicoin extends tests_common {
    
    public function runtests() {
        $this->test_coins_derive();
    }

    /**
     * This test verifies that derivation from xpub using m/0
     * matches previous derivation from root key using full
     * bip44 path.
     *
     * The previously derived addresses were spot checked against
     * the Bip39 tool at https://iancoleman.io/bip39/
     */
    protected function test_coins_derive() {
        
        $jsonpath = __DIR__ . '/util/multicoin-test-input.json';
        $coinsdata = $this->read_json_file($jsonpath);
        
        foreach($coinsdata as $row) {
            $params = [
                'key' => $row['xpub'],
                'numderive' => 1,
                'coin' => $row['coin'],
                'cols' => 'address',
                'format' => 'list'
            ];
            $address = trim($this->derive_params($params));
            $this->eq($address, $row['address_1'], "{$row['coin']} addr from xpub");
        }
    }
}
