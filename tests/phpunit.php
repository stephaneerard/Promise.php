<?php
namespace se\Promise\Test;

class PhpunitRunner
{
	public function __construct($suite, $dir, $namespaces = array())
	{
		require 'PHPUnit/Autoload.php';
		$loader = require __DIR__ . '/../vendor/.composer/autoload.php';
		foreach($namespaces as $ns => $dir)
		{
			$loader->add($ns, $dir);
		}
		$runner = new \PHPUnit_TextUI_TestRunner();
		$suite = new $suite;
		$suite->addTestFiles($this->getTestFiles($dir));
		$runner->doRun($suite, array('verbose'=>true));
	}

	function getTestFiles($dir)
	{
		$files = array();
		$dir = new \DirectoryIterator($dir);
		foreach($dir as $file)
		{
			if(in_array($file->getFilename(), array('.', '..'))) continue;
			if($file->getType() == 'dir')
			{
				$files = array_merge($files, $this->getTestFiles($file->getPathname()));
			}
			elseif(strpos($file->getFilename(), 'Test.php'))
			{
				$files[] = $file->getPathname();
			}
		}
		return $files;
	}
}

new PhpunitRunner('se\\Promise\\Test\\PromiseTestSuite', __DIR__, array('se\\Promise\Test' => __DIR__));