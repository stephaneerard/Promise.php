<?php

namespace se\Promise\Test\Tests;

use se\Promise\Promise;

class PromiseThenTestCase extends \PHPUnit_Framework_TestCase
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

	public function testFirstPromiseResultGetsPassedDowntoNext()
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

	/**
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function testNewPromiseWithNonCompliantClass()
	{
		Promise::instanciate(function(){
		}, function(){
		}, 'se\Promise\Test\myDumbClass');
	}
	
	/**
	 * 
	 * @expectedException InvalidArgumentException
	 */
	public function testMakeSuperClosureThrowsExceptionWhenGivenParamIsNotInstanceOfClosureOrSuperClosure()
	{
		Promise::makeSuperClosure(false);
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
