<?php
$path = 'tests/Feature/MascotasCitasTest.php';
if (!file_exists($path)) {
    echo "NOFILE\n";
    exit(1);
}
if (!is_readable($path)) {
    echo "NOREAD\n";
    exit(1);
}
$s = file_get_contents($path);
if ($s === false) {
    echo "READERR\n";
    exit(1);
}
$first = substr($s,0,4);
if ($first === '') {
    echo "EMPTY\n";
    exit(0);
}
foreach (str_split($first) as $c) {
    echo ord($c) . ' ';
}
echo PHP_EOL;
