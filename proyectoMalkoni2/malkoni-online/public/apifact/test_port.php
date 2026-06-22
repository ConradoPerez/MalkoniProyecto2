<?php
header('Content-Type: text/plain; charset=utf-8');

$host = 'malkonihnos.ddns.net';
$ports = [443, 9000];

foreach ($ports as $p) {
  $errno = 0; $errstr = '';
  $t0 = microtime(true);
  $fp = @fsockopen($host, $p, $errno, $errstr, 5);
  $dt = round((microtime(true)-$t0)*1000);

  if ($fp) {
    fclose($fp);
    echo "OK puerto $p (".$dt."ms)\n";
  } else {
    echo "FAIL puerto $p ($errno) $errstr (".$dt."ms)\n";
  }
}
