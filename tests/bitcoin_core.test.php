<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class bitcoin_core extends tests_common {
    
    public function runtests() {
        $this->test_p2shsegwit_addrs();
//        $this->test_path_errors();
    }
    
    protected function parse_dumpfile() {
        $buf = file_get_contents(__DIR__ . '/util/dumpwallet.hd-wallet-addrs.txt');
        
        $data = [];
        
        preg_match('/# extended private masterkey: (xprv.*)/', $buf, $matches);
        $this->check(@$matches[1], 'extended private masterkey');
        $data['xprv-master'] = $matches[1];
        
        // example line: L4gRYHFCSw5RyWaEseCKy6NTXattF4jH27b5n4XNLCv8nKWmFXxK 2018-07-20T10:32:20Z reserve=1 # addr=3JaWnpGSSbPW2LRDA9vjNquJwqmaKr6rJ4 hdkeypath=m/0'/0'/23'

        preg_match_all("#addr=([^\s]*) hdkeypath=(m/?.*'?)#", $buf, $matches);
        $this->check(@$matches[1], 'p2sh-segwit addresses');
        $rows = [];
        assert( array_unique( $matches[2] ));   // paths should be unique.
        foreach($matches[1] as $idx => $addr) {
            $path = $matches[2][$idx];
            $rows[$path] = $addr;
        }
        ksort($rows, SORT_NATURAL);  // sort addrs by path.
        
        $data['addr-master'] = $rows['m'];
        unset($rows['m']);
        
        $data['addrs'] = $rows;
        return $data;
    }
    
    protected function check($val, $label) {
        if(!$val) {
            throw new \Exception("Error parsing dumpfile.  $label not found");
        }
    }

    protected function test_p2shsegwit_addrs() {
        $data = $this->parse_dumpfile();
        
        
        // check xprv derivation results in correct addresses.
        $params = [
            'format' => 'jsonpretty',
            'cols'   => 'address',
            'numderive'   => '10',
            'path'   => "m/0'/0'/x'",
            'addr-type' => 'p2sh-segwit',
            'key'    => $data['xprv-master']
        ];

        // path not set.
        $results = $this->derive_params( $params );
        foreach($results as $idx => $r) {
            $pathkey = "m/0'/0'/$idx'";
            $this->eq($r['address'], $data['addrs'][$pathkey], 'receive address');
        }
    }
    
    protected function test_path_errors() {
        $expect_rc = 1;        
        
        // check xprv derivation results in correct addresses.
        $params = ['format' => 'list', 'mnemonic' => 'lake undo sustain'];

        $params['path'] = 'm/a/0';
        $expect_str = "path parameter is invalid.  It should begin with m or an integer and contain only [0-9'/]";
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
    }

    
}
