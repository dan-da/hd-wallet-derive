<?php

if( true ) {
    echo "Deriving keys for all coins...\n";
    $lines = implode("\n", exec_p('--gen-key --gen-key-all --format=jsonpretty -g'));
    while($lines[0] != '[') {
        array_shift($lines);
    }
    $data = json_decode(implode("\n", $lines), true);
}
else {
    $data = json_decode(file_get_contents('/tmp/out.json'), true);
}

$out = [];

$seen_paths = [];
foreach($data as $row) {
    $coin_id = $row['coin'];
    $path = $row['path'];
    $rootkey = $row['root-key'];

    // Skip path that have been seen, mostly testnets.
    if(@$seen_paths[$path]) {
        echo " |- $coin_id : skipping because path [$path] already seen.\n";
        continue;
    }
    $seen_paths[$path] = 1;
    
    if(!$path) {
        echo " |- $coin_id : Bip32 path to extended key not present. skipping.\n";
        continue;
    }
    
    echo " |- $coin_id : Deriving address for [$path]\n";
    $address = exec_p(sprintf("-g --coin=%s --key=$rootkey --path=%s --numderive=1 --format=list --cols=address",
                              escapeshellarg($coin_id),
                              escapeshellarg($path)));
    
    $row['address_1'] = trim($address);
    $out[] = $row;
}
file_put_contents(__DIR__ . '/multicoin-test-input.json', json_encode($out, JSON_PRETTY_PRINT));
echo "Data written to " . __DIR__ . '/multicoin-test-input.json' . "\n";

function exec_p($args) {
    $prog = realpath(__DIR__ . '/../../hd-wallet-derive.php');
    $cmd = sprintf('%s %s', $prog, $args);

    exec($cmd, $output, $rc);
    if($rc != 0) {
        throw new Exception("command failed with exit code " . $rc . "\n  command was:\n   $cmd\n", $rc);
    }
    return implode("\n", $output);
}
