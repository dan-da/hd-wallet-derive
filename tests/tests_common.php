<?php

namespace tester;

require_once __DIR__  . '/../vendor/autoload.php';
\strictmode\initializer::init();

use tester;

abstract class tests_common extends tester\test_base {
    protected $last_cmd = null;
    
    protected function derive_params($params, $expect_rc=0, $label = '') {
        return !@$params['format'] || in_array($params['format'], ['json', 'jsonpretty']) ?
            $this->exec_json($this->gen_args($params), $expect_rc, $label) :
            $this->exec($this->gen_args($params), $expect_rc, $label);
    }
    
    protected function exec_params_expect_error($params, $expect_rc, $expect_str, $label='error message') {
        $output = $this->derive_params($params, $expect_rc, $label);
        if( $expect_str ) {
            $this->contains( $output, $expect_str, $label );
        }
    }
    
    protected function gen_args($params, $defaults=true) {
        $args = [];
        if($defaults) {
            if(!@$params['format']) {
                $params['format'] = 'jsonpretty';
            }
            $params['g'] = null;
        }
        foreach( $params as $k => $v ) {
            if($k == 'g') {
                $args[] = '-g';
            }
            else {
                $args[] = $v === null ? "--$k" : "--$k=" . escapeshellarg($v);
            }
        }
        $argbuf = implode(' ', $args);
        return $argbuf;
    }
    
    protected function exec_json($args, $expect_rc=0, $label) {
        $output = $this->exec($args, $expect_rc, $label);
        return json_decode($output, true) ?: [];
    }
    
    protected function exec($args, $expect_rc=0, $label) {
        
        $prog = realpath(__DIR__ . '/../hd-wallet-derive.php');
        $cmd = sprintf('%s %s 2>&1', $prog, $args);

        $this->last_cmd = $cmd;
        exec($cmd, $output, $rc);
        
        if( $rc != $expect_rc) {
            $this->eq($rc, $expect_rc, $label . ' : unexpected command exit code.');
        }
        
        // if($rc == 0 && $rc != $expect_rc) {
        //    throw new \Exception("command failed with exit code " . $rc . "\n  command was:\n\n\n\n$cmd", $rc);
        // }
        return trim(implode("\n", $output));
    }
    
    protected function read_json_file($path) {
        return json_decode(file_get_contents($path), true);
    }
    
    protected function add_failnotes() {
        $cmd_note = "--- command was: ---\n  {$this->last_cmd}\n---";
        return [$cmd_note];
    }    
    
    
}
