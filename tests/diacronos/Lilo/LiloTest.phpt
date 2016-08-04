<?php

namespace diacronosTests\Lilo;

use diacronos\Lilo\Lilo;
use Tester;

require_once __DIR__ . '/../../bootstrap.php';


class LiloTest extends Tester\TestCase
{
	/** @var Lilo */
	private $lilo;


	protected function setUp()
	{
		$this->lilo = new Lilo(array('js', 'coffee'));
		$this->lilo->appendLoadPath(__DIR__ . '/../../assets');
	}


	public function testIndependentJSFilesHaveNoDependencies()
	{
		$this->lilo->scan('b.js');
		$deps = $this->lilo->getChain('b.js');

		Tester\Assert::equal(array(), $deps, 'Independent JS files have no dependencies');
	}


	public function testSingleStepDependenciesAreCorrectlyRecorded()
	{
		$this->lilo->scan('a.coffee');
		$deps = $this->lilo->getChain('a.coffee');

		Tester\Assert::equal(array('b.js'), $deps, 'Single-step dependencies are correctly recorded');
	}


	public function testDependenciesWithMultiplExtensionsAreAccepted()
	{
		$this->lilo->scan('testing.js');
		$deps = $this->lilo->getChain('testing.js');

		Tester\Assert::equal(array('1.2.3.coffee'), $deps, 'Dependencies with multiple extensions are accepted');
	}


	public function testDependenciesCanHaveSubdirectory_RelativePaths()
	{
		$this->lilo->scan('song/loveAndMarriage.js');
		$deps = $this->lilo->getChain('song/loveAndMarriage.js');

		Tester\Assert::equal(array('song/horseAndCarriage.coffee'), $deps, 'Dependencies can have subdirectory-relative paths');
	}


	public function testMultipleDependenciesCanBeDeclaredInOneRequireDirective()
	{
		$this->lilo->scan('poly.coffee');
		$deps = $this->lilo->getChain('poly.coffee');

		Tester\Assert::equal(array('b.js', 'x.coffee'), $deps, 'Multiple dependencies can be declared in one require directive');
	}


	public function testChainedDependenciesAreCorrectlyRecorded()
	{
		$this->lilo->scan('z.coffee');
		$deps = $this->lilo->getChain('z.coffee');

		Tester\Assert::equal(array('x.coffee', 'y.js'), $deps, 'Chained dependencies are correctly recorded');
	}


	public function testDependencyCyclesCauseNoErrorsDuringScanning()
	{
		$this->lilo->scan('yin.js');

		Tester\Assert::exception(function() {
			$this->lilo->getChain('yin.js');
			$this->lilo->getChain('yang.coffee');
		}, 'Exception', 'Cyclic dependency from yang.coffee to yin.js');
	}


	public function testRequireTreeWorksForSameDirectory()
	{
		$this->lilo->scan('branch/center.coffee');
		$deps = $this->lilo->getChain('branch/center.coffee');

		Tester\Assert::equal(array(
			'branch/edge.coffee',
			'branch/periphery.js',
			'branch/subbranch/leaf.js',
		), $deps, 'require_tree works for same directory');
	}


	public function testRequireWorksForIncludesThatAreRelativeToOrigFileUsingDotDot()
	{
		$this->lilo->scan('first/syblingFolder.js');
		$deps = $this->lilo->getChain('first/syblingFolder.js');

		Tester\Assert::equal(array('sybling/sybling.js'), $deps, 'require works for includes that are relative to orig file using ../');
	}


	public function testRequireTreeWorksForNestedDirectories()
	{
		$this->lilo->scan('fellowship.js');
		$deps = $this->lilo->getChain('fellowship.js');

		Tester\Assert::equal(array(
			'middleEarth/legolas.coffee',
			'middleEarth/shire/bilbo.js',
			'middleEarth/shire/frodo.coffee',
		), $deps, 'require_tree works for nested directories');
	}


	public function testRequireTreeWorksForRedundantDirectories()
	{
		$this->lilo->scan('trilogy.coffee');
		$deps = $this->lilo->getChain('trilogy.coffee');

		Tester\Assert::equal(array(
			'middleEarth/shire/bilbo.js',
			'middleEarth/shire/frodo.coffee',
			'middleEarth/legolas.coffee',
		), $deps, 'require_tree works for redundant directories');
	}


	public function testGetFileChainReturnsCorrectDotJsFileNamesAndCode()
	{
		$coffee =<<<COFFEE
"""
Double rainbow
SO INTENSE
"""

COFFEE;
		$this->lilo->scan('z.coffee');
		$depsFiles = $this->lilo->getFileChain('z.coffee');

		Tester\Assert::equal(array(
			array(
				'filename' => 'x.coffee',
				'content' => $coffee,
			),
			array(
				'filename' => 'y.js',
				'content' => "//= require x\n",
			),
			array(
				'filename' => 'z.coffee',
				'content' => "#= require y\n",
			),
		), $depsFiles, 'getFileChain returns correct .js file names and code');
	}


	public function testGetFileChainReturnsCorrectDotJsFileNamesAndCodeWithDotDotSlashInRequirePath()
	{
		$this->lilo->scan('first/syblingFolder.js');
		$deps = $this->lilo->getFileChain('first/syblingFolder.js');

		Tester\Assert::equal(array(
			array(
				'filename' => 'sybling/sybling.js',
				'content' => "var thereWillBeJS = 3;\n",
			),
			array(
				'filename' => 'first/syblingFolder.js',
				'content' => "//= require ../sybling/sybling.js\n",
			),
		), $deps, 'getFileChain returns correct .js filenames and code with ../ in require path');
	}
}


run(new LiloTest());
