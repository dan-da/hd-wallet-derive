**hd-wallet-derive is a command-line tool that derives bip32 addresses and private keys for Bitcoin and many altcoins. Derivation reports show privkey (wif encoded), xprv, xpub, and address.**

**Table of Contents**
=====================

- [hd-wallet-derive](#hd-wallet-derive)
- [Path Preset examples.](#path-preset-examples)
  * [Obtaining a list of preset paths.](#obtaining-a-list-of-preset-paths)
  * [Deriving addresses for bitcoin-core using preset path.](#deriving-addresses-for-bitcoin-core-using-preset-path)
  * [Deriving Change addresses for bitcoin-core using preset path.](#deriving-change-addresses-for-bitcoin-core-using-preset-path)
- [Custom Path examples.](#custom-path-examples)
  * [using a private (xprv) key, with default columns](#using-a-private--xprv--key--with-default-columns)
  * [Deriving change addresses and showing all columns.](#deriving-change-addresses-and-showing-all-columns)
  * [Derive addresses from bip39 mnemonic seed words.](#derive-addresses-from-bip39-mnemonic-seed-words)
    + [Without a password](#without-a-password)
    + [With a password](#with-a-password)
    + [Using ypub extended key for segwit p2sh addresses.](#using-ypub-extended-key-for-segwit-p2sh-addresses)
    + [Using zpub extended key for segwit bech32 addresses.](#using-zpub-extended-key-for-segwit-bech32-addresses)
  * [Derive addresses from xpub key](#derive-addresses-from-xpub-key)
  * [We can derive segwit keys](#we-can-derive-segwit-keys)
    + [ypub / p2sh](#ypub---p2sh)
    + [zpub / bech32](#zpub---bech32)
    + [Derive addresses for a bitcoin-core wallet.](#derive-addresses-for-a-bitcoin-core-wallet)
  * [We can easily change up the columns in whatever order we want.](#we-can-easily-change-up-the-columns-in-whatever-order-we-want)
  * [Let's find what altcoins are supported.](#let-s-find-what-altcoins-are-supported)
  * [We can view altcoin addresses.](#we-can-view-altcoin-addresses)
  * [We can easily generate a new random master key, seed and extended keys for any coin.](#we-can-easily-generate-a-new-random-master-key--seed-and-extended-keys-for-any-coin)
  * [Key generation includes segwit keys and their paths.](#key-generation-includes-segwit-keys-and-their-paths)
  * [We can get results in a variety of additional formats](#we-can-get-results-in-a-variety-of-additional-formats)
    + [simple list](#simple-list)
    + [json](#json)
    + [csv](#csv)
- [How address derivation works](#how-address-derivation-works)
- [Path Presets](#path-presets)
- [Path variables](#path-variables)
    + [Variables](#variables)
- [Segwit notes](#segwit-notes)
- [Litecoin notes](#litecoin-notes)
  * [xpub vs Ltub keys.](#xpub-vs-ltub-keys)
  * [Here we see Mtub key and new style 'M' p2sh address.](#here-we-see-mtub-key-and-new-style--m--p2sh-address)
  * [And a ttub testnet key](#and-a-ttub-testnet-key)
- [Privacy and Security implications](#privacy-and-security-implications)
- [Use at your own risk.](#use-at-your-own-risk)
- [Output formats](#output-formats)
- [Usage](#usage)
- [Installation and Running.](#installation-and-running)
  * [install secp256kp1 php extension for big speedup](#install-secp256kp1-php-extension-for-big-speedup)
- [Thanks](#thanks)
- [Todos](#todos)


# hd-wallet-derive
A command-line tool that derives bip32 addresses and private keys for Bitcoin and many altcoins.

As of version 0.3.2, over 300 altcoins are available, 97 with bip44 path information.
Bitcoin Cash "CashAddr" and Ethereum address types are supported.

As of version 0.4.0, segwit keys and addresses are supported for Bitcoin as
ypub keys with p2sh style addresses and zpub keys with bech32 addresses.

As of version 0.4.1, Bitcoin-core style key derivation is supported.
[See here](./doc/bitcoin-core-hd.md).

As of version 0.4.3, [Preset paths](#path-presets) are available for common wallet software.

Derivation reports show privkey (wif encoded), xprv, xpub, and address.

Input can be a xprv key, xpub key, or bip39 mnemonic string (eg 12 words) with
optional password.

This tool can be used in place of your wallet software if it is misbehaving or
if you just want to see more information about your wallet addresses, including
private keys and addresses you haven't even used yet.

Reports are available in json, plaintext, and html. Columns can be changed or
re-ordered via command-line.

See also: [hd-wallet-addrs](https://github.com/dan-da/hd-wallet-addrs) -- a tool for finding hd-wallet addresses that have received funds.

# Path Preset examples.

## Obtaining a list of preset paths.

Let's say we want to derive addresses for bitcoin-core software.  First, we need to find out the preset
identifier for this software.

```
$ ./hd-wallet-derive.php --help-presets | head -n 7
+-------------------------+----------------------+-------------------------+------------------+---------------------------+
| id                      | path                 | wallet                  | version          | note                      |
+-------------------------+----------------------+-------------------------+------------------+---------------------------+
| bip44                   | m/44'/c'/a'/v/x      | Bip44 Compat            | n/a              | Bip44                     |
| bip49                   | m/49'/c'/a'/v/x      | Bip49 Compat            | n/a              | Bip49                     |
| bip84                   | m/84'/c'/a'/v/x      | Bip84 Compat            | n/a              | Bip84                     |
| bitcoincore             | m/a'/v'/x'           | Bitcoin Core            | v0.13 and above. | Bip32 fully hardened      |
```

See [Path Presets](#path-presets).

If we want only the ids, we could use the command:

```
$ ./hd-wallet-derive.php --help-presets --format=list
bip44
bip49
bip84
bitcoincore
bither
breadwallet
coinomi
coinomi_bech32
coinomi_p2sh
copay
copay_hardware_multisig
copay_legacy
electrum
electrum_multi
hive
jaxx
ledgerlive
multibit_hd
multibit_hd_44
mycelium
samourai
samourai_bech32
samourai_p2sh
trezor
wasabi
```

## Deriving addresses for bitcoin-core using preset path.

Using a preset means that we do not need to know the bip32 path.  We can do:

```
$ ./hd-wallet-derive.php -g --key=xprv9tyUQV64JT5qs3RSTJkXCWKMyUgoQp7F3hA1xzG6ZGu6u6Q9VMNjGr67Lctvy5P8oyaYAL9CAWrUE9i6GoNMKUga5biW6Hx4tws2six3b9c --numderive=3 --preset=bitcoincore --cols=path,address

+------------+------------------------------------+
| path       | address                            |
+------------+------------------------------------+
| m/0'/0'/0' | 1JsH5tzm2bphJySSLJ13AbFGP8KqJBYvG7 |
| m/0'/0'/1' | 19in8KwQy2waqzogwnVRvh2gt7EkHDGtwg |
| m/0'/0'/2' | 1CMc7jzi6ewKRzBNSCMkYzY3PU13ck6bxQ |
+------------+------------------------------------+
```

## Deriving Change addresses for bitcoin-core using preset path.

We can use the --path-change flag for this.  requires a preset
with variable 'v' present in the path.

```
$ ./hd-wallet-derive.php -g --key=xprv9tyUQV64JT5qs3RSTJkXCWKMyUgoQp7F3hA1xzG6ZGu6u6Q9VMNjGr67Lctvy5P8oyaYAL9CAWrUE9i6GoNMKUga5biW6Hx4tws2six3b9c --numderive=3 --preset=bitcoincore --cols=path,address --path-change

+------------+------------------------------------+
| path       | address                            |
+------------+------------------------------------+
| m/0'/1'/0' | 1B6q1KTyaa9yLHV2HTZC1rZaSKMG8KNqsp |
| m/0'/1'/1' | 15RF1R9ZaSqgtaTVBDm1ySU5MQ6dZeTpZf |
| m/0'/1'/2' | 1DpzhgrgWuRSnQjvLiZHMG2TAjs86znvjj |
+------------+------------------------------------+
```

Notice that that 2nd field has changed from 0' to 1'.


# Custom Path examples.

## using a private (xprv) key, with default columns

Here we do not specify a bip32 path or a preset, so addresses will be derived directly from
this key.

```
$ ./hd-wallet-derive.php -g --key=xprv9tyUQV64JT5qs3RSTJkXCWKMyUgoQp7F3hA1xzG6ZGu6u6Q9VMNjGr67Lctvy5P8oyaYAL9CAWrUE9i6GoNMKUga5biW6Hx4tws2six3b9c --numderive=3

+------+------------------------------------+------------------------------------------------------+
| path | address                            | privkey                                              |
+------+------------------------------------+------------------------------------------------------+
| m/0  | 1A9X6wjnER55GMpCCsTjn973y56u3zviDe | L4mgN3QEzR9PT6zPeKXGk3xNiGxxkHu4575W9YwBSRV2sRiyjV4g |
| m/1  | 192oE3o29AAoBPQiTYe65kRU2zoBLpEnUm | L1xYoR7VS6vuvkmfRM4ubaH84vRkWKUS2PmYj9DPTS76NQ4NtZP9 |
| m/2  | 1BbtRW5sua3Ewhm8jHAURUiz9t4bw8TQo9 | L21sVq8wutBSQWTzVJVBTeCdkfTgw41dK3PPViZ3Ds2xnMq8RMbK |
+------+------------------------------------+------------------------------------------------------+
```

## Deriving change addresses and showing all columns.

Typically, wallets provide xprv and xpub keys at the wallet root level (eg
m/0/0/0) and then receive addresses and change addresses are available at the
relative paths /0 and /1 respectively.

If this is the case with your wallet, then a command like the following should
work to derive change addresses. Note the --path=1 arg. Change it to --path=0 to
derive receive addresses. In the results below, the addresses are different than
above. This is because the path is different.

Notice also that the first xprv matches the --key argument and has "/" for the
relpath. This row is included because of the --includeroot flag. In this way, we
can easily obtain the xpub key for our xprv key.

```
$ ./hd-wallet-derive.php -g --key=xprv9tyUQV64JT5qs3RSTJkXCWKMyUgoQp7F3hA1xzG6ZGu6u6Q9VMNjGr67Lctvy5P8oyaYAL9CAWrUE9i6GoNMKUga5biW6Hx4tws2six3b9c --path=m/1 --cols=all --includeroot --numderive=3

+-------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+--------------------------------------------------------------------+-------+
| path  | address                            | xprv                                                                                                            | xpub                                                                                                            | privkey                                              | pubkey                                                             | pubkeyhash                                                         | index |
+-------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+--------------------------------------------------------------------+-------+
| m     | 1CHCnCjgMNb6digimckNQ6TBVcTWBAmPHK | xprv9tyUQV64JT5qs3RSTJkXCWKMyUgoQp7F3hA1xzG6ZGu6u6Q9VMNjGr67Lctvy5P8oyaYAL9CAWrUE9i6GoNMKUga5biW6Hx4tws2six3b9c | xpub67xpozcx8pe95XVuZLHXZeG6XWXHpGq6Qv5cmNfi7cS5mtjJ2tgypeQbBs2UAR6KECeeMVKZBPLrtJunSDMstweyLXhRgPxdp14sk9tJPW9 | L1pbvV86crAGoDzqmgY85xURkz3c435Z9nirMt52UbnGjYMzKBUN | 0212b55b9431515c7185355f15b48c5e1a1bbfa31af61429fa2bb8709de722f420 | 0212b55b9431515c7185355f15b48c5e1a1bbfa31af61429fa2bb8709de722f420 |       |
| m/1/0 | 1qhp7SiuVmxo3WKca7zHMkKjvjkGXs29d  | xprv9yJyMFmJjNAiXqf3yfT6NvWCkXo1PTdYw6x5sj6fH2ePRVL4N4wy8weFYJnMKxNEib9HFyS7pc69rE3tmw7FfWhshBc17wHcKsNHByt4SZC | xpub6CJKkmJCZjj1kKjX5gz6k4SwJZdVnvMQJKsgg7WGqNBNJHfCucGDgjxjPcHY9QZJCne3tubbtucYmpt7a7u1Xx3oNumZRVytpa2UdFjTr47 | KxcccHPiLz7sxUVShX9THgfH6i8D6m3iAkHtgGuB87Tm3maz8Jas | 036f45f7040f0f41068d7bcb45dbd28550be6739cfdbe16739e27cfa44e60083c3 | 093608d6a29e55873e2d1facacf6631961a4e8ed                           |     0 |
| m/1/1 | 1N3vKW2KGeKocao9eae6kVyYL8GNLzW8yd | xprv9yJyMFmJjNAiZ2NDkwj7Jcjo8AtdJTPYXF5bBLGUYh8NNid9bgAfuhNtSEco35mLrBzPPRTaA5XeezrtPxn59MsYBzjRZt6XqGW2T87JiS4 | xpub6CJKkmJCZjj1mWSgryG7fkgXgCj7hv7PtU1Byig672fMFWxJ9DUvTVhNHVA8ciGkqrwaqDW6Mvmr4ihT3EoqjgNK6s5P6C9X7UBLX5Trzw6 | KyxHznKrz1Y6jmE5fxnHh1e2AZLNCtDjnF86cZzk8F5S8nBxfqfc | 021107c4523fb021ad5c8e5ae086b8d162ebd755df80100e26c9d4cb5c7caaa3ba | e6e7fb588626c0568fbb898edf098725ac0b8736                           |     1 |
| m/1/2 | 17XRz7oPJLUNp5uf66BeqTmSDuRwqz3aW4 | xprv9yJyMFmJjNAibyUzvGgfpk8igy6DpCtjWQ6C5oVmXyUhqak5J2xj6K2y7o3qn22nAvt14cLjRQEXVpGRZoc3ULJHomWmkUTTPJCEaF4rZPr | xpub6CJKkmJCZjj1pTZU2JDgBt5TEzviDfcasd1ntBuP6K1giP5DqaGye7MSy5MYFWoGtVhvK8Ko2XW4JRMtKjMXctpPxcjRMjM47AqeWiZLKKN | L1AFnZ6JJyGRxLG9QoCTDzeLzLV617XtgLy8DrXCyLnPG6L1n7xo | 031d715e0943dd94df91771397d0cb75db46279da4bbd64596652ce2e1b36da74a | 479216fd27805445fc564410c1c5e83139b39005                           |     2 |
+-------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+--------------------------------------------------------------------+-------+
```

## Derive addresses from bip39 mnemonic seed words.

### Without a password
```
$ ./hd-wallet-derive.php --mnemonic="refuse brush romance together undo document tortoise life equal trash sun ask" -g --includeroot  --numderive=2 --cols=path,address,privkey,pubkey,xprv

+-----------------+------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+
| path            | address                            | privkey                                              | pubkey                                                             | xprv                                                                                                            |
+-----------------+------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+
| m               | 15UL8r2hkZpwi7RrQzWo6fYxfzdWL8mguG | L4jtNUY2YZfARajhSzq66RCBcfB8ZaXUYVHXD3q2q8SdMGaS7HpX | 03fa47f99f33e208cf46f42bdfc120843808a6558fa77a7806124f3b00be900b4a | xprv9s21ZrQH143K2vBDZaAMUvqS1MBuyNyisggeeGiUdMpi956vyGmkX81BapU6oD2c1qHnQYETcd85Z7i4GfmBz2TCz9PDQrxHjd3W5Ty5ayu |
| m/44'/0'/0'/0/0 | 14QEoz5SwQ5Kfjkhs5QitskgfD5QCqM7RB | L4X3nAcMs63daNUaWoVL6YBRh7GDsyT3mouEKLFhSeYe5PBwJbXe | 024977580921d65487194d2d84744b411cdbc19ec374bdb3fdfcf32e86e3e16830 | xprvA3rUF5RAJPotmYWqWmqEoWweV6CWVwYMA6vSGb6VCjhGFKaPuxHnveqBaBs19GPhp8unUjNBUj2TEFgbUUqsaPzF41w1SHcFUMC9jb8KCcJ |
| m/44'/0'/0'/0/1 | 1B83aj2N1Dxx37C4UdNM4vE5JxVKQV1Hur | L3Ks5PyY6enhjxsevfPYfsDmCtaK6nUymPmaN1Xvszh86urom9nw | 0357e9b058d9cbaece6459537579a6b7a9822f163844cd50f94ab76ad3781f2d4d | xprvA3rUF5RAJPotoshfn98F5SpcbC2vbR2CKtKkFfr8LJ7FwmukaCCN1fbcqqefb51odUrkFxnxxTyBkUM2hQ3ymusqjtEr3CErczzT1UkhERP |
+-----------------+------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+
```

note: The --path argument defaults to the bip44 extended key path when using
--mnemonic to make address generation easier.  If a Bip44 ID is not defined for
the coin then --path must be specified explicitly.

you can verify these results [with Ian Coleman's tool](https://iancoleman.io/bip39/).


### With a password

```
$ ./hd-wallet-derive.php --mnemonic="refuse brush romance together undo document tortoise life equal trash sun ask" --mnemonic-pw="mypass" --path="m/44'/0'/0'/0"  -g --includeroot  --numderive=2 --cols=path,address,privkey,pubkey,xprv

+-----------------+------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+
| path            | address                            | privkey                                              | pubkey                                                             | xprv                                                                                                            |
+-----------------+------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+
| m               | 1GmHqykYzYHz6G3FiC9t8JRjEGXmDqmnKA | L5D4PLY9ESVfF3pJnzp4vMz9DXfTstyWq29tdcFyGcziz1vDZZrU | 021d0ba761acb490283a75f523c38e572dd96a3b0ed9a34c110f40dab8aa82b766 | xprv9s21ZrQH143K4NLDtVeqgtfoxAPoRuPLipm1EzQRhP8Vp3sx5nYu8KTFutKfv3MRrGBJgcRxg9gcQwe2tBaLUov8tm18zaE2LxbwkW4j2SA |
| m/44'/0'/0'/0/0 | 1MXGCNMujDdT8AbXH7bxUEemiG8zQUk7ag | Kx3sazEL38AyeiNqz2vnW6btfCTcdn2Ww1ubxyX3yCEkiZvppp5d | 02dd4630b4ded7635093afc6bb498f498b9ce5f735d10a80ad0d0f67f23a005f79 | xprvA2XuGNexMcwzPEmVVXnwcTuax4sz4nehv6eSrqnAZ2AWjXFBYuvtge55L3ytZcdTp5S3ptwhaJbtEZ35wvib3S9cQPemQEHm6W8FNWLHH59 |
| m/44'/0'/0'/0/1 | 1EMfWdxRQYzg78PGcHq6pEDqZbQMDm31JN | L3y1MsuW6bbWsR6jkaDXcsHRocBMPHoMcnr7zz7xviHrEg1na4fU | 03597bafe11885102e45542060b8f7dec902d994fdb7b04c4b0f1f97e3db94ea64 | xprvA2XuGNexMcwzTwbLgXNrHEoYArDaV9zz17yDD1RkWsbn48g2MPJoRhoNXaZbxBqeQ64HGKkJ4hmzrgc4jFW6C9ku9L46hfbBPUguE2dRVZo |
+-----------------+------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+
```

### Using ypub extended key for segwit p2sh addresses.
```
$ ./hd-wallet-derive.php --key-type=y --mnemonic="refuse brush romance together undo document tortoise life equal trash sun ask" -g --includeroot  --numderive=2 --cols=path,address,xprv

+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+
| path            | address                            | xprv                                                                                                            |
+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+
| m               | 32AuKZR2VDE92CnXqGA9ZT8tMxQdeMmWLs | yprvABrGsX5C9jantDNLPvwyh1vwBKLMuzyDnoCsRfcN1NCbCAvADvwK9BfKc2Rgo7gXRUQbA1q25HUdSQKczNBCnG8orV5dzmmn1M79U4g6cJu |
| m/49'/0'/0'/0/0 | 3A9T2s8eeUbREb9DS3YFmK5wzajArDGvmY | yprvAN1znE9zGbrFzoVo8kSTYF7GHjDkmjkb2N8qeNA4Vecyt2t6AFroRzaLK6Fb13cidiNDAS2iL6jqrzkooz3XNQmKhBbdqiJeNhb155FS9nK |
| m/49'/0'/0'/0/1 | 3HWjvs6ZFQQgWLcvdnDFDAt284FWroMGSz | yprvAN1znE9zGbrG4HArpijJbexkCEWP8oFyCAGfudZXEmuzLsPxoF6EJ7QAxWt4N8Ro3ebcoYyR9zxQhQxPDVwkFHqmUmTWy9BQG7uQjCjw8NS |
+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+
```

### Using zpub extended key for segwit bech32 addresses.
```
$ ./hd-wallet-derive.php --key-type=z --mnemonic="refuse brush romance together undo document tortoise life equal trash sun ask" -g --includeroot  --numderive=2 --cols=path,address,xprv

+-----------------+--------------------------------------------+-----------------------------------------------------------------------------------------------------------------+
| path            | address                                    | xprv                                                                                                            |
+-----------------+--------------------------------------------+-----------------------------------------------------------------------------------------------------------------+
| m               | bc1qxy9m2x2nhrunduvjz0hx8sdv6av52fqzqtfw08 | zprvAWgYBBk7JR8GjWZTEHjbu72SMHUorcxihuj6D4WFPNaUFGjPUb6smFKTdEPGo2LSq7XPuVRaXwqBKgwBi4bDaVpQipn4agbGH5AnrfYE8kQ |
| m/84'/0'/0'/0/0 | bc1qy0p88n2d09ntyvjlkmum5p9rp2a99ws04cxjjv | zprvAgjsrdySZb8q4L1tQX9EmwRYa5Ldu3kPEK6YYPBt1Et64fovuAHA5izEPaFCCNZCkeUHnVcy9Z2B6cAauJa6mz9byU1KXEgfCo4qStWFF8D |
| m/84'/0'/0'/0/1 | bc1qvznkdsdg3nwhjy08j3x9hc8u5mf0mfwhzwgych | zprvAgjsrdySZb8q7QEmNzR81DRaTQfjcq96znUUKGhH93eRZmyWV1vPc7QiBzYTMMBVmnvvz5XQ73ofAQSrfDu3RCJiT5cAR3nLNRJNJTrrpdt |
+-----------------+--------------------------------------------+-----------------------------------------------------------------------------------------------------------------+
```

note: you can verify these results [with Ian Coleman's tool](https://iancoleman.io/bip39/).


## Derive addresses from xpub key

Addresses can also be derived for a public (xpub) key.  In this case, result fields pertaining to private keys will be empty.

```
$ ./hd-wallet-derive.php -g --key=xpub6BfKpqjTwvH21wJGWEfxLppb8sU7C6FJge2kWb9315oP4ZVqCXG29cdUtkyu7YQhHyfA5nt63nzcNZHYmqXYHDxYo8mm1Xq1dAC7YtodwUR --numderive=3

+------+------------------------------------+---------+
| path | address                            | privkey |
+------+------------------------------------+---------+
| m/0  | 1FZKdR3E7S1UPvqsuqStXAhZiovntFirge |         |
| m/1  | 12UMERLGAHKe5PQPaSYX8sczr52rSAg2Mi |         |
| m/2  | 1Pyk8NLx3gaXSng7XhKoNMLfBffUsJGAjr |         |
+------+------------------------------------+---------+
```

## We can derive segwit keys

### ypub / p2sh
```
$ ./hd-wallet-derive.php --key=yprvALsZfj564Q4XBd6aaAJ3sA1BVBhEw9Q8Xn7x9jFeMku6u2y6kRFdaDVFsdj9d8zuAdvwH5id6VJ5xKdwGJgMyx292kcJigqNdic2piwqAuL --numderive=3 --cols=path,address -g

+------+------------------------------------+
| path | address                            |
+------+------------------------------------+
| m/0  | 31qFbTk4btBfVVV3BrSopZQGfR5E5yqHQE |
| m/1  | 3Qys6SzWm27q5KCeky5SQoaWUxLqd3xNnW |
| m/2  | 3FKLumUJPQXtTScmpyQCEaHhePWrYcXQLj |
+------+------------------------------------+
```

### zpub / bech32
```
$ ./hd-wallet-derive.php --key=zprvAfvRyooajPigxomveEuGYUPwB7KLGseATsYbGB63S6wnFvFahfNnkBYmWCjhGmWbh7fJgNZSoUeAu7HcbRqcFtRPf5KXShRj7Sik7GadJrK --numderive=3 --cols=path,address -g

+------+--------------------------------------------+
| path | address                                    |
+------+--------------------------------------------+
| m/0  | bc1qgpkf27eva4rdqsd3cv60hywmc83k63g08xum60 |
| m/1  | bc1qjfz7nn90szmzzy38jym79p0hxr8s2c4dxhj4v0 |
| m/2  | bc1qv23447pd7ezf589d9elcuztz3pnarf0ndk2twv |
+------+--------------------------------------------+
```

### Derive addresses for a bitcoin-core wallet.

Here we make use of *--path="m/0'/0'/x'"* to specify hardened address generation
and *--addr-type=p2sh-segwit* to force generation of p2sh-segwit keys for an xprv key.

[See here](doc/bitcoin-core-hd.md) for more docs and examples on bitcoin-core derivation.

```
$ /home/websites/hd-wallet-derive/hd-wallet-derive.php --path="m/0'/0'/x'" --addr-type=p2sh-segwit --cols=path,address,pubkey --numderive=3 --key=xprv9s21ZrQH143K3KeCJ5DMac7XqmriV7xVDDCV5MNE564bKUF6piF7JK6RWHVJMzQMUBbzxLaV9kNaRMHyjVnjNiLAq2SyvJJBs7ZUg4c9kcy -g

+------------+------------------------------------+--------------------------------------------------------------------+
| path       | address                            | pubkey                                                             |
+------------+------------------------------------+--------------------------------------------------------------------+
| m/0'/0'/0' | 3LTKKaLjr83nSCEE5gUfLzRhavU3wAdMtu | 0236ac3d8df99023e259d24754fd022af696542e25ff237bc9c835d52468b538ae |
| m/0'/0'/1' | 34dCyjA9rEtjDEZ1AUViTGxYvmNAFA3gFH | 0365d31a6168e1187202ffb30bc80b4a788d68e87909024b624d4963ff2426b339 |
| m/0'/0'/2' | 3FzQckSqoQNnzdGgn5sLRUgaE8Vxt4g4eo | 02446804c9bd85f0f782a0f4e52baa7398005b0ee54dc4eed23aeef64363a7ea99 |
+------------+------------------------------------+--------------------------------------------------------------------+
```


## We can easily change up the columns in whatever order we want.

Just use the --cols parameter.

```
$ ./hd-wallet-derive.php -g --key=xpub6BfKpqjTwvH21wJGWEfxLppb8sU7C6FJge2kWb9315oP4ZVqCXG29cdUtkyu7YQhHyfA5nt63nzcNZHYmqXYHDxYo8mm1Xq1dAC7YtodwUR --numderive=3 --cols=xpub,address

+-----------------------------------------------------------------------------------------------------------------+------------------------------------+
| xpub                                                                                                            | address                            |
+-----------------------------------------------------------------------------------------------------------------+------------------------------------+
| xpub6Dv5JYyovBicwK57NbxM4zbV6PBr5vP6EDZTMHsULd8uFKSXAArqiChQKo26JWjrAxgsz78riSpt2QU1TEfkK6ZSdSYqgSwnTbbrs7J3qZ8 | 1FZKdR3E7S1UPvqsuqStXAhZiovntFirge |
| xpub6Dv5JYyovBiczGhM8vgsv5LXpjoHhBZeD8cMgjtkMbd4vsqkYudETnnKWmJHyFvcNWvgqj21pBxunfHaBGdWgEKqo9ogr1x6qaExZ5wTvPm | 12UMERLGAHKe5PQPaSYX8sczr52rSAg2Mi |
| xpub6Dv5JYyovBid1MKNu51wxzPaCqooCFmKgzHLihydzgibWkYqQg9QZmpMuX8jqZ4zSAdTBJQKh7ekmdXrT8LNthKD1ia6nC3nNNNmua5Rfg3 | 1Pyk8NLx3gaXSng7XhKoNMLfBffUsJGAjr |
+-----------------------------------------------------------------------------------------------------------------+------------------------------------+
```

## Let's find what altcoins are supported.

```
$ ./hd-wallet-derive.php --help-coins
+--------------------+------------------------------------+
| Symbol             | Coin / Network                     |
+--------------------+------------------------------------+
...
| ZEC                | Zcash - Mainnet                    |
| ZEC-test           | Zcash - Testnet                    |
| ZEC-regtest        | Zcash - Regtest                    |
...
+--------------------+------------------------------------+

```
(340+ altcoins omitted for brevity)

Note that testnet and regtest are supported for many coins.

## We can view altcoin addresses.

```
$ ./hd-wallet-derive.php --key=xprv9zbB6Xchu2zRkf6jSEnH9vuy7tpBuq2njDRr9efSGBXSYr1QtN8QHRur28QLQvKRqFThCxopdS1UD61a5q6jGyuJPGLDV9XfYHQto72DAE8 --cols=path,address --coin=ZEC --numderive=3 -g

+------+-------------------------------------+
| path | address                             |
+------+-------------------------------------+
| m/0  | t1V1Qp41kbHn159hvVXZL5M1MmVDRe6EdpA |
| m/1  | t1Tw6iqFY1g9dKeAqPDAncaUjha8cn9SZqX |
| m/2  | t1VGTPzBSSYd27GF8p9rGKGdFuWekKRhug4 |
+------+-------------------------------------+
```

## We can easily generate a new random master key, seed and extended keys for any coin.

```
$ ./hd-wallet-derive.php --coin=DOGE --gen-key --format=jsonpretty -g
[
    {
        "coin": "DOGE",
        "seed": "a3adc3e71ac05b3336422e6506d646e995f7bfcb960e6fca48dc13c93fae8ef3dc37a6013791ad1cfe7fe408de0e7676a9fe29b02413c79b988d54c74515d3db",
        "mnemonic": "arch hover pen regret priority sugar thunder glimpse west diagram path sword divide spread anger vendor century roof agree know treat drastic allow blind advance oil iron gold skate absorb stem shiver can pear twin helmet loan satisfy fragile admit comfort mercy pelican pupil debate tornado rifle desert",
        "master_priv_key": "dgpv51eADS3spNJh8eoSPqujdFPAhBZywAW6KQrR5TqM1Q5NMsrJmFP1hTXvfbUHLQFLmh4jVYZjXtJvKJVakn5YxT48mocEXu7yTNkCYN29cMV",
        "path": "m\/44'\/3'\/0'\/0",
        "ext_priv_key": "dgpv59SfnUBjPvKLfM453bkxJXHRfNvDQ3zAngt3fpKheqR846z9W1QYzoUz5ss4qtvLU7iBd93nw8ZXcXArpdLjuyudR2uUFH4KeV9Nes8eNeJ",
        "ext_pub_key": "dgub8tKh8A7cx4yfxCiE5qNvRNq27wHrEB1t5HfFpvigSxU8cA6qumxKe6tdf7TkUPFBoj6C8eBxofiydXy5hGf471zWZkYiy4tQ6vWqRwETdGA"
    }
]
```

## Key generation includes segwit keys and their paths.

```
$ ./hd-wallet-derive.php --gen-key --cols=path,xprv -g
+---------------+-----------------------------------------------------------------------------------------------------------------+
| path          | xprv                                                                                                            |
+---------------+-----------------------------------------------------------------------------------------------------------------+
| m/44'/0'/0'/0 | xprvA19NHbsi3bjKhC9DTPzg2mxfkgrPHyHtEJZUs3P17s8QeDiefRL7jGKkHapfqyxycCBW76bdYR2cezv1ECW4rNpmJ691XaGRVDbB8m4S1Ln |
| m/49'/0'/0'/0 | yprvALsZfj564Q4XBd6aaAJ3sA1BVBhEw9Q8Xn7x9jFeMku6u2y6kRFdaDVFsdj9d8zuAdvwH5id6VJ5xKdwGJgMyx292kcJigqNdic2piwqAuL |
| m/84'/0'/0'/0 | zprvAfvRyooajPigxomveEuGYUPwB7KLGseATsYbGB63S6wnFvFahfNnkBYmWCjhGmWbh7fJgNZSoUeAu7HcbRqcFtRPf5KXShRj7Sik7GadJrK |
+---------------+-----------------------------------------------------------------------------------------------------------------+
```

## We can get results in a variety of additional formats

### simple list

only the first column will be used.  This is handy for cut/paste operations.

```
 ./hd-wallet-derive.php -g --key=xpub6BfKpqjTwvH21wJGWEfxLppb8sU7C6FJge2kWb9315oP4ZVqCXG29cdUtkyu7YQhHyfA5nt63nzcNZHYmqXYHDxYo8mm1Xq1dAC7YtodwUR --numderive=3 --cols=address,xpub --format=list

1FZKdR3E7S1UPvqsuqStXAhZiovntFirge
12UMERLGAHKe5PQPaSYX8sczr52rSAg2Mi
1Pyk8NLx3gaXSng7XhKoNMLfBffUsJGAjr
```

### json

json can be pretty printed or compact form.

```
$ ./hd-wallet-derive.php -g --key=xpub6BfKpqjTwvH21wJGWEfxLppb8sU7C6FJge2kWb9315oP4ZVqCXG29cdUtkyu7YQhHyfA5nt63nzcNZHYmqXYHDxYo8mm1Xq1dAC7YtodwUR --numderive=3 --cols=address,xpub --format=jsonpretty
[
    {
        "address": "1FZKdR3E7S1UPvqsuqStXAhZiovntFirge",
        "xpub": "xpub6Dv5JYyovBicwK57NbxM4zbV6PBr5vP6EDZTMHsULd8uFKSXAArqiChQKo26JWjrAxgsz78riSpt2QU1TEfkK6ZSdSYqgSwnTbbrs7J3qZ8"
    },
    {
        "address": "12UMERLGAHKe5PQPaSYX8sczr52rSAg2Mi",
        "xpub": "xpub6Dv5JYyovBiczGhM8vgsv5LXpjoHhBZeD8cMgjtkMbd4vsqkYudETnnKWmJHyFvcNWvgqj21pBxunfHaBGdWgEKqo9ogr1x6qaExZ5wTvPm"
    },
    {
        "address": "1Pyk8NLx3gaXSng7XhKoNMLfBffUsJGAjr",
        "xpub": "xpub6Dv5JYyovBid1MKNu51wxzPaCqooCFmKgzHLihydzgibWkYqQg9QZmpMuX8jqZ4zSAdTBJQKh7ekmdXrT8LNthKD1ia6nC3nNNNmua5Rfg3"
    }
]
```

### csv

For all the spreadsheet lovers out there.

```
$ ./hd-wallet-derive.php -g --key=xpub6BfKpqjTwvH21wJGWEfxLppb8sU7C6FJge2kWb9315oP4ZVqCXG29cdUtkyu7YQhHyfA5nt63nzcNZHYmqXYHDxYo8mm1Xq1dAC7YtodwUR --numderive=3 --cols=address,xpub --format=csv

address,xpub
1FZKdR3E7S1UPvqsuqStXAhZiovntFirge,xpub6Dv5JYyovBicwK57NbxM4zbV6PBr5vP6EDZTMHsULd8uFKSXAArqiChQKo26JWjrAxgsz78riSpt2QU1TEfkK6ZSdSYqgSwnTbbrs7J3qZ8
12UMERLGAHKe5PQPaSYX8sczr52rSAg2Mi,xpub6Dv5JYyovBiczGhM8vgsv5LXpjoHhBZeD8cMgjtkMbd4vsqkYudETnnKWmJHyFvcNWvgqj21pBxunfHaBGdWgEKqo9ogr1x6qaExZ5wTvPm
1Pyk8NLx3gaXSng7XhKoNMLfBffUsJGAjr,xpub6Dv5JYyovBid1MKNu51wxzPaCqooCFmKgzHLihydzgibWkYqQg9QZmpMuX8jqZ4zSAdTBJQKh7ekmdXrT8LNthKD1ia6nC3nNNNmua5Rfg3
```


# How address derivation works

For background, please read [bip32 proposal](https://github.com/bitcoin/bips/blob/master/bip-0032.mediawiki).

This tool does not care about absolute bip32 paths or their meanings. In the
default mode it simply accepts a key as input and begins to derive new
(deterministic) keys from it.

This behavior can be modified with the --path argument, which specifies a
sub-path relative to the provided key. For xprv and xpub keys exported from most
wallet software, use --path=0 to find receive addresses and --path=1 to find
change addresses. Expert users may have trickier uses for this flag.

Due to the simplicity of this approach, the tool does not need to know or care about specific
bip32 path layouts such as bip44, bip45, etc.


# Path Presets

Path presets are available for standardized paths and commonly used Bitcoin Wallet software.

A list of the presets can be obtained by running

    $ ./hd-wallet-derive.php --help-presets

Or [view them online](./doc/wallet-bip32-path-presets.md).

--path and --preset are mutually exlusive, so use one or the other.


# Path variables

Some wallet software supports features such as multiple accounts or even multiple
cryptocurrencies that share a common bip32 path structure.  The mostly communly used structure
is Bip44, so we will use that as an example.

Bip44 has the structure:

    m / purpose' / coin_type' / account' / change / address_index

A valid Bip44 path used to generate an address might look like:

    m/44'/0/0/0/0

hd-wallet-derive accepts one-letter variables with special meanings that can be
substituted in a path.

These variables are used in path presets and variable values can be defined with flags
--coin, --path-account and --path-change

### Variables

| var | meaning                                    | flag to fill it in  | default           |
|-----|--------------------------------------------|---------------------|-------------------|
| c   | coin identifier, from --help-coins         | --coin=\<id>        | 0 (btc)           |
| a   | account                                    | --path-account=\<n> | 0 (first account) |
| v   | visibility.  internal/external, aka change | --path-change       | 0 ( external )    |
| x   | the field to increment / iterate.          | none                | n/a               |


Normal usage will be to choose a preset with --preset=\<id> and then use
one or more of the flags to override default for any included variables.

Use the --help-presets flag to find path definitions for your wallet software.

note: It is possible to use variables in paths passed via the --path argument, but you
shouldn't need to do this as you can directly manipulate every field when using a custom path.


# Segwit notes

*Segwit support is considered experimental in this release!*

This tool uses the notation x,y,z to indicate the extended key prefix bytes,
regardless of coin or network.

The meanings of x,y,z are:

|key-type|meaning        |
|--------|---------------|
|x       |p2pkh or p2sh  |
|y       |p2wpkh in p2sh |
|z       |p2wpkh         |


Some examples:

|key-type|coin      |network  |private ext key prefix|public ext key prefix|
|--------|----------|---------|----------------------|---------------------|
|x       |BTC       |Mainnet  |xprv                  |xpub                 |
|y       |BTC       |Mainnet  |yprv                  |ypub                 |
|z       |BTC       |Mainnet  |zprv                  |zpub                 |
|x       |BTC-test  |Testnet  |tprv                  |tpub                 |
|y       |BTC-test  |Testnet  |uprv                  |upub                 |
|z       |BTC-test  |Testnet  |vprv                  |vpub                 |
|x       |DOGE      |Mainnet  |dgpv                  |dgub                 |
|x       |OK        |Mainnet  |okpv                  |okub                 |

At present, most coins do not have prefixes defined for y and z types.
This tool will default to BTC values for y and z in these cases, although
z will not work unless bech32_prefix is defined in the coin's source code.

[Slip132](https://github.com/satoshilabs/slips/blob/master/slip-0132.md) has a list of known prefix types.

Anyway, y and z addresses for coins other than BTC are for experimental purposes
only and you should never send money to one without first verifying it in your
wallet software.


# Litecoin notes

LTC went off the rails a bit and did two funky things:

1. Litecoin-core always used xpub/xprv like BTC, but some wallets started
using Ltub and now Mtub for segwit p2sh extended keys.  This tool uses
xpub/xprv by default, but exposes a command-line option *--alt-extended=Ltub*
which will use the alternate prefixes.  Note that this is only relevant to
extended key generation.  Both xpub and Ltub style keys will generate the
exact same addresses.

2. Liteoin-core changed the SCRIPT_ADDRESS prefix for p2sh addresses. It
can read old and new style, but will generate new style.  This tool
generates only new style.

## xpub vs Ltub keys.

notice below that the generated addresses are the same as are the privkey and pubkey but the encoding of the xprv and xpub are different.

```
 ./hd-wallet-derive.php --coin=LTC --mnemonic="wagon rail round impulse donor radar escape harsh series" --numderive=1 --includeroot --cols=path,address,xprv,xpub,privkey,pubkey -g

+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+
| path            | address                            | xprv                                                                                                            | xpub                                                                                                            | privkey                                              | pubkey                                                             |
+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+
| m               | LKNY2uPMT9pXG1k9pHrfv5PAHXy5Skt4B5 | xprv9s21ZrQH143K277qXR6NRni1DS7qmSNBs96NmnF5VGdcejgrJtBHzRJYZxqDJZKX1pF5i6gmmDmWDbBCs4DcQ9T1bH65UttBRw2uMaNJnNQ | xpub661MyMwAqRbcEbCJdSdNnvejmTxLAu63EN1yaAeh3cAbXY1zrRVYYDd2RH3654fwTBL4HaFhoMSuo8h8T7iAM7sVLhgKU6WB2EAGTkQh4zu | T5z8xkPacjcxmaQuTnTvc9nJwguGyQdvsHBFhRceBS3vWG3rxSiM | 03f99450e4f69f1e03b778e60b0339a5a6a39525c071dfb0f41b55e1afe0fa7fdc |
| m/44'/2'/0'/0/0 | LYy3dBHszRadxCVczpsxaND32FoaAa8nbk | xprvA2rhfN2XaYQLti4axzziLV6djZrjCo4Wbwi73sbiCVVba9av45Z2MGeKi14Eqgq7pUKgxHuYAdLZ5FYu8oANqeqkDgd3tFmFHvNv8CHzEfH | xpub6Fr44sZRQuxe7C9452Xihd3NHbhDcFnMyAdhrG1Kkq2aSwv4bcsGu4xoZFJuaQ165C7sp4H1WFVV2AoYZLQ7puBFsGqucYCtQpWAt487nr1 | TArerCDBMzqdgM3zprT7hJZobonS16BZgUaj2m7irgRynQ1TNW4e | 028278f02f088cf83910006ff226e35190abef67aee82e761a29f96a7a5833d048 |
+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+

$ ./hd-wallet-derive.php --alt-extended=Ltub --coin=LTC --mnemonic="wagon rail round impulse donor radar escape harsh series" --numderive=1 --includeroot --cols=path,address,xprv,xpub,privkey,pubkey -g

+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+
| path            | address                            | xprv                                                                                                            | xpub                                                                                                            | privkey                                              | pubkey                                                             |
+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+
| m               | LKNY2uPMT9pXG1k9pHrfv5PAHXy5Skt4B5 | Ltpv71G8qDifUiNes5mQyK8zaCvkQhahrS3XH87vjGdWUvDhfYTjBWwrMG1CXjNEBGxhvvpMncLHvJUAD6JHGBCb3FpMYr3FnqwDEKeoR2568uZ | Ltub2SSUS19CirucUxUFDvdNenkxrGGtvgcBMeDxQFPoR71JFueoWRzHMs7Tn9Z7jU5C3PjdsheFeP6HuE4FN1ocC3eudqZn8nGDWivcNFPktRC | T5z8xkPacjcxmaQuTnTvc9nJwguGyQdvsHBFhRceBS3vWG3rxSiM | 03f99450e4f69f1e03b778e60b0339a5a6a39525c071dfb0f41b55e1afe0fa7fdc |
| m/44'/2'/0'/0/0 | LYy3dBHszRadxCVczpsxaND32FoaAa8nbk | Ltpv7B6pvjLv4CjgjgiAQu3LUuKNvqKbHnjr1vjf1Mz9C95gaxMnviKai7LyfmbFiQUJjaty2oZ4Ki3D4kfyXv9MUmD6BFaECCpH6JzpBgw25UA | Ltub2cHAXWmTJMGeMZQzfWXiZV9bNQ1nN3JW6SqggLkS8KsHBKYsFdN1iiTEv7pwEoQLfQXTQBfZMH8s8GAfUEVZfpxgAQjNHDxvuKGWnWWk8Xv | TArerCDBMzqdgM3zprT7hJZobonS16BZgUaj2m7irgRynQ1TNW4e | 028278f02f088cf83910006ff226e35190abef67aee82e761a29f96a7a5833d048 |
+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+--------------------------------------------------------------------+
```

note: When deriving with --key="Ltub......", the *--alt-extended=Ltub* flag must be included or
an error will be generated.

The *--alt-extended=Ltub* applies to all LTC operations include key generation,
key derivation via --key or --mnemonic as well as to LTC testnet and segwit (y,z) keys.

## Here we see Mtub key and new style 'M' p2sh address.
```
$ ./hd-wallet-derive.php --key-type=y --alt-extended=Ltub --coin=LTC --mnemonic="wagon rail round impulse donor radar escape harsh series"  --numderive=1 --includeroot --cols=path,address,xpub -g
+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+
| path            | address                            | xpub                                                                                                            |
+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+
| m               | MTcqc7d7YeCCqtznxqukXhvuXjFU3YiNYY | Mtub2mGjjfp7sYT6LFfN4HQzrsrU2ERLsJbgGkkBBeHgo7PBK1U2m69qyvmboMWhjNj7T2rSdBEp73SqnWfp5iDczHLWWBGCih5hnSzFkqKszC1 |
| m/49'/2'/0'/0/0 | MDrdvYHZXqkT2pd7VDZgz2WUs8zg7ZfkDf | Mtub2wrQVfmfLAR5st3nERaqt9FYnD8guDyrjxew73rWq2zUHiV95434YnMjowEoq4723ZQ27Kw8i6vSRyQaCoraNPKpMHyaLhquS5Gkq7XNhjC |
+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+
```

## And a ttub testnet key
```
$ ./hd-wallet-derive.php  --alt-extended=Ltub --coin=LTC-test --mnemonic="wagon rail round impulse donor radar escape harsh series"  --numderive=1 --includeroot --cols=path,address,xpub -g

+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+
| path            | address                            | xpub                                                                                                            |
+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+
| m               | mffY4kAWBX1inKXcMiqkTyXiwKCWC3fiH6 | ttub4XNESS7BCg9c1Teh7mfmHL5gLFFmJfs5TADpxkPThNp41RnVWdkwdBrW7tJkAPRjfQVJFSp3yoBdy4jS6fYkdDKSskLLpkXdX56FWPahTBW |
| m/44'/1'/0'/0/0 | mnuJrsWK9XjTLME4GHo1tLNd6TFrGjtAaG | ttub4hLeb7yEPwSJkhbb5hjEuZ8BeYUaJJJNQvUSL92ohB3Kj8L6phZ2YSpAFisrjxowXYSDbYHxu46wfv5EqZKkM5E66mWhcA51bSPpj3z7o69 |
+-----------------+------------------------------------+-----------------------------------------------------------------------------------------------------------------+
```


# Privacy and Security implications

This tool runs locally and does not make any requests to a server.
This eliminates many forms of leaks and privacy issues.

That said, any time when you are working with private keys you should
take serious security precautions.

In particular, be advised that a master xprv key exposes not only a single key,
but potentially all private keys in your wallet both receive and change
addresses.

Further, when you run this tool in a terminal, the executed command(s) will
usually be saved to a history file -- including your xprv or xpub key! You
should be very careful to either expunge the command(s), or move the funds to
another wallet, or be certain untrusted parties cannot access your machine.

Finally, this tool depends on libraries written by other authors and they
have not been carefully audited for security.  So use at your own risk.


# Use at your own risk.

The author makes no claims or guarantees of correctness.

By using this software you agree to take full responsibility for any losses
incurred before, during, or after the usage, whatsoever the cause, and not to
hold the software author liable in any manner.


# Output formats

The report may be printed in the following formats:
* plain      - an ascii formatted table, as above.  intended for humans.
* csv        - CSV format.  For spreadsheet programs.
* json       - raw json format.  for programs to read easily.
* jsonpretty - pretty json format.  for programs or humans.
* list       - single column list. for easy cut/paste.  uses first col.


# Usage

```
   hd-wallet-derive.php

   This script derives private keys and public addresses

   Options:

    -g                   go!  ( required )

    --key=<key>          xpriv or xpub key
    --mnemonic=<words>   bip39 seed words
                           note: either key or nmemonic is required.

    --mnemonic-pw=<pw>   optional password for mnemonic.

    --addr-type=<t>      legacy | p2sh-segwit | bech32 | auto
                            default = auto  (based on key-type)

    --key-type=<t>       x | y | z
                            default = x. applies to --mnemonic only.

    --coin=<coin>        Coin Symbol ( default = btc )
                         See --helpcoins for a list.

    --helpcoins          List all available coins/networks.
                         --format applies to output.

    --numderive=<n>      Number of keys to derive.  default=10

    --startindex=<n>     Index to start deriving keys from.  default=0

    --cols=<cols>        a csv list of columns, or "all"
                         all:
                          (path,address,xprv,xpub,privkey,pubkey,pubkeyhash,index)
                         default:
                          (path,address,privkey)

    --bch-format=<fmt>   Bitcoin cash address format.
                           legacy|cash   default=cash
    --alt-extended=<id>  Use alternate extended keys. supported:
                           LTC:  Ltub

    --outfile=<path>     specify output file path.
    --format=<format>    txt|md|csv|json|jsonpretty|html|list|all   default=txt

                         if 'all' is specified then a file will be created
                         for each format with appropriate extension.
                         only works when outfile is specified.

                         'list' prints only the first column. see --cols

    --path=<path>        bip32 path to derive, relative to provided key (m).
                           ex: "", "m/0", "m/1"
                           default = "m"
                             if --mnemonic is used, then default is the
                             bip44 path to extended key, eg m/44'/0'/0'/0
                             which facilitates address derivation from
                             mnemonic phrase.
                           note: /x' generates hardened addrs; requires xprv.
                           note: /x is implicit; m/x is equivalent to m.
                           ex: m/0/x'", "m/1/x'"
                           for bitcoin-core hd-wallet use: m/0'/0'/x'
                           for ledger-live use m/44'/60'/x'/0/0
                           for trezor, mew use m/44'/60'/0'/0

    --includeroot       include root key as first element of report.
    --gen-key           generates a new key.
    --gen-words=<n>     num words to generate. implies --gen-key.
                           one of: [12, 15, 18, 21, 24, 27, 30, 33, 36, 39, 42, 45, 48]
                           default = 24.

    --logfile=<file>    path to logfile. if not present logs to stdout.
    --loglevel=<level>  debug,info,specialinfo,warning,exception,fatalerror
                          default = info

```


# Installation and Running.

Linux Ubuntu 16.04 requirements:
```
apt-get install php php-gmp php-mbstring php-mcrypt
```

Basics   ( see below for big performance speedup )
```
 git clone https://github.com/dan-da/hd-wallet-derive
 cd hd-wallet-derive
 php -r "readfile('https://getcomposer.org/installer');" | php
 php composer.phar install
```

Try an example
```
$ ./hd-wallet-derive.php -g --key=xprv9tyUQV64JT5qs3RSTJkXCWKMyUgoQp7F3hA1xzG6ZGu6u6Q9VMNjGr67Lctvy5P8oyaYAL9CAWrUE9i6GoNMKUga5biW6Hx4tws2six3b9c
```

## install secp256kp1 php extension for big speedup

It is really slow to generate keys in PHP.  For a huge speedup, you can install the
secp256k1 extension from:

<a href="https://github.com/Bit-Wasp/secp256k1-php">https://github.com/Bit-Wasp/secp256k1-php</a>


# Thanks

A big thank-you to the author of bitwasp/bitcoin-php.  This library does the
heavy lifting of dealing with deterministic keys and all things bitcoin.


# Todos

* refactor under /src and make into a separate lib/package
for high level derivation operations.
* add test cases, ideally for each coin.
* web frontend, maybe just for xpub keys, or to run locally.
