<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class path extends tests_common {
    
    public function runtests() {
        $this->test_path_valid();
        $this->test_path_errors();
    }

    protected function test_path_valid() {
        // check xprv derivation results in correct addresses.
        $params = [
            'format' => 'list',
            'cols'   => 'address',
            'numderive'   => '1',
            'key'    => 'xprv9zsV3UkGciQbtWB5f9f6ihPxTmeHnjDSynDmcZez14ficFJcbEsFSXJPsQPrtrR5c647W6V6zpxV9x8qCrB3Tv1CPSSqmsCfieUGpdfDP1f'
        ];
        $addr_correct = '14QYcbc8bS5ADCg3qpWPYCtrPpiVVsQib9';

        // path not set.
        $address = $this->derive_params( $params );
        $this->eq($address, $addr_correct, 'path not set');

        $params['path'] = 'm';
        $address = $this->derive_params( $params );
        $this->eq($address, $addr_correct, 'path --> m');
        
        $addr_correct = '1Dy97ByZsGPDTkrcdv5MSRhWHyTjJw9iPp';
        $params['path'] = '0';
        $address = $this->derive_params( $params );
        $this->eq($address, $addr_correct, 'path --> 0');

        $params['path'] = 'm/0';
        $address = $this->derive_params( $params );
        $this->eq($address, $addr_correct, 'path --> m/0');

        $addr_correct = '1KXfyQubJA5db6RzsEwbK2TmwtKXKQn932';
        $params['path'] = '0/0';
        $address = $this->derive_params( $params );
        $this->eq($address, $addr_correct, 'path --> 0/0');

        $params['path'] = 'm/0/0';
        $address = $this->derive_params( $params );
        $this->eq($address, $addr_correct, 'path --> m/0/0');
        
        $addr_correct = '1FFWo72KpeRTyaUk3k29VzsZsAza7cSwEG';
        $params['path'] = 'm/1';
        $address = $this->derive_params( $params );
        $this->eq($address, $addr_correct, 'path --> m/1');
        
        $params['path'] = '1';
        $address = $this->derive_params( $params );
        $this->eq($address, $addr_correct, 'path --> 1');

        $addr_correct = '1JdoaWUPBxzkxk2rAbVee9Le121saTw6uJ';
        $params['path'] = "m/1'";
        $address = $this->derive_params( $params );
        $this->eq($address, $addr_correct, "path --> m/1'");
        
        $params['path'] = "1'";
        $address = $this->derive_params( $params );
        $this->eq($address, $addr_correct, "path --> 1'");

        $params['path'] = "m/1'/x";
        $address = $this->derive_params( $params );
        $this->eq($address, $addr_correct, "path --> m/1'/x");
        
        $addr_correct = '17CJsKVHfw7KW97G9p6pmypjvREkad41VU';
        $params['path'] = "m/0'/x'/0";
        $address = $this->derive_params( $params );
        $this->eq($address, $addr_correct, "path --> m/0'/x'/0");
        
        
        $addr_correct = '1P7CWn1DE5wFR69AVK58m8ZKEEeT3ai7bR';
        $params['path'] = "m/0'/0'/x'";
        $address = $this->derive_params( $params );
        $this->eq($address, $addr_correct, "path --> m/0'/0'/x'");        
        
    }
    
    protected function test_path_errors() {
        $expect_rc = 1;        
        
        // check xprv derivation results in correct addresses.
        $params = ['format' => 'list', 'mnemonic' => 'lake undo sustain'];

        $params['path'] = 'm/q/0';
        $expect_str = "path parameter is invalid.  It should begin with m or an integer and contain only [0-9'/xcva]";
        $this->exec_params_expect_error( $params, $expect_rc, $expect_str, 'path with invalid char' );
        
        $params['path'] = 'g';
        $expect_str = "path parameter is invalid.  It should begin with m or an integer number.";
        $this->exec_params_expect_error( $params, $expect_rc, $expect_str, 'path with invalid start char' );

        $params['path'] = '0/0//1';
        $expect_str = "path parameter is invalid.  It must not contain '//'";
        $this->exec_params_expect_error( $params, $expect_rc, $expect_str, 'path with //' );

        $params['path'] = '0/0///1';
        $expect_str = "path parameter is invalid.  It must not contain '//'";
        $this->exec_params_expect_error( $params, $expect_rc, $expect_str, 'path with ///' );
        
        $params['path'] = "0/'/0";
        $expect_str = "path parameter is invalid. single-quote must follow an integer";
        $this->exec_params_expect_error( $params, $expect_rc, $expect_str, "path with /'" );
        
        $params['path'] = "0/0''";
        $expect_str = "path parameter is invalid. It must not contain \"''\"";
        $this->exec_params_expect_error( $params, $expect_rc, $expect_str, "path with ''" );
        
        $params['path'] = "m/0'/0/xx";
        $expect_str = "path parameter is invalid. x may only be used once";
        $this->exec_params_expect_error( $params, $expect_rc, $expect_str, "path with xx" );
        
    }

    
}
