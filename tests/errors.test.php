<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class errors extends tests_common {
    
    public function runtests() {
        // $this->test_unsupported_keytype();
        $this->test_required_args();
    }
    
    protected function test_unsupported_keytype() {
        $mnemonic = 'lake undo sustain coconut rail nation shrimp million cabbage moral during bomb moral crack fatal flower install blue mail hollow okay angry give turn either fold tube climb tool proof little pull report chunk wash riot oven essence tell parent velvet captain call airport agent relax write tackle';
        $expect_str = 'z extended keys are not supported for BTX';
        $expect_rc = 1;        
        
        // check xprv derivation results in correct addresses.
        $params = ['mnemonic' => $mnemonic,
                   'coin' => 'BTX',
                   'key-type' => 'z',
                   'format' => 'list',
        ];        
        $this->exec_params_expect_error( $params, $expect_rc, $expect_str, 'unsupported key-type' );
    }

    protected function test_required_args() {
        $expect_str = '--key or --mnemonic or --gen-key must be specified';
        $expect_rc = 1;        
        
        // check xprv derivation results in correct addresses.
        $params = ['format' => 'list'];        
        $this->exec_params_expect_error( $params, $expect_rc, $expect_str, 'missing --key or --nmenonic' );
    }
    
}
