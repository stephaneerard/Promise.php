<?php
namespace se\Promise;

interface PromiseInterface
{
	/**
	 * 
	 * @param mixed $fulfill
	 * @param mixed $fail
	 * @param string $class
	 * @return PromiseInterface
	 */
	static public function instanciate($fulfill, $fail = null, $class = null);
	
	/**
	 * 
	 * @param mixed $fulfill
	 * @param mixed $fail
	 * @return PromiseInterface
	 */
	public function then($fulfill, $fail = null);
	
}