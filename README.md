# hd-wallet-derive

A command-line tool that derives bip32 addresses and private keys.

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

+------+------------------------------------------------------+------------------------------------+
| path | privkey                                              | address                            |
+------+------------------------------------------------------+------------------------------------+
| m/0  | L4mgN3QEzR9PT6zPeKXGk3xNiGxxkHu4575W9YwBSRV2sRiyjV4g | 1A9X6wjnER55GMpCCsTjn973y56u3zviDe |
| m/1  | L1xYoR7VS6vuvkmfRM4ubaH84vRkWKUS2PmYj9DPTS76NQ4NtZP9 | 192oE3o29AAoBPQiTYe65kRU2zoBLpEnUm |
| m/2  | L21sVq8wutBSQWTzVJVBTeCdkfTgw41dK3PPViZ3Ds2xnMq8RMbK | 1BbtRW5sua3Ewhm8jHAURUiz9t4bw8TQo9 |
+------+------------------------------------------------------+------------------------------------+```
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

+-------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+------------------------------------+-------+
| path  | xprv                                                                                                            | xpub                                                                                                            | privkey                                              | address                            | index |
+-------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+------------------------------------+-------+
| m     | xprv9tyUQV64JT5qs3RSTJkXCWKMyUgoQp7F3hA1xzG6ZGu6u6Q9VMNjGr67Lctvy5P8oyaYAL9CAWrUE9i6GoNMKUga5biW6Hx4tws2six3b9c | xpub67xpozcx8pe95XVuZLHXZeG6XWXHpGq6Qv5cmNfi7cS5mtjJ2tgypeQbBs2UAR6KECeeMVKZBPLrtJunSDMstweyLXhRgPxdp14sk9tJPW9 | L1pbvV86crAGoDzqmgY85xURkz3c435Z9nirMt52UbnGjYMzKBUN | 1CHCnCjgMNb6digimckNQ6TBVcTWBAmPHK |       |
| m/1/0 | xprv9yJyMFmJjNAiXqf3yfT6NvWCkXo1PTdYw6x5sj6fH2ePRVL4N4wy8weFYJnMKxNEib9HFyS7pc69rE3tmw7FfWhshBc17wHcKsNHByt4SZC | xpub6CJKkmJCZjj1kKjX5gz6k4SwJZdVnvMQJKsgg7WGqNBNJHfCucGDgjxjPcHY9QZJCne3tubbtucYmpt7a7u1Xx3oNumZRVytpa2UdFjTr47 | KxcccHPiLz7sxUVShX9THgfH6i8D6m3iAkHtgGuB87Tm3maz8Jas | 1qhp7SiuVmxo3WKca7zHMkKjvjkGXs29d  |     0 |
| m/1/1 | xprv9yJyMFmJjNAiZ2NDkwj7Jcjo8AtdJTPYXF5bBLGUYh8NNid9bgAfuhNtSEco35mLrBzPPRTaA5XeezrtPxn59MsYBzjRZt6XqGW2T87JiS4 | xpub6CJKkmJCZjj1mWSgryG7fkgXgCj7hv7PtU1Byig672fMFWxJ9DUvTVhNHVA8ciGkqrwaqDW6Mvmr4ihT3EoqjgNK6s5P6C9X7UBLX5Trzw6 | KyxHznKrz1Y6jmE5fxnHh1e2AZLNCtDjnF86cZzk8F5S8nBxfqfc | 1N3vKW2KGeKocao9eae6kVyYL8GNLzW8yd |     1 |
| m/1/2 | xprv9yJyMFmJjNAibyUzvGgfpk8igy6DpCtjWQ6C5oVmXyUhqak5J2xj6K2y7o3qn22nAvt14cLjRQEXVpGRZoc3ULJHomWmkUTTPJCEaF4rZPr | xpub6CJKkmJCZjj1pTZU2JDgBt5TEzviDfcasd1ntBuP6K1giP5DqaGye7MSy5MYFWoGtVhvK8Ko2XW4JRMtKjMXctpPxcjRMjM47AqeWiZLKKN | L1AFnZ6JJyGRxLG9QoCTDzeLzLV617XtgLy8DrXCyLnPG6L1n7xo | 17XRz7oPJLUNp5uf66BeqTmSDuRwqz3aW4 |     2 |
+-------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+------------------------------------+-------+
```

## Derive addresses from bip39 mnemonic seed words.

```
$ ./hd-wallet-derive.php --mnemonic="refuse brush romance together undo document tortoise life equal trash sun ask blah adfadsfadsfadsfadsfadsf"  -g --includeroot  --numderive=2 --cols=all

+------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+------------------------------------+-------+
| path | xprv                                                                                                            | xpub                                                                                                            | privkey                                              | address                            | index |
+------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+------------------------------------+-------+
| m    | xprv9s21ZrQH143K4CTyEk32W1CTegxHiBhPymLeqhqiuwcC1nRHRJKVi1ALq7fa7eeqkBEv6SNbfMqKv8Pxecr3eCG4W32NYeAC1gEc6GTaJ8c | xpub661MyMwAqRbcGgYSLma2s99CCinn7eRFLzGFe6FLUH9AtakRxqdkFoUpgQezcBfw1mv7Qpk5w855BLA227mYfLj17BCmGuR57PSspgtKebV | KxMvKHv6fyNWZyi5nnURRXHoxBHr5ij2N68RmKwmjgi4Wj2y9HqL | 1GdfFaJALCAMjbXD5g8h2cvayvnjCUUge5 |       |
| m/0  | xprv9vAFEsVvB5BRU8FL81ims4W2AghDqAvo98FfcHRVtVqHAFjx96jLmaDLCokzZYs8fFajSW7ToEbx8SjDG9qMJyyNNDojXrQytVXvKq6ByMG | xpub699beP2p1SjigcKoE3FnECSkiiXiEdeeWMBGQfq7SqNG3456ge3bKNXp449ZeZ3ZsGPwtV8mx5SLgatv4jtZEJWPhsfa2aEotrDeeHdrcXw | L3FEHQEiq1jF7mnS3v8pDZw2wTKE22DQ9f9UhphJMepJv5XkjiLE | 148CyDkNig2KX8JBEp7Q7osof94yX35ZMw |     0 |
| m/1  | xprv9vAFEsVvB5BRWCtf8Dco1t99zSyoVBn3JPHd2BW9qXxvrKEwNrKCctUsSun1vT4RQddGnnRMMCTG9DQQsM6oczv4GumRBbQwNnTZ4ctsPYu | xpub699beP2p1Sjiigy8EF9oP25tYUpHteVtfcDDpZumPsVuj7a5vPdTAgoMJEWyhFCpdsH1MhCnqEMYAsmxnNTWRLw46nax64k75pcSwc5yKUQ | KwchLem2rn58g9zRDPamsuP35t4YYySaixtC9niBidAM8RP5VCJc | 1LYP3U4SSU4qmp8GgShhoqNSzyktvx9rMw |     1 |
+------+-----------------------------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------------+------------------------------------------------------+------------------------------------+-------+
```



## Derive addresses from xpub key

Addresses can also be derived for a public (xpub) key.  In this case, result fields pertaining to private keys will be empty.

```
$ $ ./hd-wallet-derive.php -g --key=xpub6BfKpqjTwvH21wJGWEfxLppb8sU7C6FJge2kWb9315oP4ZVqCXG29cdUtkyu7YQhHyfA5nt63nzcNZHYmqXYHDxYo8mm1Xq1dAC7YtodwUR --numderive=3

+------+---------+------------------------------------+
| path | privkey | address                            |
+------+---------+------------------------------------+
| m/0  |         | 1FZKdR3E7S1UPvqsuqStXAhZiovntFirge |
| m/1  |         | 12UMERLGAHKe5PQPaSYX8sczr52rSAg2Mi |
| m/2  |         | 1Pyk8NLx3gaXSng7XhKoNMLfBffUsJGAjr |
+------+---------+------------------------------------+
```


## We can easily change up the columns in whatever order we want.

Just the --cols parameter.

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
                            
    --cols=<cols>        a csv list of columns, or "all"
                         all:
                          (path,xprv,xpub,privkey,address,index)
                         default:
                          (path,privkey,address)

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

* add test cases
* Add bip39 support to obtain xpub from secret words.  maybe?
* web frontend, maybe just for xpub keys, or to run locally.
