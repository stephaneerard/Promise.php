<?php

namespace se\Promise\Test\Tests;

use se\Promise\Promise;
use se\Promise\Test\PromiseTestCase;

class PromiseIfTestCase extends PromiseTestCase
{
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

	public function testGetIfPromiseClass()
	{
		$this->assertEquals('se\Promise\IfPromise', Promise::instanciate(function(){
		})->getIfPromiseClass());
	}

	/**
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function testSetIfPromiseClassThrowExceptionWhenGivingBadInterfacedClass()
	{
		$promise = Promise::instanciate(function(){
		})->setIfPromiseClass('se\Promise\Test\myDumbClass');
	}

	public function testSetIfPromiseClassDoesNotThrowExceptionWhenGivingGoodInterfacedClass()
	{
		$promise = Promise::instanciate(function(){
		})->setIfPromiseClass('se\Promise\IfPromise');
		$this->assertEquals('se\Promise\IfPromise', $promise->getIfPromiseClass());
	}
	
	public function testGetIfPromiseClassDoesNotThrowExceptionWhenGivingGoodInterfaceClass()
	{
		$ifPromiseClass = Promise::instanciate(function(){
		})->getIfPromiseClass('se\Promise\IfPromise');
		$this->assertEquals('se\Promise\IfPromise', $ifPromiseClass);
	}
	
	public function testSetStaticIfPromiseClassDoesNotThrowExceptionWhenGivingGoodInterfaceClass()
	{
		Promise::setStaticIfPromiseClass('se\Promise\IfPromise');
	}
	
	/**
	 * 
	 * @expectedException InvalidArgumentException
	 */
	public function testSetStaticIfPromiseClassDoesThrowExceptionWhenGivingGoodInterfaceClass()
	{
		Promise::setStaticIfPromiseClass('se\Promise\Test\myDumbClass');
	}
}
