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
	
	/**
	 * 
	 * @param mixed $condition
	 * @param mixed $block
	 * @param mixed $fail
	 * @param string $class
	 * @return IfPromiseInterface
	 */
	public function _if($condition, $block, $fail = null, $class = null);
	
	
	public function _do($block, $while, $fail = null, $class = null);
	public function _while($condition, $block, $fail = null, $class = null);
	public function _for($start, $block, $fail = null, $class = null);
}