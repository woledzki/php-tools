<?php
echo "vendors.php    by Wojtek Oledzki\n";
set_time_limit(0);

$defaults = array(
	'config' => 'vendors.ini',
	'branch' => 'master',
	'vendors-dir' => './',
);

$options = getopt(
	'c:t:v:b:g:h',
array('config:', 'transport:', 'vendors-dir:', 'branch:', 'tag:', 'help')
);

$optionsMap = array(
	'h' => 'help',
	'c' => 'config',
	't' => 'transport',
	'v' => 'vendors-dir',
	'g' => 'tag',
	'b' => 'branch',
);

foreach ($options as $option => $value) {
	if (isset($optionsMap[$option])) {
		if (!isset($options[$optionsMap[$option]])) {
			$options[$optionsMap[$option]] = $value;
		}
		unset($options[$option]);
	}
}
$options += $defaults;

if (isset($options['help'])) {
	printHelp();
	exit(0);
}

$vendors = array();
if (is_readable($options['config'])) {
	$vendors = parse_ini_file($options['config'], true);
} else {
	echo "  Config file `{$options['config']}` not readable\n";
	printHelp();
	exit(1);
}

foreach ($vendors as $name => $vendor) {
	foreach (array('git', 'target') as $key) {
		if (empty($vendor[$key])) {
			echo "missing `{$key}` url. Skipping\n";
			continue;
		}
	}

	$url = $vendor['git'];
	$rev = empty($vendor['branch']) ? $options['branch'] : $vendor['branch'];

	if (!empty($options['transport'])) {
		$url = preg_replace('/^(http:|https:|git:)(.*)/', $options['transport'] . ':$2', $url);
	}

	$installDir = $options['vendors-dir'] . '/' . $vendor['target'];
	if (!is_dir($installDir)) {
		echo "  Installing $name ({$url})...\n";
		system(sprintf('git clone %s %s', escapeshellarg($url), escapeshellarg($installDir)));
	}

	echo '  Updating ' . stringBold($name) . " ({$url} {$rev})...\n";
	system(sprintf('cd %s && git fetch origin && git reset --hard %s',
			escapeshellarg($installDir), escapeshellarg($rev)));
	echo "\n";
}

function stringBold($msg) {
	return "\033[1m{$msg}\033[22m";
}

function printHelp() {
	echo "vendors.php usage:
  -c (--config)
  -t (--transport)
  -v (--vendors-dir)
  -b (--branch)
  -g (--tag)
  -h (--help) prints this message
";
}