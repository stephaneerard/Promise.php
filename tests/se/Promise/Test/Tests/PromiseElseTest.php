<?php

namespace se\Promise\Test\Tests;

use se\Promise\Promise;

class PromiseElseTestCase extends \PHPUnit_Framework_TestCase
{
	public function testElsePromise()
	{
		$test						= $this;
		$passedInIfCondition		= false;
		$passedInIfBlock			= false;
		$passedInElseBlock			= false;

		$passedAlong				= 'passed along';

		$root = $this->getNewPromise(function($arg){
			return $arg;
		});

		$if = $root
		->_if(/** if condition **/function($result) use($test, &$passedInIfCondition, $passedAlong){
				
			$passedInIfCondition = true;
			$test->assertEquals($passedAlong, $result);
			return false;
				
		}, /** if block **/function($result) use($test, &$passedInIfBlock, $passedAlong){
				
			$passedInIfBlock = true;
			$test->assertEquals($passedAlong, $result);
				
		});

		$else = $if
		->_else(/** else block**/function($arg) use($test, &$passedInElseBlock, $passedAlong){
			$passedInElseBlock = true;
			$test->assertEquals($passedAlong, $arg);
				
			return true;
		});

		$else($passedAlong);

		$this->assertInstanceOf('se\Promise\Promise', $root);
		$this->assertInstanceOf('se\Promise\IfPromise', $if);
		$this->assertInstanceOf('se\Promise\ElsePromise', $else);

		$this->assertTrue($passedInIfCondition, 'if condition closure has been executed as expected');
		$this->assertFalse($passedInIfBlock, 'if block closure has not been executed as expected');
		$this->assertTrue($passedInElseBlock, 'else block closure has been executed as expected');

		$this->assertSame($root, $else->getRoot());
	}

	/**********************
	 *
	* 			HELPERS
	*
	*********************/

	public function getNewPromise($fulfill = null, $fail = null, $class = null)
	{
		$fulfill 		= null === $fulfill ? function(){
		} : $fulfill;

		$fail 			= null === $fail ? function(){
		} : $fail;

		return Promise::instanciate($fulfill, $fail ? $fail : null, $class);
	}
}