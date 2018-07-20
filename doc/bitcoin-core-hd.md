# bitcoin-core HD Wallet Derivation

bitcoin-core (and recent alt-coin clones) use a bip32 key derivation scheme that
is different from most 3rd party wallets in the ecosystem.

hd-wallet-derive supports deriving keys and addresses for bitcoin-core,
though it is not presently the default behavior.

[Jump to examples](#deriving-addresses-with-hd-wallet-derive)

## Background: The Bip44 way.

Many 3rd party wallets use the bip44 derivation scheme and derive keys from
an extended private key (xprv) or an extended public key (xpub).  This path
is defined as:

```
m / purpose' / coin_type' / account' / change / address_index
```

note: apostrophe denotes a hardened key, with no corresponding public key.

With the advent of segwit, 3rd party wallets have mostly embraced an
"x,y,z" scheme:  xprv/xpub,  yprv/ypub, zprv/zpub.  In this scheme, the
*purpose* portion of the path varies according to the x,y, or z prefix.
The value of purpose is based on the Bip that defines it.

|prefix|purpose|path to 1st receive address (BTC)|
|------|-------|---------------------------------|
|x     |44     |m/44'/0'/0'/0/0                  |
|y     |49     |m/49'/0'/0'/0/0                  |
|z     |84     |m/84'/0'/0'/0/0                  |


## Background: The bitcoin-core way

Bitcoin-core developers argue that using an xpub is unsafe and
they chose to use a simpler Bip32 path with only hardened keys:

```
m / account' / change' / address_index'
```

Further, bitcoin-core uses the same derivation path for all types of addresses
and have established labels for the different address types:  *legacy*, *p2sh-segwit*, and *bech32*.

Thus, a single private key from bitcoin-core can have addresses of all 3 types
and it is likely that a given sequence of derived keys will have funds
received via different address types.

# Obtaining master key from bitcoin-core.

As of bitcoin-core 0.16.0, the only way to do this is to use the dumpwallet RPC
command.  Here is an example for linux users:

```
$ bitcoin-cli dumpwallet /tmp/wallet.dump >/dev/null && grep "extended private masterkey" /tmp/wallet.dump && shred /tmp/wallet.dump
# extended private masterkey: xprv9s21ZrQH143K3KeCJ5DMac7XqmriV7xVDDCV5MNE564bKUF6piF7JK6RWHVJMzQMUBbzxLaV9kNaRMHyjVnjNiLAq2SyvJJBs7ZUg4c9kcy
```

# Deriving addresses with hd-wallet-derive.

Notice that the pubkey remains the same in the examples below, although the address
format changes.

## p2sh-segwit addresses

There are two flags that need to be passed in order to generate segwit addresses that
match the default addresses in bitcoin-core 0.16.0 and above:

|flag                     | desc                               |
|-------------------------|------------------------------------|
|--path="m/0'/0'/x'"      | gen hardened addrs beneath m/0'/0' |
|--addr-type=p2sh-segwit  | gen p2sh-segwit type addrs         |

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

These addresses match the first 3 addresses produced by bitcoin-core.

This can be verified by running the following test case which uses
the output from dumpwallet RPC command to verify the addresses.

```
$ ./tests/tester.php tests/bitcoin_core.test.php 
Running tests in bitcoin_core...
[pass] "3LTKKaLjr83nSCEE5..." == "3LTKKaLjr83nSCEE5..."  |  receive address
[pass] "34dCyjA9rEtjDEZ1A..." == "34dCyjA9rEtjDEZ1A..."  |  receive address
[pass] "3FzQckSqoQNnzdGgn..." == "3FzQckSqoQNnzdGgn..."  |  receive address
[pass] "3QbUNpKUPvdRyjbso..." == "3QbUNpKUPvdRyjbso..."  |  receive address
[pass] "3H3uN52w4ktrLuNbR..." == "3H3uN52w4ktrLuNbR..."  |  receive address
[pass] "34rhtWdXKb6kAr2mf..." == "34rhtWdXKb6kAr2mf..."  |  receive address
[pass] "3Hh6RakkQYZ3dxPYu..." == "3Hh6RakkQYZ3dxPYu..."  |  receive address
[pass] "38vGhM64tZdbXQ8Za..." == "38vGhM64tZdbXQ8Za..."  |  receive address
[pass] "3BrYgTcojUi9fkGT2..." == "3BrYgTcojUi9fkGT2..."  |  receive address
[pass] "3Gq1Jp3vUofRte8e7..." == "3Gq1Jp3vUofRte8e7..."  |  receive address


10 tests passed.
0 tests failed.
```

## legacy addresses

Like p2sh-segwit, except use --addr-type=legacy.

```
$ ./hd-wallet-derive.php --path="m/0'/0'/x'" --addr-type=legacy --cols=path,address,pubkey --numderive=3 --key=xprv9s21ZrQH143K3KeCJ5DMac7XqmriV7xVDDCV5MNE564bKUF6piF7JK6RWHVJMzQMUBbzxLaV9kNaRMHyjVnjNiLAq2SyvJJBs7ZUg4c9kcy -g

+------------+------------------------------------+--------------------------------------------------------------------+
| path       | address                            | pubkey                                                             |
+------------+------------------------------------+--------------------------------------------------------------------+
| m/0'/0'/0' | 1Pi67woafk4Gi1AGfiByu716RsRHbLdfTj | 0236ac3d8df99023e259d24754fd022af696542e25ff237bc9c835d52468b538ae |
| m/0'/0'/1' | 1FeodJygtAY98MpLcWDjBUYAkmkE3iXAzb | 0365d31a6168e1187202ffb30bc80b4a788d68e87909024b624d4963ff2426b339 |
| m/0'/0'/2' | 1AjdYCK79u3YP4PD2QP9maA2KTfywPN2yK | 02446804c9bd85f0f782a0f4e52baa7398005b0ee54dc4eed23aeef64363a7ea99 |
+------------+------------------------------------+--------------------------------------------------------------------+
```

## bech32 addresses

Like p2sh-segwit, except use --addr-type=bech32.

```
$ ./hd-wallet-derive.php --path="m/0'/0'/x'" --addr-type=bech32 --cols=path,address,pubkey --numderive=3 --key=xprv9s21ZrQH143K3KeCJ5DMac7XqmriV7xVDDCV5MNE564bKUF6piF7JK6RWHVJMzQMUBbzxLaV9kNaRMHyjVnjNiLAq2SyvJJBs7ZUg4c9kcy -g

+------------+--------------------------------------------+--------------------------------------------------------------------+
| path       | address                                    | pubkey                                                             |
+------------+--------------------------------------------+--------------------------------------------------------------------+
| m/0'/0'/0' | bc1qlyvpsmttm6nahrdshsd4yrq72a8gnkfkkdvtdp | 0236ac3d8df99023e259d24754fd022af696542e25ff237bc9c835d52468b538ae |
| m/0'/0'/1' | bc1q5zup9v8ffaly7lnu7l5xgwd89jszcdefs0tlyw | 0365d31a6168e1187202ffb30bc80b4a788d68e87909024b624d4963ff2426b339 |
| m/0'/0'/2' | bc1qdty35jh6gdmwk77dfyqznwh90dedcapck7fv2t | 02446804c9bd85f0f782a0f4e52baa7398005b0ee54dc4eed23aeef64363a7ea99 |
+------------+--------------------------------------------+--------------------------------------------------------------------+
```
