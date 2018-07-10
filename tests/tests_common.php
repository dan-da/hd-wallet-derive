<?php

namespace tester;

require_once __DIR__  . '/../vendor/autoload.php';
\strictmode\initializer::init();

use tester;

abstract class tests_common extends tester\test_base {
    
    protected function derive_params($params) {
        return $this->exec_json($this->gen_args($params));
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
    
    protected function exec_json($args) {
        $output = $this->exec($args);
        return json_decode($output, true);
    }
    
    protected function exec($args) {
        
        $prog = realpath(__DIR__ . '/../hd-wallet-derive.php');
        $cmd = sprintf('%s %s', $prog, $args);

        exec($cmd, $output, $rc);
        if($rc != 0) {
            throw new \Exception("command failed with exit code " . $e->getCode() . "\n  command was:\n\n\n\n$cmd", $e->getCode());
        }
        return implode("\n", $output);
    }
}