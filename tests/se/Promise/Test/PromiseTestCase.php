<?php

namespace se\Promise\Test;

use se\Promise\Promise;

abstract class PromiseTestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * 
	 * @param Closure $fulfill
	 * @param Closure $fail
	 * @param string $class
	 * @return se\Promise\Promise
	 */
	public function getNewPromise($fulfill = null, $fail = null, $class = null)
	{
		$fulfill 		= null === $fulfill ? function(){
		} : $fulfill;

		$fail 			= null === $fail ? function(){
		} : $fail;

		return Promise::instanciate($fulfill, $fail ? $fail : null, $class);
	}
}