<?php

namespace se\Promise\Test;

use se\Promise\Promise;

class PromiseTestCase extends \PHPUnit_Framework_TestCase
{

	public function testNew()
	{
		$new = $this->getNewPromise();
		$this->assertInstanceOf('se\Promise\Promise', $new);
	}

	public function testThen()
	{
		$passedInFulfill 	= false;
		$passedInFail 		= false;
		$object				= $this->getNewPromise();

		$object->then(function() use(&$passedInFulfill){
			$passedInFulfill = true;
		}, function(Exception $e) use(&$passedInFail){
			$passedInFail = true;
		});

		$object();

		$this->assertTrue($passedInFulfill);
		$this->assertFalse($passedInFail);
	}

	public function testThenPassResultToChainedPromises()
	{
		$test 				= $this;
		$passedInFulfill 	= false;
		$passedInFail 		= false;

		$object 			= $this->getNewPromise(function(){
			return 'passed along';
		});

		$object->then(function($passedAlong) use($test, &$passedInFulfill){
			$passedInFulfill = true;
			$test->assertEquals('passed along', $passedAlong);
		}, function(Exception $e) use(&$passedInFail){
			$passedInFail = true;
		});

		$object();

		$this->assertTrue($passedInFulfill);
		$this->assertFalse($passedInFail);
	}


	public function testThenFailingCallFailClosureWhenSet()
	{
		$test 				= $this;
		$passedInFulfill 	= false;
		$passedInFail 		= false;

		$object 			= $this->getNewPromise(function(){
			return 'passed along';
		});

		$object->then(function($passedAlong) use($test, &$passedInFulfill){
			$passedInFulfill = true;
			$test->assertEquals('passed along', $passedAlong);
			throw new \LogicException('', 202);
		}, function(\Exception $e) use($test, &$passedInFail){
			$passedInFail = true;
			$test->assertEquals(202, $e->getCode());
		});

		$object();

		$this->assertTrue($passedInFulfill);
		$this->assertTrue($passedInFail);
	}

	/**
	 * @expectedException \LogicException
	 * @throws \LogicException
	 */
	public function testThenFailingNotCallFailClosureWhenNotSetAndThrowUp()
	{
		$test 				= $this;
		$passedInFulfill 	= false;
		$passedInFail 		= false;

		$object 			= $this->getNewPromise(function(){
			return 'passed along';
		}, false);

		$object->then(function($passedAlong) use($test, &$passedInFulfill){
			$passedInFulfill = true;
			$test->assertEquals('passed along', $passedAlong);
			throw new \LogicException('', 202);
		});

		$object();
	}

	public function testFirstPromiseResultGetsPassedDownto()
	{
		$test 				= $this;

		$object 			= $this->getNewPromise(function(){
			return 'passed along';
		}, false);

		$object->then(function($passedAlong){
			return $passedAlong;
		});

		$object();
		$result = $object->result();
		$this->assertEquals('passed along', $result);
	}

	public function testIfPromise()
	{
		$test 					= $this;
		$passedInIfCondition	= false;
		$passedInIfBlock		= false;

		$root = $this->getNewPromise(function(){
			return 'passed along';
		}, false);


		$if = $root
		->_if(/** if condition **/function($result /** $result of first promise **/) use($test, &$passedInIfCondition){

			$passedInIfCondition = true;

			$condition = $result == 'passed along';
			$test->assertTrue($condition);

			return $condition;

		}, /** if block **/function($result) use ($test, &$passedInIfBlock){

			$passedInIfBlock = true;

			return $result;

		})
		;

		$if();

		$this->assertTrue($passedInIfBlock);
		$this->assertTrue($passedInIfCondition);
		$this->assertEquals('passed along', $if->getRoot()->result());
		$this->assertSame($root, $if->getRoot());

	}

	public function testIfPromisePassingInvokingArgsToRoot()
	{
		$test 					= $this;
		$passedInIfCondition	= false;
		$passedInIfBlock		= false;

		$root = $this->getNewPromise(function($arg){
			return $arg;
		}, false);


		$if = $root
		->_if(/** if condition **/function($result /** $result of first promise **/) use($test, &$passedInIfCondition){

			$passedInIfCondition = true;

			$condition = $result == 'passed along';
			$test->assertTrue($condition);

			return $condition;

		}, /** ifblock **/function($result) use ($test, &$passedInIfBlock){

			$passedInIfBlock = true;

			return $result;

		})
		;

		$if('passed along');

		$this->assertTrue($passedInIfBlock);
		$this->assertTrue($passedInIfCondition);
		$this->assertEquals('passed along', $if->getRoot()->result());
		$this->assertSame($root, $if->getRoot());
	}

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