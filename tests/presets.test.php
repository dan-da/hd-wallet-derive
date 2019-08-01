<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class presets extends tests_common {
    
    public function runtests() {
        $this->test_valid();
        $this->test_mnemonic_valid();
        $this->test_errors();
    }

    protected function test_valid() {
        // check xprv derivation results in correct addresses.
        $params = [
            'format' => 'list',
            'cols'   => 'address',
            'numderive'   => '1',
            'key'    => 'xprv9zsV3UkGciQbtWB5f9f6ihPxTmeHnjDSynDmcZez14ficFJcbEsFSXJPsQPrtrR5c647W6V6zpxV9x8qCrB3Tv1CPSSqmsCfieUGpdfDP1f'
        ];
        $addr_correct = '14QYcbc8bS5ADCg3qpWPYCtrPpiVVsQib9';

        $map = [
                'bip32' => '1HKpen4TCPVueNkkkJVpHhNrYew6zmu9H9',
                'bip44' => '13A2wd2H4vsyjHXjvDQLZtxy4b3pStUwjo',
                'bip49' => '3LR3HZCi5GjWx88ixAr7D1eFVi3Cq7Vxip',
                'bip84' => 'bc1qrmp525wswv0djeqtdfpfu9uh7qhmt6lrrcpu0s',
                'bitcoincore' => '1P7CWn1DE5wFR69AVK58m8ZKEEeT3ai7bR',
                'bither' => '13A2wd2H4vsyjHXjvDQLZtxy4b3pStUwjo',
                'breadwallet' => '1HKpen4TCPVueNkkkJVpHhNrYew6zmu9H9',
                'coinomi' => '13A2wd2H4vsyjHXjvDQLZtxy4b3pStUwjo',
                'coinomi_bech32' => 'bc1qrmp525wswv0djeqtdfpfu9uh7qhmt6lrrcpu0s',
                'coinomi_p2sh' => '3LR3HZCi5GjWx88ixAr7D1eFVi3Cq7Vxip',
                'copay' => '13A2wd2H4vsyjHXjvDQLZtxy4b3pStUwjo',
                'copay_hardware_multisig' => '19RKD6My49ZGx7sMUKChZytfByytpLe3t2',
                'copay_legacy' => '1NU36AsgnstyVJ2D68xMGquUnyjQ6tmtae',
                'electrum' => '1Dy97ByZsGPDTkrcdv5MSRhWHyTjJw9iPp',
                'electrum_multi' => '1KXfyQubJA5db6RzsEwbK2TmwtKXKQn932',
                'hive' => '1HKpen4TCPVueNkkkJVpHhNrYew6zmu9H9',
                'jaxx' => '13A2wd2H4vsyjHXjvDQLZtxy4b3pStUwjo',
                'ledgerlive' => '13A2wd2H4vsyjHXjvDQLZtxy4b3pStUwjo',
                'multibit_hd' => '1HKpen4TCPVueNkkkJVpHhNrYew6zmu9H9',
                'multibit_hd_44' => '13A2wd2H4vsyjHXjvDQLZtxy4b3pStUwjo',
                'mycelium' => '13A2wd2H4vsyjHXjvDQLZtxy4b3pStUwjo',
                'samourai' => '13A2wd2H4vsyjHXjvDQLZtxy4b3pStUwjo',
                'samourai_bech32' => 'bc1qrmp525wswv0djeqtdfpfu9uh7qhmt6lrrcpu0s',
                'samourai_p2sh' => '3LR3HZCi5GjWx88ixAr7D1eFVi3Cq7Vxip',
                'trezor' => '13A2wd2H4vsyjHXjvDQLZtxy4b3pStUwjo',
                'wasabi' => '13ofCQabZvcPVFWV2MDaFbcjv9ziRzy5XH',
                ];
        
        // preset and path not set
        $address = $this->derive_params( $params );
        $this->eq($address, $addr_correct, 'path and preset not set');

        foreach($map as $id => $addr_correct) {
            $params['preset'] = $id;
            $params['addr-type'] = 'auto';
            
            // we just guess addr-type based on id.  works for some.  not critical.
            if( strstr($id, 'bech32') || strstr($id, '84')) {
                $params['addr-type'] = 'bech32';
            }
            else if( strstr($id, 'p2sh') || strstr($id, '49')) {
                $params['addr-type'] = 'p2sh-segwit';
            }
            
            $address = $this->derive_params( $params );
//	    echo "                '$id' => '$address',\n";
            $this->eq($address, $addr_correct, 'preset: ' . $id);
        }
    }

    protected function test_mnemonic_valid() {
        // check xprv derivation results in correct addresses.
        $params = [
            'format' => 'list',
            'cols'   => 'address',
            'numderive'   => '1',
            'mnemonic' => 'nothing edit remember box goddess local cabin term social destroy inner universe candy maze horse zone step direct captain patch cream output large ticket early cactus clap curious link quarter stamp guitar bone believe subject lawsuit funny infant creek width trigger talent kick payment habit example game shrimp',
        ];
        $addr_correct = '14QYcbc8bS5ADCg3qpWPYCtrPpiVVsQib9';

        $map = [
                'bip32' => '1D1XQGzskxM6GseAAV245VZrPp5gJSoQkd',
                'bip44' => '1JxwtAMZYMiT2pR6DCRDBpqsoZ4FdTxoSS',
                ];
        
        foreach($map as $id => $addr_correct) {
            $params['preset'] = $id;
            $params['addr-type'] = 'auto';
            
            $address = $this->derive_params( $params );
//	    echo "                '$id' => '$address',\n";
            $this->eq($address, $addr_correct, 'preset applied to mnemonic: ' . $id);
        }
    }

    
    protected function test_errors() {
        $expect_rc = 1;        
        
        // check xprv derivation results in correct addresses.
        $params = ['format' => 'list', 'mnemonic' => 'lake undo sustain'];

        $params['preset'] = 'foobarbaz';
        $expect_str = "Invalid preset identifier";
        $this->exec_params_expect_error( $params, $expect_rc, $expect_str, 'invalid preset ID' );        
    }
}
