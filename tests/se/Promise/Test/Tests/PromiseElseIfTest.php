<?php

namespace se\Promise\Test\Tests;

use se\Promise\Promise;

class PromiseElseIfTestCase extends \PHPUnit_Framework_TestCase
{
	public function testElseIfPromise()
	{
		$test						= $this;
		$passedInIfCondition		= false;
		$passedInIfBlock			= false;
		$passedInElseIfCondition	= false;
		$passedInElseIfBlock		= false;

		$passedAlong				= 'passed along';

		$root = $this->getNewPromise(function($arg){
			return $arg;
		});

		$if = $root
		->_if(/** if condition **/function($result) use($test, &$passedInIfCondition, $passedAlong){
			$passedInIfCondition = true;
			return false;
				
		}, /** if block **/function($result) use($test, &$passedInIfBlock, $passedAlong){
				
			$passedInIfBlock = true;
		});

		$elseIf = $if
		->_elseif(/** elseif condition**/function($arg) use($test, &$passedInElseIfCondition, $passedAlong){
			$passedInElseIfCondition = true;
			
			return true;
		}, /** elseif block **/function($arg) use($test, &$passedInElseIfBlock, $passedAlong){
			$passedInElseIfBlock = true;
			return $arg;
		});
		
		$elseIf($passedAlong);

		$this->assertInstanceOf('se\Promise\Promise', $root);
		$this->assertInstanceOf('se\Promise\IfPromise', $if);

		$this->assertTrue($passedInIfCondition, 'if condition closure has been executed as expected');
		$this->assertFalse($passedInIfBlock, 'if block closure has not been executed as expected');
		$this->assertTrue($passedInElseIfCondition, 'elseif condition closure has been executed as expected');
		$this->assertTrue($passedInElseIfBlock, 'elseif block closure has been executed as expected');

		$this->assertEquals($passedAlong, $elseIf->result());
		$this->assertSame($root, $elseIf->getRoot());
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