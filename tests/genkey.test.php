<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

class genkey extends tests_common {
    
    const mnemonic = 'nothing edit remember box goddess local cabin term social destroy inner universe candy maze horse zone step direct captain patch cream output large ticket early cactus clap curious link quarter stamp guitar bone believe subject lawsuit funny infant creek width trigger talent kick payment habit example game shrimp';
    
    public function runtests() {
        $this->test_genkey_btc_mainnet();
    }
    
    protected function test_genkey_btc_mainnet() {
        
        // check xprv derivation results in correct addresses.
        $params = [
            'gen-key' => null,
            'format' => 'jsonpretty',
        ];
        
        $rows = $this->derive_params( $params );
        $this->count_eq( $rows, 3, 'num root keys');
        
        $row = @$rows[0];
        $this->eq( @$row['coin'], 'BTC', 'symbol' );
        $this->not_empty( @$row['seed'], 'seed' );
        $this->not_empty( @$row['mnemonic'], 'mnemonic' );
        $this->starts_with( @$row['root-key'], 'xprv', 'root-key' );
        $this->eq( @$row['path'], "m/44'/0'/0'/0", 'path' );
        $this->starts_with( @$row['xprv'], "xprv", 'xprv' );
        $this->starts_with( @$row['xpub'], "xpub", 'xpub' );

        $row = @$rows[1];
        $this->eq( @$row['coin'], 'BTC', 'symbol' );
        $this->not_empty( @$row['seed'], 'seed' );
        $this->not_empty( @$row['mnemonic'], 'mnemonic' );
        $this->starts_with( @$row['root-key'], 'yprv', 'root-key' );
        $this->eq( @$row['path'], "m/49'/0'/0'/0", 'path' );
        $this->starts_with( @$row['xprv'], "yprv", 'xprv' );
        $this->starts_with( @$row['xpub'], "ypub", 'xpub' );

        $row = @$rows[2];
        $this->eq( @$row['coin'], 'BTC', 'symbol' );
        $this->not_empty( @$row['seed'], 'seed' );
        $this->not_empty( @$row['mnemonic'], 'mnemonic' );
        $this->starts_with( @$row['root-key'], 'zprv', 'root-key' );
        $this->eq( @$row['path'], "m/84'/0'/0'/0", 'path' );
        $this->starts_with( @$row['xprv'], "zprv", 'xprv' );
        $this->starts_with( @$row['xpub'], "zpub", 'xpub' );
    }
}
