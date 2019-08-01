<?php

namespace App\Utils;

use \Exception;

/**
 * Bip32 Path Presets for various classes.
 * Resources:
 *   https://bitcoin.stackexchange.com/questions/78993/default-derivation-paths
 */

 
class PathPresets {
    
    static function getPreset($preset_id) {
        $list = static::getAllPresetID();
        if( !in_array($preset_id, $list)) {
            throw new Exception("Invalid preset identifier");
        }
        
        $class = 'App\Utils\PathPreset_' . $preset_id;
        $c = new $class();        
    return $c;
    }
    
    static function getAllPresetID() {
        
        static $id_list = null;
        
        if(!$id_list) {
            $id_list = [];
            $declared = get_declared_classes();
            foreach($declared as $d) {
                if(strpos($d, 'App\Utils\PathPreset_') === 0) {
                    $id = str_replace('App\Utils\PathPreset_', '', $d);
                    $id_list[] = $id;
                }
            }
        }
        
        return $id_list;
    }
    
    static function getAllPresets() {
        
        
        $all = self::getAllPresetID();
        $presets = [];
        
        foreach($all as $id) {
            $presets[] = self::getPreset($id);
        }
        return $presets;
    }
}

interface PathPreset {
    public function getID() : string;
    public function getPath() : string;
    public function getWalletSoftwareName() : string;
    public function getWalletSoftwareVersionInfo() : string;
}

class PathPreset_ledgerlive {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }
        
    public function getPath() : string {
        return "m/44'/c'/x'/v/0";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Ledger Live';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return 'All versions';
    }
    
    public function getNote() : string {
        return 'Non-standard Bip44';
    }    
}

class PathPreset_bitcoincore {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/a'/v'/x'";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Bitcoin Core';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return 'v0.13 and above.';
    }
    
    public function getNote() : string {
        return 'Bip32 fully hardened';
    }    
}

class PathPreset_trezor {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/44'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Trezor';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return 'All versions';
    }
    
    public function getNote() : string {
        return 'Bip44';
    }    
}

class PathPreset_bip32 {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Bip32 Compat';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return 'n/a';
    }
    
    public function getNote() : string {
        return 'Bip32';
    }    
}

class PathPreset_bip44 {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/44'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Bip44 Compat';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return 'n/a';
    }
    
    public function getNote() : string {
        return 'Bip44';
    }    
}

class PathPreset_bip49 {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/49'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Bip49 Compat';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return 'n/a';
    }
    
    public function getNote() : string {
        return 'Bip49';
    }    
}


class PathPreset_bip84 {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/84'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Bip84 Compat';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return 'n/a';
    }
    
    public function getNote() : string {
        return 'Bip84';
    }
}


class PathPreset_bither {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/44'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Bither';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return 'n/a';
    }
    
    public function getNote() : string {
        return 'Bip44';
    }    
}



// See https://github.com/bitpay/copay/wiki

class PathPreset_copay_legacy {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/45'/2147483647/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Copay Legacy';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '< 1.2';
    }
    
    public function getNote() : string {
        return 'Bip45 special cosign idx';
    }    
}

class PathPreset_copay {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/44'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Copay';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '>= 1.2';
    }
    
    public function getNote() : string {
        return 'Bip44';
    }
}

class PathPreset_copay_hardware_multisig {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/48'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Copay';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '>= 1.5';
    }
    
    public function getNote() : string {
        return 'Hardware multisig wallets';
    }
}


class PathPreset_mycelium {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/44'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Mycelium';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '>= 2.0';
    }
    
    public function getNote() : string {
        return 'Bip44';
    }
}

class PathPreset_jaxx {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/44'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Jaxx';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '?';
    }
    
    public function getNote() : string {
        return 'Bip44';
    }
}


class PathPreset_electrum {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Electrum';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '2.0+';
    }
    
    public function getNote() : string {
        return 'Single account wallet';
    }
}

class PathPreset_electrum_multi {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/a/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Electrum multi';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '2.0+';
    }
    
    public function getNote() : string {
        return 'Multi account wallet';
    }
}

class PathPreset_wasabi {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/84'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Wasabi';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '?';
    }
    
    public function getNote() : string {
        return 'Bip84';
    }
}

class PathPreset_samourai {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/44'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Samourai (p2pkh)';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '?';
    }
    
    public function getNote() : string {
        return 'Bip44';
    }
}

class PathPreset_samourai_p2sh {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/49'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Samourai (p2sh)';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '?';
    }
    
    public function getNote() : string {
        return 'Bip49';
    }
}


class PathPreset_samourai_bech32 {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/84'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Samourai (bech32)';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '?';
    }
    
    public function getNote() : string {
        return 'Bip84';
    }
}


class PathPreset_breadwallet {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'BreadWallet';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '?';
    }
    
    public function getNote() : string {
        return 'Bip32';
    }
}

class PathPreset_hive {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Hive';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '?';
    }
    
    public function getNote() : string {
        return 'Bip32';
    }
}

class PathPreset_multibit_hd {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Multibit HD';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '?';
    }
    
    public function getNote() : string {
        return 'Bip32';
    }
}

class PathPreset_multibit_hd_44 {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/44'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Multibit HD (Bip44)';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '?';
    }
    
    public function getNote() : string {
        return 'Bip44';
    }
}

 
class PathPreset_coinomi {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/44'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Coinomi (p2pkh)';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '?';
    }
    
    public function getNote() : string {
        return 'Bip44';
    }
}

class PathPreset_coinomi_p2sh {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/49'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Coinomi (p2sh)';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '?';
    }
    
    public function getNote() : string {
        return 'Bip49';
    }
}


class PathPreset_coinomi_bech32 {
    
    public function getID() : string {
        return str_replace('App\Utils\PathPreset_', '', get_class($this));
    }

    public function getPath() : string {
        return "m/84'/c'/a'/v/x";
    }
    
    public function getWalletSoftwareName() : string {
        return 'Coinomi (bech32)';
    }
    
    public function getWalletSoftwareVersionInfo() : string {
        return '?';
    }
    
    public function getNote() : string {
        return 'Bip84';
    }
}
 
 