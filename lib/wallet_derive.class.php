<?php

require_once __DIR__  . '/../vendor/autoload.php';

// For HD-Wallet Key Derivation
use \BitWasp\Bitcoin\Bitcoin;
use \BitWasp\Bitcoin\Address;
use \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory;
use \BitWasp\Buffertools\Buffer;
use \BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;

// For Bip39 Mnemonics
use \BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use BitWasp\Bitcoin\Mnemonic\MnemonicFactory;

// For ethereum addresses
use kornrunner\Keccak;
use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PublicKeyInterface;
use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\PhpEcc\Serializer\Key\PublicKeySerializer;
use BitWasp\Bitcoin\Crypto\EcAdapter\EcAdapterFactory;
use Mdanter\Ecc\Serializer\Point\UncompressedPointSerializer;
use Mdanter\Ecc\EccFactory;

// For generating html tables.
require_once __DIR__ . '/html_table.class.php';

// For logging.
require_once __DIR__ . '/mylogger.class.php';


/* A class that implements HD wallet key/address derivation
 */
class wallet_derive {

    // Contains options we care about.
    protected $params;
    
    public function __construct( $params ) {
        $this->params = $params;
    }

    /* Getter for params
     */
    private function get_params() {
        return $this->params;
    }

    private function getEthereumAddress(PublicKeyInterface $publicKey){
    	static $pubkey_serializer = null;
    	static $point_serializer = null;
    	if(!$pubkey_serializer){
    		$adapter = EcAdapterFactory::getPhpEcc(Bitcoin::getMath(), Bitcoin::getGenerator());
    		$pubkey_serializer = new PublicKeySerializer($adapter);
    		$point_serializer = new UncompressedPointSerializer(EccFactory::getAdapter());
    	}

    	$pubKey = $pubkey_serializer->parse($publicKey->getHex());
    	$point = $pubKey->getPoint();
    	$upk = $point_serializer->serialize($point);
    	$upk = hex2bin(substr($upk, 2));

        $keccak = Keccak::hash($upk, 256);
        $eth_address_lower = strtolower(substr($keccak, -40));

        $hash = Keccak::hash($eth_address_lower, 256);
        $eth_address = '';
        for($i = 0; $i < 40; $i++) {
        	// the nth letter should be uppercase if the nth digit of casemap is 1
        	$char = substr($eth_address_lower, $i, 1);

        	if(ctype_digit($char))
        		$eth_address .= $char;
        	else if('0' <= $hash[$i] && $hash[$i] <= '7')
        		$eth_address .= strtolower($char);
        	else 
        		$eth_address .= strtoupper($char);
        }

        return '0x'. $eth_address;
    }

    /* Derives child keys/addresses for a given key.
     */
    public function derive_keys($key) {

        $params = $this->get_params();
        $addrs = array();
        
        $math = Bitcoin::getMath();
        $network = Bitcoin::getNetwork();

        $master = HierarchicalKeyFactory::fromExtended($key, $network);
        
        
        $start = $params['startderive'];
        $end = $params['startderive'] + $params['numderive'];
        
        if( $params['includeroot'] ) {
			$publicKey = $master->getPublicKey();
			$address = new PayToPubKeyHashAddress($publicKey->getPubKeyHash());
			$address = $address->getAddress();

            $xprv = $master->isPrivate() ? $master->toExtendedKey($network) : null;
            $wif = $master->isPrivate() ? $master->getPrivateKey()->toWif($network) : null;
            $pubkey = $publicKey->getHex();
            $pubkeyhash = $publicKey->getPubKeyHash()->getHex();
            $xpub = $master->toExtendedPublicKey($network);
            $eth_address = $this->getEthereumAddress($publicKey);

            $addrs[] = array( 'xprv' => $xprv,
                              'privkey' => $wif,
                              'pubkey' => $pubkey,
                              'pubkeyhash' => $pubkey,
                              'xpub' => $xpub,
                              'address' => $address,
                              'eth_address' => $eth_address,
                              'index' => null,
                              'path' => 'm');
        }

        mylogger()->log( "Generating addresses", mylogger::info );
        $path_base = is_numeric( $params['path']{0} ) ?  'm/' . $params['path'] : $params['path'];
        for( $i = $start; $i < $end; $i ++ ) {
            if( $i && $i % 10 == 0 ) {
                mylogger()->log( "Generated $i keys", mylogger::specialinfo );
            }
            $path = $path_base . "/$i";
            $key = $master->derivePath($path);
            
            // fixme: hack for copay/multisig.  maybe should use a callback?
            if(method_exists($key, 'getPublicKey')) {
                // bip32 path
				$publicKey = $key->getPublicKey();
				$address = new PayToPubKeyHashAddress($publicKey->getPubKeyHash());
				$address = $address->getAddress();

                $xprv = $key->isPrivate() ? $key->toExtendedKey($network) : null;
                $priv_wif = $key->isPrivate() ? $key->getPrivateKey()->toWif($network) : null;
                $pubkey = $publicKey->getHex();
                $pubkeyhash = $publicKey->getPubKeyHash()->getHex();
                $xpub = $key->toExtendedPublicKey($network);

            	$eth_address = $this->getEthereumAddress($publicKey);
            }
            else {
                throw new Exception("multisig keys not supported");
            }
            $addrs[] = array( 'xprv' => $xprv,
                              'privkey' => $priv_wif,
                              'pubkey' => $pubkey,
                              'pubkeyhash' => $pubkeyhash,
                              'xpub' => $xpub,
                              'address' => $address,
                              'eth_address' => $eth_address,
                              'index' => $i,
                              'path' => $path);
        }

        return $addrs;
    }

    // converts a bip39 mnemonic string with optional password to an xprv key (string).
    static public function mnemonicToKey($mnemonic, $password=null) {
        $bip39 = MnemonicFactory::bip39();
        $seedGenerator = new Bip39SeedGenerator($bip39);

        // Derive a seed from mnemonic/password
        $seed = $seedGenerator->getSeed($mnemonic, $password);
        
        // not logging seed.  just in case somebody keeps logs in insecure location.
        // mylogger()->log( "Seed: " . $seed->getHex(), mylogger::info );
        // echo $seed->getHex() . "\n";
        
        $bip32 = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromEntropy($seed);
        return $bip32->toExtendedKey();
    }

    /* Returns all columns available for reports
     */
    static public function all_cols() {
        return ['path', 'address', 'xprv', 'xpub', 'privkey', 'pubkey', 'pubkeyhash', 'index', 'eth_address'];
    }

    /* Returns default reporting columns
     */
    static public function default_cols() {
        return ['path', 'address', 'privkey'];
    }
}

/* A class that generates wallet-discovery reports in various formats.
 */
class walletderivereport {

    /* prints out single report in one of several possible formats,
     * or multiple reports, one for each possible format.
     */
    static public function print_results( $params, $results ) {
        $format = $params['format'];
        $outfile = @$params['outfile'];
        
        $summary = [];  // placeholder
        
        // remove columns not in report and change column order.
        $report_cols = $params['cols'];
        foreach( $results as &$r ) {
            $tmp = $r;
            $r = [];
            foreach( $report_cols as $colname ) {
                $r[$colname] = $tmp[$colname];
            }
        }

        if( $outfile && $format == 'all' ) {
            $formats = array( 'txt', 'csv', 'json', 'jsonpretty', 'html', 'list' );
            
            foreach( $formats as $format ) {
                
                $outfile = sprintf( '%s/%s.%s',
                                    pathinfo($outfile, PATHINFO_DIRNAME),
                                    pathinfo($outfile, PATHINFO_FILENAME),
                                    $format );
                
                self::print_results_worker( $summary, $results, $outfile, $format );
            }
        }
        else {
            self::print_results_worker( $summary, $results, $outfile, $format );
        }
    }

    /* prints out single report in specified format, either to stdout or file.
     */
    static protected function print_results_worker( $summary, $results, $outfile, $format ) {

        $fname = $outfile ?: 'php://stdout';
        $fh = fopen( $fname, 'w' );

        switch( $format ) {
            case 'txt':        self::write_results_fixed_width( $fh, $results, $summary ); break;
            case 'list':       self::write_results_list( $fh, $results, $summary );    break;
            case 'csv':        self::write_results_csv( $fh, $results );         break;
            case 'json':       self::write_results_json( $fh, $results );        break;
            case 'html':       self::write_results_html( $fh, $results );        break;
            case 'jsonpretty': self::write_results_jsonpretty( $fh, $results );  break;
        }

        fclose( $fh );

        if( $outfile ) {
            mylogger()->log( "Report was written to $fname", mylogger::specialinfo );
        }
    }

    /* writes out results in json (raw) format
     */
    static public function write_results_json( $fh, $results ) {
        fwrite( $fh, json_encode( $results ) );
    }

    /* writes out results in jsonpretty format
     */
    static public function write_results_jsonpretty( $fh, $results ) {
        fwrite( $fh, json_encode( $results,  JSON_PRETTY_PRINT ) );
    }
    
    /* writes out results in csv format
     */
    static public function write_results_csv( $fh, $results ) {
        if( @$results[0] ) {
            fputcsv( $fh, array_keys( $results[0] ) );
        }
        
        foreach( $results as $row ) {
            fputcsv( $fh, $row );
        }
    }

    /* writes out results in html format
     */
    static public function write_results_html( $fh, $results ) {
        $html = '';
        $data = [];

        // make our own array to avoid modifying the original.
        foreach( $results as $row ) {
            $myrow = $row;
            if( isset( $myrow['addr'] ) ) {
                $addr_url = sprintf( 'http://blockchain.info/address/%s', $myrow['addr'] );
                $myrow['addr'] = sprintf( '<a href="%s">%s</a>', $addr_url, $myrow['addr'] );
            }
            $data[] = $myrow;
        }

        if( @$data[0] ) {
            $header = array_keys( $data[0] );
        }
        else {
           // bail.
           return $html;
        }
    
        $table = new html_table();
        $table->header_attrs = array();
        $table->table_attrs = array( 'class' => 'wallet-derive bordered' );
        $html .= $table->table_with_header( $data, $header );
            
        fwrite( $fh, $html );
    }
    
    /* writes out results as a plain text table.  similar to mysql console results.
     */
    static protected function write_results_fixed_width( $fh, $results, $summary ) {

        $buf = texttable::table( $results );
        fwrite( $fh, $buf );
        
        fwrite( $fh, "\n" );
    }
    
    /* writes out results as a plain text list of addresses. single column only.
     */
    static protected function write_results_list( $fh, $results, $summary ) {

        foreach( $results as $info ) {
            $firstcol = array_shift( $info );
            fprintf( $fh, "%s\n", $firstcol );
        }
        
        fwrite( $fh, "\n" );
    }
    
}
