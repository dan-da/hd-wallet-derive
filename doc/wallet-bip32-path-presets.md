The table below is a list of available Bip32 path presets that are in use by common Bitcoin wallets.

This table is generated with the command:
    $ ./hd-wallet-derive.php --help-presets --format=md
    
If you notice any error or omission, please open a github issue.

| id                      | path                 | wallet                  | version          | note                      |
|-------------------------|----------------------|-------------------------|------------------|---------------------------|
| bip44                   | m/44'/c'/a'/y/x      | Bip44 Compat            | n/a              | Bip44                     |
| bip49                   | m/49'/c'/a'/y/x      | Bip49 Compat            | n/a              | Bip49                     |
| bip84                   | m/84'/c'/a'/y/x      | Bip84 Compat            | n/a              | Bip84                     |
| bitcoincore             | m/a'/y'/x'           | Bitcoin Core            | v0.13 and above. | Bip32 fully hardened      |
| bither                  | m/44'/c'/a'/y/x      | Bither                  | n/a              | Bip44                     |
| breadwallet             | m/a'/y/x             | BreadWallet             | ?                | Bip32                     |
| coinomi                 | m/44'/c'/a'/y/x      | Coinomi (p2pkh)         | ?                | Bip44                     |
| coinomi_bech32          | m/84'/c'/a'/y/x      | Coinomi (bech32)        | ?                | Bip84                     |
| coinomi_p2sh            | m/49'/c'/a'/y/x      | Coinomi (p2sh)          | ?                | Bip49                     |
| copay                   | m/44'/c'/a'/y/x      | Copay                   | >= 1.2           | Bip44                     |
| copay_hardware_multisig | m/48'/c'/a'/y/x      | Copay                   | >= 1.5           | Hardware multisig wallets |
| copay_legacy            | m/45'/2147483647/y/x | Copay Legacy            | < 1.2            | Bip45 special cosign idx  |
| electrum                | m/44'/c'/a'/y/x      | Electrum                | 2.6+             | Bip44                     |
| electrum_legacy         | m/y/x                | Electrum (legacy)       | 2.x < 2.6        | Single account wallet     |
| electrum_legacy_multi   | m/a/y/x              | Electrum (legacy multi) | 2.x < 2.6        | Multi account wallet      |
| hive                    | m/a'/y/x             | Hive                    | ?                | Bip32                     |
| jaxx                    | m/44'/c'/a'/y/x      | Jaxx                    | ?                | Bip44                     |
| ledgerlive              | m/44'/c'/x'/y/0'     | Ledger Live             | All versions     | Non-standard Bip44        |
| multibit_hd             | m/a'/y/x             | Multibit HD             | ?                | Bip32                     |
| multibit_hd_44          | m/44'/c'/a'/y/x      | Multibit HD (Bip44)     | ?                | Bip44                     |
| mycelium                | m/44'/c'/a'/y/x      | Mycelium                | >= 2.0           | Bip44                     |
| samourai                | m/44'/c'/a'/y/x      | Samourai (p2pkh)        | ?                | Bip44                     |
| samourai_bech32         | m/84'/c'/a'/y/x      | Samourai (bech32)       | ?                | Bip84                     |
| samourai_p2sh           | m/49'/c'/a'/y/x      | Samourai (p2sh)         | ?                | Bip49                     |
| trezor                  | m/44'/c'/a'/y/x      | Trezor                  | All versions     | Bip44                     |
| wasabi                  | m/84'/c'/a'/y/x      | Wasabi                  | ?                | Bip84                     |



