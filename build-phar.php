<?php

$opt = getopt('n:p:');

$path = $opt['p'];
$name = $opt['n'] . '.phar';
$pharStub = "<?php Phar::mapPhar('{$name}'); __HALT_COMPILER();";

if (empty($path)) {
	echo 'please specify path';
	die;
}

// mini phar
$phar = new Phar($name, 0, $name);
$phar->buildFromDirectory($path);
$phar->setStub($pharStub);