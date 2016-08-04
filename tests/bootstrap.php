<?php

$loader = require __DIR__ . '/../vendor/autoload.php';  /** @var Composer\Autoload\ClassLoader $loader */
$loader->add('diacronosTests', __DIR__ . '/diacronos');
$loader->add('SrigiTests', __DIR__ . '/Srigi');


define('TEMP_DIR', __DIR__ . '/temp/' . (isset($_SERVER['argv']) ? md5(serialize($_SERVER['argv'])) : getmypid()));

function run(Tester\TestCase $testCase)
{
	$testCase->run(isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : NULL);
}


Tester\Environment::setup();
Tester\Helpers::purge(TEMP_DIR);
