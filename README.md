# hd-wallet-derive

A command-line tool that derives bip32 addresses and private keys for Bitcoin and many altcoins.

As of version 0.3.2, over 300 altcoins are available, 97 with bip44 path information.
Bitcoin Cash "CashAddr" and Ethereum address types are supported.

Derivation reports show privkey (wif encoded), xprv, xpub, and address.

Input can be a xprv key, xpub key, or bip39 mnemonic string (eg 12 words) with
optional password.

This tool can be used in place of your wallet software if it is misbehaving or
if you just want to see more information about your wallet addresses, including
private keys and addresses you haven't even used yet.

Reports are available in json, plaintext, and html. Columns can be changed or
re-ordered via command-line.

See also: [hd-wallet-addrs](https://github.com/dan-da/hd-wallet-addrs) -- a tool for finding hd-wallet addresses that have received funds.

# Let's see some examples.

## using a private (xprv) key, with default columns

Here we do not specify a bip32 path, so addresses will be derived directly from
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

## Derive addresses from bip39 mnemonic seed words. (no password)

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

you can verify these results [with this tool](https://iancoleman.github.io/bip39/).


## Derive addresses from bip39 mnemonic seed words. (with password)

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

note: you can verify these results [with this tool](https://iancoleman.github.io/bip39/).



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
$ ./hd-wallet-derive.php --helpcoins
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

    --mnemonic-pw=<pw>   optionally specify password for mnemonic.

    --numderive=<n>      Number of keys to derive.  default=10

    --startderive=<n>    Starting key index to derive.  default=0

    --cols=<cols>        a csv list of columns, or "all"
                         all:
                          (path,address,xprv,xpub,privkey,pubkey,pubkeyhash,index,eth_address)
                         default:
                          (path,address,privkey)

    --outfile=<path>     specify output file path.
    --format=<format>    txt|csv|json|jsonpretty|html|list|all   default=txt

                         if 'all' is specified then a file will be created
                         for each format with appropriate extension.
                         only works when outfile is specified.

                         'list' prints only the first column. see --cols

    --path=<path>        bip32 path to derive, relative to provided key (m).
                           eg "", "m/0" or "m/1"
                           default = "m"

    --includeroot       include root key as first element of report.

    --logfile=<file>    path to logfile. if not present logs to stdout.
    --loglevel=<level>  debug,info,specialinfo,warning,exception,fatalerror
                          default = info
```


# Installation and Running.

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
heavy lifting of dealing with deterministic keys and multisig, amongst other
things.


# Todos

* add test cases, ideally for each coin.
* web frontend, maybe just for xpub keys, or to run locally.
